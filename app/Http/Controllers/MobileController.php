<?php

namespace App\Http\Controllers;

use App\Models\Depannage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileController extends Controller
{
    /**
     * Point d'entrée principal mobile
     * Redirige vers la bonne interface selon le rôle
     */
    public function index()
    {
        $user = Auth::user();
        
        // Mapping rôles → routes disponibles
        $routesDisponibles = [
            'technicien' => 'mobile.technicien.dashboard',
            'chef technicien' => 'mobile.technicien.dashboard',
            // À décommenter au fur et à mesure du développement
            // 'demandeur' => 'mobile.demandeur.dashboard',
            // 'superviseur_client' => 'mobile.superviseur.dashboard',
            // 'superviseur_soutarah' => 'mobile.superviseur_soutarah.dashboard',
            // 'admin' => 'mobile.admin.dashboard',
        ];
        
        // Si interface dispo pour ce rôle
        if (isset($routesDisponibles[$user->type_utilisateur])) {
            return redirect()->route($routesDisponibles[$user->type_utilisateur]);
        }
        
        // Sinon : Coming Soon
        return view('mobile.coming-soon', [
            'user' => $user,
            'roadmap' => $this->getRoadmap()
        ]);
    }
    
    /**
     * Dashboard technicien mobile (comme desktop)
     */
    public function technicienDashboard()
    {
        $user = Auth::user();
        
        // Vérifier que c'est bien un technicien
        if (!in_array($user->type_utilisateur, ['technicien', 'chef technicien'])) {
            abort(403, 'Accès réservé aux techniciens');
        }
        
        // Récupérer les IDs des équipes du technicien
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        // Interventions (Depannages & Installations)
        $mesInterventions = Depannage::with(['demandeur', 'equipement.site', 'equipement.client'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->orderBy('id', 'desc')
            ->get();

        // Maintenances
        $mesMaintenances = \App\Models\Maintenance::with(['client', 'site', 'equipement'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->orderBy('date_debut_prevue', 'desc')
            ->get();

        // Statistiques détaillées
        $stats = [
            // Totaux
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
        
        return view('mobile.technicien.dashboard', compact('mesInterventions', 'mesMaintenances', 'stats'));
    }
    
    /**
     * Liste des interventions technicien (comme desktop)
     */
    public function technicienInterventions(Request $request)
    {
        $user = Auth::user();
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        // Si on demande les maintenances
        if ($request->input('type') === 'Maintenance') {
            $query = \App\Models\Maintenance::with(['client', 'base', 'site', 'equipement', 'equipe', 'technicien']);

            // Filtrer uniquement les maintenances assignées au technicien
            $query->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });

            // Filtres de statut pour maintenances
            if ($statut = $request->input('statut')) {
                $query->where('statut', $statut);
            }

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero_maintenance', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('equipement', function ($eq) use ($search) {
                            $eq->where('equipement_nom', 'like', "%{$search}%");
                        });
                });
            }

            $maintenances = $query->orderBy('date_debut_prevue', 'desc')->paginate(15);

            // Compteurs
            $baseMaintenanceQuery = \App\Models\Maintenance::query();
            $baseMaintenanceQuery->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });

            $maintenancesCount = (clone $baseMaintenanceQuery)->count();

            // Compteurs interventions pour les onglets
            $baseQuery = Depannage::query();
            $baseQuery->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });

            $installationsCount = (clone $baseQuery)->where('type_intervention', 'Installation')->count();
            $depannagesCount    = (clone $baseQuery)->where('type_intervention', 'Dépannage')->count();

            return view('mobile.technicien.interventions.liste', compact('maintenances', 'installationsCount', 'depannagesCount', 'maintenancesCount'));
        }

        // Sinon, logique normale pour Installations et Dépannages
        $query = Depannage::with(['technicien', 'equipe', 'demandeur', 'equipement.baseSite', 'equipement.client', 'equipement.site']);

        // Filtrer uniquement les interventions assignées au technicien
        $query->where(function ($q) use ($user, $equipesIds) {
            $q->where('technicien_id', $user->id);
            if (!empty($equipesIds)) {
                $q->orWhereIn('equipe_id', $equipesIds);
            }
        });

        // Filtres
        if (!$request->has('type') && !$request->has('statut') && !$request->has('search')) {
            $query->where('type_intervention', 'Installation');
        } elseif ($type = $request->input('type')) {
            $query->where('type_intervention', $type);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('equipement_reference', 'like', "%{$search}%")
                    ->orWhere('description_panne', 'like', "%{$search}%");
            });
        }

        $depannages = $query->orderBy('id', 'desc')->paginate(15);

        // Compteurs par type
        $baseQuery = Depannage::query();
        $baseQuery->where(function ($q) use ($user, $equipesIds) {
            $q->where('technicien_id', $user->id);
            if (!empty($equipesIds)) {
                $q->orWhereIn('equipe_id', $equipesIds);
            }
        });

        $installationsCount = (clone $baseQuery)->where('type_intervention', 'Installation')->count();
        $depannagesCount    = (clone $baseQuery)->where('type_intervention', 'Dépannage')->count();

        // Compteur maintenances
        $baseMaintenanceQuery = \App\Models\Maintenance::query();
        $baseMaintenanceQuery->where(function ($q) use ($user, $equipesIds) {
            $q->where('technicien_id', $user->id);
            if (!empty($equipesIds)) {
                $q->orWhereIn('equipe_id', $equipesIds);
            }
        });
        $maintenancesCount = $baseMaintenanceQuery->count();

        return view('mobile.technicien.interventions.liste', compact('depannages', 'installationsCount', 'depannagesCount', 'maintenancesCount'));
    }
    
    /**
     * Détails d'une intervention
     */
    public function technicienInterventionDetails($id)
    {
        $user = Auth::user();
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        $depannage = Depannage::with([
            'demandeur', 
            'equipement.client', 
            'equipement.site.baseSite', 
            'technicien', 
            'equipe.membres',
            'equipe'
        ])
        ->where(function($q) use ($user, $equipesIds) {
            $q->where('technicien_id', $user->id);
            if (!empty($equipesIds)) {
                $q->orWhereIn('equipe_id', $equipesIds);
            }
        })
        ->findOrFail($id);
        
        return view('mobile.technicien.interventions.details', compact('depannage'));
    }
    
    /**
     * Planning technicien mobile
     */
    public function technicienPlanning()
    {
        $user = Auth::user();
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        // Interventions (Dépannages + Installations) des 30 prochains jours
        $interventions = Depannage::with(['equipement.site', 'equipement.client', 'demande'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->whereDate('date_demande', '>=', now())
            ->whereDate('date_demande', '<=', now()->addDays(30))
            ->orderBy('date_demande')
            ->get();
        
        // Maintenances des 30 prochains jours
        $maintenances = \App\Models\Maintenance::with(['client', 'site', 'equipe', 'equipement'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->whereDate('date_debut_prevue', '>=', now())
            ->whereDate('date_debut_prevue', '<=', now()->addDays(30))
            ->orderBy('date_debut_prevue')
            ->get();
        
        // Fusionner et grouper par date
        $toutesLesInterventions = collect();
        
        // Ajouter les dépannages/installations
        foreach ($interventions as $intervention) {
            $toutesLesInterventions->push([
                'id' => $intervention->id,
                'type' => $intervention->type_intervention ?? 'Dépannage',
                'numero' => '#' . $intervention->id,
                'titre' => $intervention->equipement->equipement_nom ?? 'Équipement',
                'site' => $intervention->equipement->site->nom_site ?? 'Site',
                'client' => $intervention->equipement->client->nom ?? 'Client',
                'date' => $intervention->date_demande,
                'urgence' => $intervention->urgence ?? 'Normal',
                'statut' => $intervention->statut ?? 'en attente',
                'route' => 'mobile.technicien.interventions.details',
                'badge_color' => $intervention->type_intervention === 'Installation' ? 'orange' : 'red'
            ]);
        }
        
        // Ajouter les maintenances
        foreach ($maintenances as $maintenance) {
            $toutesLesInterventions->push([
                'id' => $maintenance->id,
                'type' => 'Maintenance',
                'numero' => $maintenance->numero_maintenance,
                'titre' => $maintenance->description ?? 'Maintenance',
                'site' => $maintenance->site->nom_site ?? ($maintenance->base->nom_base ?? 'Site'),
                'client' => $maintenance->client->nom ?? 'Client',
                'date' => $maintenance->date_debut_prevue,
                'urgence' => null,
                'statut' => $maintenance->statut,
                'route' => 'mobile.technicien.maintenances.details',
                'badge_color' => 'blue',
                'progression' => $maintenance->pourcentage_avancement ?? 0,
                'nombre_equipements' => $maintenance->nombre_equipements_prevus ?? 0
            ]);
        }
        
        // Trier par date et grouper
        $interventionsParDate = $toutesLesInterventions
            ->sortBy('date')
            ->groupBy(function($item) {
                $date = $item['date'];
                if ($date instanceof \Carbon\Carbon) {
                    return $date->toDateString();
                }
                return date('Y-m-d', strtotime($date));
            });
        
        return view('mobile.technicien.planning.index', compact('interventionsParDate', 'maintenances', 'interventions'));
    }
    
    /**
     * Profil technicien mobile
     */
    public function technicienProfil()
    {
        $user = Auth::user();
        
        // Stats globales technicien
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        $stats = [
            'total_interventions' => Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })->count(),
            
            'interventions_mois' => Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })->whereMonth('date_demande', now()->month)->count(),
            
            'taux_resolution' => 0,
        ];
        
        // Calcul taux de résolution
        $total = $stats['interventions_mois'];
        if ($total > 0) {
            $resolues = Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->whereMonth('date_demande', now()->month)
            ->where('statut', 'résolu')
            ->count();
            
            $stats['taux_resolution'] = round(($resolues / $total) * 100);
        }
        
        return view('mobile.technicien.profil.index', compact('stats'));
    }
    
    /**
     * Statistiques technicien mobile
     */
    public function technicienStatistiques()
    {
        $user = Auth::user();
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        // Stats complètes
        $stats = [
            'total' => Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })->count(),
            
            'mois_actuel' => Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })->whereMonth('date_demande', now()->month)->count(),
            
            'resolues' => Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })->where('statut', 'résolu')->count(),
        ];
        
        return view('mobile.technicien.statistiques', compact('stats'));
    }
    
    /**
     * Comptes-rendus technicien mobile
     */
    public function technicienComptesRendus()
    {
        $user = Auth::user();
        $equipesIds = $user->equipes->pluck('id')->toArray();
        
        // Interventions terminées (avec ou sans rapport)
        $rapports = Depannage::with(['equipement.site', 'equipement.client'])
            ->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            })
            ->whereIn('statut', ['résolu', 'resolu'])
            ->orderBy('date_demande', 'desc')
            ->paginate(20);
        
        return view('mobile.technicien.comptes-rendus', compact('rapports'));
    }
    
    /**
     * Paramètres technicien mobile
     */
    public function technicienParametres()
    {
        $user = Auth::user();
        return view('mobile.technicien.parametres', compact('user'));
    }
    
    /**
     * Roadmap des interfaces mobiles
     */
    private function getRoadmap()
    {
        return [
            [
                'role' => 'Techniciens',
                'status' => 'disponible',
                'date' => 'Disponible maintenant',
                'icon' => '✅'
            ],
            [
                'role' => 'Demandeurs',
                'status' => 'en_cours',
                'date' => 'Dans 2 semaines',
                'icon' => '🔜'
            ],
            [
                'role' => 'Superviseurs Client',
                'status' => 'planifie',
                'date' => 'Dans 4 semaines',
                'icon' => '📅'
            ],
            [
                'role' => 'Superviseurs Soutarah',
                'status' => 'planifie',
                'date' => 'Dans 4 semaines',
                'icon' => '📅'
            ],
            [
                'role' => 'Administrateurs',
                'status' => 'planifie',
                'date' => 'Dans 5 semaines',
                'icon' => '📅'
            ],
        ];
    }

    /**
     * Détails d'une maintenance (Vue mobile)
     */
    public function technicienMaintenanceDetails($id)
    {
        $user = Auth::user();
        $maintenance = \App\Models\Maintenance::with(['client', 'site', 'equipe', 'equipement', 'createdBy'])
            ->findOrFail($id);

        // Vérifier que le technicien a accès à cette maintenance
        $equipesIds = $user->equipes->pluck('id')->toArray();
        $hasAccess = $maintenance->technicien_id == $user->id 
            || in_array($maintenance->equipe_id, $equipesIds)
            || ($maintenance->equipes && $maintenance->equipes->pluck('id')->intersect($equipesIds)->isNotEmpty());

        if (!$hasAccess) {
            abort(403, 'Cette maintenance ne vous est pas affectée.');
        }

        return view('mobile.technicien.maintenances.details', compact('maintenance'));
    }

    /**
     * Formulaire de rapport de maintenance (Vue mobile)
     */
    public function technicienMaintenanceRapport($id)
    {
        $user = Auth::user();
        $maintenance = \App\Models\Maintenance::with(['client', 'site', 'equipe', 'equipes', 'equipement'])
            ->findOrFail($id);

        // Vérifier que c'est le chef d'équipe (support équipe unique ou multi-équipes)
        $isChef = $user->isChefTechnicien() && (
            ($maintenance->equipe && $maintenance->equipe->chef_equipe == $user->id) ||
            ($maintenance->equipes && $maintenance->equipes->contains(fn($e) => $e->chef_equipe == $user->id))
        );

        if (!$isChef) {
            return redirect()->route('mobile.technicien.dashboard')->with('error', 'Seul le chef d\'équipe peut terminer une maintenance.');
        }

        // Vérifier que la maintenance est en cours
        if ($maintenance->statut !== 'en_cours') {
            return redirect()->route('mobile.technicien.maintenances.details', $maintenance->id)->with('error', 'La maintenance doit être en cours pour être terminée.');
        }

        // Vérifier que tous les équipements prévus ont été traités
        if ($maintenance->nombre_equipements_prevus > 0 && $maintenance->nombre_equipements_restants > 0) {
            return redirect()->route('mobile.technicien.maintenances.details', $maintenance->id)
                ->with('error', "Impossible de terminer la maintenance : il reste encore {$maintenance->nombre_equipements_restants} équipement(s) à traiter sur les {$maintenance->nombre_equipements_prevus} prévus.");
        }

        return view('mobile.technicien.maintenances.rapport', compact('maintenance'));
    }
}
