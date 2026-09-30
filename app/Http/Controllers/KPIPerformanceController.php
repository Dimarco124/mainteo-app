<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Equipe;
use App\Models\CompteRenduJournalier;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KPIPerformanceController extends Controller
{
    /**
     * Tableau de bord des indicateurs de performance par équipe
     * Accès : admin (tout) | superviseur_soutarah (périmètre de son client/base uniquement)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // Sécurité : seuls admin et superviseur_soutarah ont accès
        if (!in_array($role, ['admin', 'superviseur_soutarah'])) {
            abort(403, 'Accès réservé à l\'équipe Soutarah.');
        }

        // Période de filtrage (par défaut mois en cours)
        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));

        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // ── Périmètre du superviseur soutarah ─────────────────────────────────────
        // Un superviseur_soutarah est affecté à UN client (et éventuellement une base).
        // Il ne voit que les équipes et maintenances de SON périmètre.
        $scopeClientId = null;
        $scopeBaseId   = null;

        if ($role === 'superviseur_soutarah') {
            // Récupérer le périmètre via les assignments
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)
                ->latest()
                ->first();

            if ($assignment) {
                $scopeClientId = $assignment->client_id;
                $scopeBaseId   = $assignment->base_id ?? null;
            } else {
                // Fallback : via les champs directs du compte utilisateur
                $scopeClientId = $user->client_id;
                $scopeBaseId   = $user->base_id;
            }
        }

        // ── Maintenances planifiées pour ce mois ──────────────────────────────────
        $maintenancesQuery = Maintenance::with(['client', 'base', 'site', 'equipe', 'equipes', 'comptesRendusJournaliers'])
            ->where(function ($q) use ($startDate, $endDate) {
                // Maintenances planifiées dans le mois
                $q->whereBetween('date_debut_prevue', [$startDate, $endDate])
                  // Ou maintenances en cours chevauchant le mois
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('date_debut_prevue', '<', $startDate)
                          ->whereIn('statut', ['en_cours', 'planifiée', 'confirmée_client'])
                          ->where(function ($s) use ($startDate) {
                              $s->whereNull('date_fin_reelle')
                                ->orWhere('date_fin_reelle', '>=', $startDate);
                          });
                  });
            });

        // Filtrer par périmètre pour superviseur_soutarah
        if ($role === 'superviseur_soutarah') {
            if ($scopeBaseId) {
                $maintenancesQuery->where(function ($q) use ($scopeClientId, $scopeBaseId) {
                    $q->where('base_id', $scopeBaseId)
                      ->orWhere('client_id', $scopeClientId);
                });
            } elseif ($scopeClientId) {
                $maintenancesQuery->where('client_id', $scopeClientId);
            }
        }

        $maintenancesMois = $maintenancesQuery->orderBy('date_debut_prevue', 'asc')->get();

        // ── Rapports du mois ──────────────────────────────────────────────────────
        $rapportsQuery = CompteRenduJournalier::with(['maintenance', 'equipe', 'responsable', 'site'])
            ->whereBetween('date_rapport', [$startDate, $endDate]);

        if ($role === 'superviseur_soutarah') {
            $rapportsQuery->whereHas('maintenance', function ($q) use ($scopeClientId, $scopeBaseId) {
                if ($scopeBaseId) {
                    $q->where(function($sub) use ($scopeClientId, $scopeBaseId) {
                        $sub->where('base_id', $scopeBaseId)
                            ->orWhere('client_id', $scopeClientId);
                    });
                } elseif ($scopeClientId) {
                    $q->where('client_id', $scopeClientId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        $rapports = $rapportsQuery->orderBy('date_rapport', 'desc')->get();

        // ── Indicateurs Globaux du Mois ───────────────────────────────────────────
        $totalEquipementsPrevusMois = (int) $maintenancesMois->sum('nombre_equipements_prevus');
        $totalEquipementsTraitesMois = (int) $rapports->sum('nombre_equipements_traites');
        $totalEquipementsRestantsMois = max(0, $totalEquipementsPrevusMois - $totalEquipementsTraitesMois);
        $tauxRealisationMois = $totalEquipementsPrevusMois > 0 
            ? min(100, round(($totalEquipementsTraitesMois / $totalEquipementsPrevusMois) * 100, 1)) 
            : 0;

        $nbMaintenancesMois = $maintenancesMois->count();
        $nbMaintenancesTerminees = $maintenancesMois->where('statut', 'terminée')->count();
        $nbMaintenancesEnCours = $maintenancesMois->where('statut', 'en_cours')->count();
        $nbMaintenancesPlanifiees = $maintenancesMois->whereIn('statut', ['planifiée', 'confirmée_client'])->count();

        $totalRapports = $rapports->count();
        $totalAnomalies = $rapports->filter(function ($r) {
            return !empty($r->anomalies_constatees)
                && !in_array(strtolower(trim($r->anomalies_constatees)), [
                    'ras', 'ras.', 'pas d\'anomalie', 'aucune', 'aucun', 'neant', 'néant', 'none'
                ]);
        })->count();

        // Jours travaillés dans le mois
        $joursTravaillesMois = $rapports->pluck('date_rapport')->unique()->count();
        $cadenceMoyenneGlobale = $joursTravaillesMois > 0 
            ? round($totalEquipementsTraitesMois / $joursTravaillesMois, 1) 
            : 0;

        // ── Performance par Équipe sur le Mois ────────────────────────────────────
        $equipesQuery = Equipe::with(['chef', 'membres']);

        if ($role === 'superviseur_soutarah') {
            $equipeIdsInRapports = $rapports->pluck('equipe_id')->filter()->unique();
            $equipesQuery->where(function ($q) use ($scopeClientId, $scopeBaseId, $equipeIdsInRapports) {
                $q->whereIn('id', $equipeIdsInRapports)
                  ->orWhereHas('maintenances', function ($mq) use ($scopeClientId, $scopeBaseId) {
                      if ($scopeBaseId) {
                          $mq->where('base_id', $scopeBaseId)->orWhere('client_id', $scopeClientId);
                      } elseif ($scopeClientId) {
                          $mq->where('client_id', $scopeClientId);
                      }
                  });
            });
        }

        $equipes = $equipesQuery->get();

        $equipesStats = $equipes->map(function ($equipe) use ($rapports, $maintenancesMois, $totalEquipementsTraitesMois) {
            $equipeRapports = $rapports->where('equipe_id', $equipe->id);
            $nbTraites = $equipeRapports->sum('nombre_equipements_traites');
            $nbJoursActifs = $equipeRapports->pluck('date_rapport')->unique()->count();
            $cadenceMoyenne = $nbJoursActifs > 0 ? round($nbTraites / $nbJoursActifs, 1) : 0;

            // Nombre de maintenances du mois auxquelles l'équipe participe
            $nbMaintenancesEquipe = $maintenancesMois->filter(function ($m) use ($equipe) {
                return $m->equipe_id == $equipe->id 
                    || ($m->equipes && $m->equipes->contains('id', $equipe->id));
            })->count();

            // Part contributive de l'équipe
            $partContribution = $totalEquipementsTraitesMois > 0 
                ? round(($nbTraites / $totalEquipementsTraitesMois) * 100, 1) 
                : 0;

            $anomaliesCount = $equipeRapports->filter(function ($r) {
                return !empty($r->anomalies_constatees)
                    && !in_array(strtolower(trim($r->anomalies_constatees)), [
                        'ras', 'ras.', 'pas d\'anomalie', 'aucune', 'aucun', 'neant', 'néant'
                    ]);
            })->count();

            return [
                'equipe'                 => $equipe,
                'total_traites'          => $nbTraites,
                'nb_maintenances'        => $nbMaintenancesEquipe,
                'total_rapports'         => $equipeRapports->count(),
                'jours_actifs'           => $nbJoursActifs,
                'cadence_moyenne'        => $cadenceMoyenne,
                'part_contribution'      => $partContribution,
                'anomalies_count'        => $anomaliesCount,
            ];
        })->sortByDesc('total_traites');

        return view('kpis.equipes', compact(
            'month',
            'year',
            'startDate',
            'endDate',
            'rapports',
            'maintenancesMois',
            'totalEquipementsPrevusMois',
            'totalEquipementsTraitesMois',
            'totalEquipementsRestantsMois',
            'tauxRealisationMois',
            'nbMaintenancesMois',
            'nbMaintenancesTerminees',
            'nbMaintenancesEnCours',
            'nbMaintenancesPlanifiees',
            'joursTravaillesMois',
            'cadenceMoyenneGlobale',
            'totalRapports',
            'totalAnomalies',
            'equipesStats',
            'role',
            'scopeClientId',
            'scopeBaseId'
        ));
    }
}
