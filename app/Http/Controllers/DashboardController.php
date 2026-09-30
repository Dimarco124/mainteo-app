<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Client;
use App\Models\Depannage;
use App\Models\Equipement;
use App\Models\FicheFroid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tableau de bord Admin
     */
    public function admin(Request $request)
    {
        $user = Auth::user();
        if ($user->type_utilisateur !== 'admin') {
            return redirect()->route('dashboard');
        }

        $stats = [
            'clients'                  => Client::count(),
            'bases'                    => BaseSite::count(),
            'equipements'              => Equipement::count(),
            'utilisateurs'             => User::where('type_utilisateur', '!=', 'admin')->count(),
            'techniciens'              => User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->count(),
            'interventions_total'      => Depannage::count(),
            'interventions_attente'    => Depannage::where('statut', 'en attente')->count(),
            'interventions_en_cours'   => Depannage::where('statut', 'en cours')->count(),
            'interventions_effectuees' => Depannage::where('statut', 'résolu')->count(),
            'fiches_froid'             => FicheFroid::count(),
            'demandes_vip_urgentes'    => \App\Models\Demande::where('est_vip', true)->whereIn('statut', ['validated_by_client', 'needs_technical_operation'])->count(), // 🚨 VIP
        ];

        // 🚨 NOUVEAU: Demandes VIP urgentes
        $demandesVip = \App\Models\Demande::with(['createdBy', 'equipement', 'site', 'base', 'zone', 'client'])
            ->where('est_vip', true)
            ->whereIn('statut', ['validated_by_client', 'needs_technical_operation'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcul des données pour le graphique des 7 derniers jours
        $interventionsParJour = [];
        $interventionsEnCoursParJour = [];
        $labelsJours = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();
            
            // Compter toutes les interventions créées ce jour
            $total = Depannage::whereDate('date_demande', $dateString)->count();
            $interventionsParJour[] = $total;
            
            // Compter les interventions en cours ou en attente créées ce jour
            $enCours = Depannage::whereDate('date_demande', $dateString)
                ->whereIn('statut', ['en attente', 'en cours'])
                ->count();
            $interventionsEnCoursParJour[] = $enCours;
            
            // Label du jour (format court: Lun, Mar, etc.)
            $labelsJours[] = $date->locale('fr')->isoFormat('ddd');
        }

        // Calcul des urgences réelles
        $urgences = [
            'critique' => Depannage::where('urgence', 'Critique')->count(),
            'urgent' => Depannage::where('urgence', 'Urgent')->count(),
            'moyen' => Depannage::where('urgence', 'Moyen')->count(),
            'faible' => Depannage::where('urgence', 'Faible')->count(),
        ];

        // Dépannages récents avec filtres
        $query = Depannage::with(['technicien', 'demandeur']);
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipement_reference', 'like', "%{$search}%")
                  ->orWhere('description_panne', 'like', "%{$search}%");
            });
        }
        $interventionsRecentes = $query->orderBy('id', 'desc')->limit(10)->get();

        // Leaderboard techniciens
        $topTechniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])
            ->withCount('depannages')
            ->orderBy('depannages_count', 'desc')
            ->limit(5)
            ->get();

        // Répartition des équipements par entreprise (vrais noms des entreprises/clients)
        $clients = Client::all();
        $equipementsParClient = [];

        foreach ($clients as $clientItem) {
            // Compter les équipements rattachés au client (directement, via site ou via base)
            $count = Equipement::where('client_id', $clientItem->id)
                ->orWhereHas('site', function($q) use ($clientItem) {
                    $q->where('client_id', $clientItem->id);
                })
                ->orWhereHas('baseSite', function($q) use ($clientItem) {
                    $q->where('client_id', $clientItem->id);
                })
                ->count();

            if ($count > 0) {
                $equipementsParClient[] = [
                    'nom'   => $clientItem->nom ?? 'Entreprise sans nom',
                    'count' => $count,
                ];
            }
        }

        // Trier par nombre d'équipements décroissant
        usort($equipementsParClient, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // Équipements non rattachés
        $unassignedEquipmentsCount = Equipement::whereNull('client_id')
            ->whereNull('site_id')
            ->whereNull('base_id')
            ->count();
            
        if ($unassignedEquipmentsCount > 0) {
            $equipementsParClient[] = [
                'nom'   => 'Non attribués',
                'count' => $unassignedEquipmentsCount,
            ];
        }

        // 📊 NOUVEAU : Répartition des équipements par BASE (Admin)
        $equipementsParBase = [];
        $bases = BaseSite::with('client')->get();
        
        foreach ($bases as $base) {
            $count = Equipement::whereHas('site', function($q) use ($base) {
                $q->where('base_id', $base->id);
            })->count();
            
            if ($count > 0) {
                $equipementsParBase[] = [
                    'nom' => $base->nom_base,
                    'client' => $base->client ? $base->client->nom : 'Client inconnu',
                    'count' => $count,
                ];
            }
        }
        
        // Trier par count décroissant
        usort($equipementsParBase, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return view('dashboards.admin', compact('stats', 'interventionsRecentes', 'topTechniciens', 'interventionsParJour', 'interventionsEnCoursParJour', 'labelsJours', 'urgences', 'equipementsParClient', 'equipementsParBase', 'demandesVip'));
    }

    /**
     * Tableau de bord Superviseur Client
     * Affiche uniquement les données de SA base (base_id) OU de son client direct (client_id)
     */
    public function superviseur()
    {
        $user = Auth::user();
        if (!in_array($user->type_utilisateur, ['superviseur_client', 'admin'])) {
            return redirect()->route('dashboard');
        }

        // Vérifier que le superviseur client a bien un base_id OU un client_id
        if ($user->type_utilisateur === 'superviseur_client' && !$user->base_id && !$user->client_id) {
            abort(403, 'Aucune base ou client assigné à ce superviseur client.');
        }

        // Si admin, voir tout (pour test), sinon filtrer par base_id ou client_id
        $baseId = $user->type_utilisateur === 'admin' ? null : $user->base_id;
        $clientId = $user->type_utilisateur === 'admin' ? null : $user->client_id;

        // Stats filtrées par base OU client
        if ($baseId) {
            // Filtrer par base_id (base spécifique)
            $equipements = Equipement::whereHas('site', function($q) use ($baseId) {
                $q->where('base_id', $baseId);
            })->get();
        } elseif ($clientId) {
            // Filtrer par client_id (entreprise directe sans base ni site)
            $equipements = Equipement::where('client_id', $clientId)->get();
        } else {
            // Admin voit tout
            $equipements = Equipement::all();
        }

        // Interventions filtrées par base ou client
        $interventionsAttente = Depannage::where('statut', 'en attente')
            ->when($baseId, function($q) use ($baseId) {
                $q->whereHas('equipement.site', function($sq) use ($baseId) {
                    $sq->where('base_id', $baseId);
                });
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->whereHas('equipement', function($sq) use ($clientId) {
                    $sq->where('client_id', $clientId);
                });
            })
            ->count();

        $interventionsEnCours = Depannage::where('statut', 'en cours')
            ->when($baseId, function($q) use ($baseId) {
                $q->whereHas('equipement.site', function($sq) use ($baseId) {
                    $sq->where('base_id', $baseId);
                });
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->whereHas('equipement', function($sq) use ($clientId) {
                    $sq->where('client_id', $clientId);
                });
            })
            ->count();

        $interventionsResolues = Depannage::where('statut', 'résolu')
            ->when($baseId, function($q) use ($baseId) {
                $q->whereHas('equipement.site', function($sq) use ($baseId) {
                    $sq->where('base_id', $baseId);
                });
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->whereHas('equipement', function($sq) use ($clientId) {
                    $sq->where('client_id', $clientId);
                });
            })
            ->count();

        $interventionsSemaine = Depannage::where('date_demande', '>=', now()->subDays(7))
            ->when($baseId, function($q) use ($baseId) {
                $q->whereHas('equipement.site', function($sq) use ($baseId) {
                    $sq->where('base_id', $baseId);
                });
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->whereHas('equipement', function($sq) use ($clientId) {
                    $sq->where('client_id', $clientId);
                });
            })
            ->count();

        // Demandes en attente de validation pour cette base/client
        $demandesAttente = \App\Models\Demande::with(['createdBy', 'equipement', 'site'])
            ->when($baseId, function($q) use ($baseId) {
                $q->where('base_id', $baseId);
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->where('statut', 'pending_client_validation')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Demandes en attente de validation finale
        $demandesValidationFinale = \App\Models\Demande::with(['createdBy', 'equipement', 'site'])
            ->when($baseId, function($q) use ($baseId) {
                $q->where('base_id', $baseId);
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->where('statut', 'confirmed_by_demandeur')
            ->orderBy('date_confirmation_demandeur', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'equipements'                  => $equipements->count(),
            'demandes_attente'             => $demandesAttente->count(),
            'demandes_validation_finale'   => $demandesValidationFinale->count(),
            'interventions_en_cours'       => $interventionsEnCours,
            'interventions_resolues'       => $interventionsResolues,
            'interventions_semaine'        => $interventionsSemaine,
            'interventions_total'          => $interventionsAttente + $interventionsEnCours + $interventionsResolues,
        ];

        // Interventions récentes de la base/client
        $interventionsRecentes = Depannage::with(['technicien', 'demandeur', 'equipement'])
            ->when($baseId, function($q) use ($baseId) {
                $q->whereHas('equipement.site', function($sq) use ($baseId) {
                    $sq->where('base_id', $baseId);
                });
            })
            ->when($clientId && !$baseId, function($q) use ($clientId) {
                $q->whereHas('equipement', function($sq) use ($clientId) {
                    $sq->where('client_id', $clientId);
                });
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Info de la base ou du client
        $base = $baseId ? BaseSite::with('client')->find($baseId) : null;
        $client = ($clientId && !$baseId) ? Client::find($clientId) : null;

        // 📊 NOUVEAU : Répartition des équipements par SITE (Superviseur Client)
        $equipementsParSite = [];
        
        if ($baseId) {
            // Récupérer les sites de cette base
            $sites = \App\Models\Site::where('base_id', $baseId)->get();
            
            foreach ($sites as $site) {
                $count = Equipement::where('site_id', $site->id)->count();
                
                if ($count > 0) {
                    $equipementsParSite[] = [
                        'nom' => $site->nom_site,
                        'count' => $count,
                    ];
                }
            }
        } elseif ($clientId) {
            // Récupérer les sites de ce client direct (sans base)
            $sites = \App\Models\Site::where('client_id', $clientId)->whereNull('base_id')->get();
            
            foreach ($sites as $site) {
                $count = Equipement::where('site_id', $site->id)->count();
                
                if ($count > 0) {
                    $equipementsParSite[] = [
                        'nom' => $site->nom_site,
                        'count' => $count,
                    ];
                }
            }
        }
        
        // Trier par count décroissant
        usort($equipementsParSite, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return view('dashboards.superviseur', compact('stats', 'demandesAttente', 'demandesValidationFinale', 'interventionsRecentes', 'base', 'client', 'equipementsParSite'));
    }

    /**
     * Tableau de bord Superviseur Soutarah
     * Même interface que l'admin MAIS filtré par SA base/company assignée
     */
    public function superviseurSoutarah()
    {
        $user = Auth::user();
        if ($user->type_utilisateur !== 'superviseur_soutarah') {
            return redirect()->route('dashboard');
        }

        // Récupérer l'assignment
        $assignment = $user->assignment;
        if (!$assignment) {
            return view('dashboards.superviseur_soutarah_sans_assignment');
        }

        // Déterminer le périmètre (base_id ou client_id)
        $baseId = $assignment->base_id;
        $clientId = $assignment->client_id;

        // Stats filtrées par le périmètre
        if ($baseId) {
            // Assigné à une base spécifique
            $bases = BaseSite::where('id', $baseId)->get();
            $equipements = Equipement::whereHas('site', function($q) use ($baseId) {
                $q->where('base_id', $baseId);
            });
            $interventions = Depannage::whereHas('equipement.site', function($q) use ($baseId) {
                $q->where('base_id', $baseId);
            });
            $demandes = \App\Models\Demande::where('base_id', $baseId);
        } elseif ($clientId) {
            // Assigné à une company (sans bases ni sites)
            $bases = collect(); // Pas de bases
            $equipements = Equipement::where('client_id', $clientId);
            $interventions = Depannage::whereHas('equipement', function($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
            $demandes = \App\Models\Demande::where('client_id', $clientId);
        } else {
            abort(403, 'Assignment invalide.');
        }

        $stats = [
            'bases'                    => $bases->count(),
            'equipements'              => $equipements->count(),
            'interventions_total'      => $interventions->count(),
            'interventions_attente'    => (clone $interventions)->where('statut', 'en attente')->count(),
            'interventions_en_cours'   => (clone $interventions)->where('statut', 'en cours')->count(),
            'interventions_effectuees' => (clone $interventions)->where('statut', 'résolu')->count(),
            'demandes_validees_client' => (clone $demandes)->where('statut', 'validated_by_client')->count(),
            'demandes_operation_requise' => (clone $demandes)->where('statut', 'needs_technical_operation')->count(),
            'demandes_vip_urgentes' => (clone $demandes)->where('est_vip', true)->whereIn('statut', ['validated_by_client', 'needs_technical_operation'])->count(), // 🚨 VIP
        ];

        // Demandes récentes nécessitant validation Soutarah
        $demandesAValider = (clone $demandes)
            ->with(['createdBy', 'equipement', 'site', 'base'])
            ->where('statut', 'validated_by_client')
            ->orderBy('date_validation_client', 'desc')
            ->limit(10)
            ->get();

        // 🚨 NOUVEAU: Demandes VIP urgentes (séparées pour visibilité maximale)
        $demandesVip = (clone $demandes)
            ->with(['createdBy', 'equipement', 'site', 'base', 'zone'])
            ->where('est_vip', true)
            ->whereIn('statut', ['validated_by_client', 'needs_technical_operation'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Interventions récentes
        $interventionsRecentes = (clone $interventions)
            ->with(['technicien', 'demandeur', 'equipement'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Info assignment
        $assignmentInfo = [
            'type' => $baseId ? 'base' : 'company',
            'nom' => $baseId ? $bases->first()->nom_base : Client::find($clientId)->nom,
        ];

        // 📊 NOUVEAU : Répartition des équipements par SITE (Superviseur Soutarah)
        // Les sites des bases assignées
        $equipementsParSite = [];
        
        if ($baseId) {
            // Récupérer les sites de cette base
            $sites = \App\Models\Site::where('base_id', $baseId)->get();
            
            foreach ($sites as $site) {
                $count = Equipement::where('site_id', $site->id)->count();
                
                if ($count > 0) {
                    $equipementsParSite[] = [
                        'nom' => $site->nom_site,
                        'count' => $count,
                    ];
                }
            }
        } elseif ($clientId) {
            // Récupérer les sites de ce client
            $sites = \App\Models\Site::whereHas('baseSite', function($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->get();
            
            foreach ($sites as $site) {
                $count = Equipement::where('site_id', $site->id)->count();
                
                if ($count > 0) {
                    $equipementsParSite[] = [
                        'nom' => $site->nom_site,
                        'base' => $site->baseSite ? $site->baseSite->nom_base : 'N/A',
                        'count' => $count,
                    ];
                }
            }
        }
        
        // Trier par count décroissant
        usort($equipementsParSite, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return view('dashboards.superviseur_soutarah', compact('stats', 'demandesAValider', 'demandesVip', 'interventionsRecentes', 'assignmentInfo', 'equipementsParSite'));
    }

    /**
     * Tableau de bord Demandeur
     * Affiche ses demandes créées ainsi que les maintenances préventives sur ses sites
     */
    public function demandeur()
    {
        $user = Auth::user();
        if ($user->type_utilisateur !== 'demandeur') {
            return redirect()->route('dashboard');
        }

        // Récupérer les IDs des sites du demandeur
        $siteIds = $user->sitesAssignes->pluck('id')->toArray();
        if ($user->site_id) {
            $siteIds[] = $user->site_id;
        }
        $siteIds = array_unique(array_filter($siteIds));

        // Récupérer les demandes créées par ce demandeur OU sur ses sites assignés
        $mesDemandes = \App\Models\Demande::with(['equipement', 'site', 'base', 'technicalOperation.equipe', 'technicalOperation.technicien'])
            ->where(function($q) use ($user, $siteIds) {
                $q->where('created_by_user_id', $user->id);
                if (!empty($siteIds)) {
                    $q->orWhereIn('site_id', $siteIds);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer les bases et clients liés aux sites du demandeur
        $baseIds = [];
        $clientIdsSites = [];
        if (!empty($siteIds)) {
            $sitesData = \App\Models\Site::whereIn('id', $siteIds)->get();
            $baseIds = $sitesData->pluck('base_id')->filter()->unique()->values()->toArray();
            $clientIdsSites = $sitesData->pluck('client_id')->filter()->unique()->values()->toArray();
        }

        // Récupérer les maintenances programmées sur les sites du demandeur
        // Cherche par : site_id direct, base_id (base du site), client_id (client du site ou du demandeur)
        $maintenancesSite = \App\Models\Maintenance::with(['client', 'site', 'base', 'equipe', 'equipes', 'technicien'])
            ->where(function($q) use ($user, $siteIds, $baseIds, $clientIdsSites) {
                // 1) Sites directs
                if (!empty($siteIds)) {
                    $q->whereIn('site_id', $siteIds);
                }
                // 2) Bases contenant les sites du demandeur
                if (!empty($baseIds)) {
                    $q->orWhereIn('base_id', $baseIds);
                }
                // 3) Clients des sites du demandeur
                if (!empty($clientIdsSites)) {
                    $q->orWhereIn('client_id', $clientIdsSites);
                }
                // 4) Client direct du demandeur
                if ($user->client_id) {
                    $q->orWhere('client_id', $user->client_id);
                }
            })
            ->orderBy('date_debut_prevue', 'desc')
            ->get();

        $stats = [
            'total'                 => $mesDemandes->count(),
            'en_attente'            => $mesDemandes->where('statut', 'pending_client_validation')->count(),
            'validees'              => $mesDemandes->whereIn('statut', ['validated_by_client', 'needs_technical_operation'])->count(),
            'en_cours'              => $mesDemandes->where('statut', 'needs_technical_operation')->count(),
            'a_confirmer'           => $mesDemandes->filter(function($d) {
                return $d->technicalOperation && $d->technicalOperation->rapport_technique && !$d->date_confirmation_demandeur;
            })->count(),
            'closees'               => $mesDemandes->where('statut', 'closed')->count(),
            'maintenances_total'    => $maintenancesSite->count(),
            'maintenances_en_cours' => $maintenancesSite->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours'])->count(),
        ];

        return view('dashboards.demandeur', compact('mesDemandes', 'maintenancesSite', 'stats'));
    }

    /**
     * Tableau de bord Technicien
     */
    public function technicien()
    {
        $user = Auth::user();
        if (!in_array($user->type_utilisateur, ['technicien', 'chef technicien', 'admin'])) {
            return redirect()->route('dashboard');
        }

        // Récupérer les IDs des équipes du technicien
        $equipesIds = $user->equipes->pluck('id')->toArray();
        if ($user->equipe_id) {
            $equipesIds[] = $user->equipe_id;
        }
        $equipesIds = array_unique(array_filter($equipesIds));

        // Interventions (Depannages & Installations)
        $mesInterventions = Depannage::with(['demandeur', 'equipement.site', 'equipement.zone'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->orderBy('id', 'desc')
            ->get();

        // Séparer les Dépannages et les Installations
        $mesDepannages = $mesInterventions->filter(fn($i) => $i->type_intervention !== 'Installation');
        $mesInstallations = $mesInterventions->filter(fn($i) => $i->type_intervention === 'Installation');

        // Maintenances
        $mesMaintenances = \App\Models\Maintenance::with(['client', 'site', 'equipement', 'equipes', 'equipe'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                    $q->orWhereHas('equipes', function($eqQ) use ($equipesIds) {
                        $eqQ->whereIn('equipes.id', $equipesIds);
                    });
                }
            })
            ->orderBy('date_debut_prevue', 'desc')
            ->get();

        // Statistiques détaillées
        $stats = [
            // Totaux
            'total_general' => $mesInterventions->count() + $mesMaintenances->count(),
            'total_depannages' => $mesDepannages->count(),
            'depannages_en_cours' => $mesDepannages->where('statut', 'en cours')->count(),
            'total_installations' => $mesInstallations->count(),
            'installations_en_cours' => $mesInstallations->where('statut', 'en cours')->count(),
            'total_interventions' => $mesInterventions->count() + $mesMaintenances->count(), // Total général
            'total_interventions_seules' => $mesInterventions->count(), // Interventions uniquement
            'total_maintenances' => $mesMaintenances->count(),
            
            // Interventions (Dépannages + Installations)
            'interventions_en_cours' => $mesInterventions->where('statut', 'en cours')->count(),
            'interventions_resolues' => $mesInterventions->whereIn('statut', ['resolu', 'résolu'])->count(),
            'interventions_en_attente' => $mesInterventions->whereNotIn('statut', ['en cours', 'resolu', 'résolu'])->count(),
            
            // Maintenances
            'maintenances_actives' => $mesMaintenances->whereIn('statut', ['planifiée', 'confirmée_client', 'en_cours'])->count(),
            'maintenances_en_cours' => $mesMaintenances->where('statut', 'en_cours')->count(),
            'maintenances_terminees' => $mesMaintenances->where('statut', 'terminée')->count(),
            'maintenances_confirmees' => $mesMaintenances->where('statut', 'confirmée_client')->count(),
            'maintenances_planifiees' => $mesMaintenances->where('statut', 'planifiée')->count(),
        ];

        return view('dashboards.technicien', compact('mesInterventions', 'mesDepannages', 'mesInstallations', 'mesMaintenances', 'stats'));
    }

    /**
     * Tableau de bord Client / Utilisateur - SUPPRIMÉ
     * Il n'y a plus de rôle "utilisateur" - Uniquement Superviseur
     */
    public function client()
    {
        return redirect()->route('dashboard')->with('error', 'Ce rôle n\'existe plus. Les comptes utilisateurs sont maintenant des superviseurs de base.');
    }
}
