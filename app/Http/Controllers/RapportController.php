<?php

namespace App\Http\Controllers;

use App\Models\Depannage;
use App\Models\FicheFroid;
use App\Models\Maintenance;
use App\Models\User;

class RapportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->type_utilisateur;

        // Récupération des filtres (mois et année)
        $mois = request('mois', now()->month);
        $annee = request('annee', now()->year);

        // Initialiser assignment pour tous les rôles
        $assignment = null;
        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
        }

        // Filtrage selon le rôle
        if ($role === 'superviseur_client' && $user->base_id) {
            // Superviseur Client : stats de SA base uniquement FILTRÉES PAR MOIS/ANNÉE
            $resoluesQuery = Depannage::where('statut', 'résolu')
                ->whereYear('date_demande', $annee)
                ->whereMonth('date_demande', $mois)
                ->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            
            $attenteQuery = Depannage::where('statut', 'en attente')
                ->whereYear('date_demande', $annee)
                ->whereMonth('date_demande', $mois)
                ->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            
            $enCoursQuery = Depannage::where('statut', 'en cours')
                ->whereYear('date_demande', $annee)
                ->whereMonth('date_demande', $mois)
                ->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });

            $fichesFroidQuery = FicheFroid::whereYear('date_saisie', $annee)
                ->whereMonth('date_saisie', $mois)
                ->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });

            $stats = [
                'resolues'    => $resoluesQuery->count(),
                'en_attente'  => $attenteQuery->count(),
                'en_cours'    => $enCoursQuery->count(),
                'fiches_froid'=> $fichesFroidQuery->count(),
                'techniciens' => User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->count(),
            ];

            $fichesFroid = $fichesFroidQuery
                ->with('technicien', 'equipement')
                ->orderBy('date_saisie', 'desc')
                ->limit(20)
                ->get();

            // Techniciens ayant travaillé sur cette base CE MOIS
            $technicienIds = Depannage::whereYear('date_demande', $annee)
                ->whereMonth('date_demande', $mois)
                ->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                })
                ->whereNotNull('technicien_id')
                ->pluck('technicien_id')
                ->unique();

            $topTechniciens = User::whereIn('id', $technicienIds)
                ->whereIn('type_utilisateur', ['technicien', 'chef technicien'])
                ->withCount(['depannages' => function($q) use ($user, $mois, $annee) {
                    $q->whereYear('date_demande', $annee)
                      ->whereMonth('date_demande', $mois)
                      ->whereHas('equipement.site', function($subQ) use ($user) {
                          $subQ->where('base_id', $user->base_id);
                      });
                }])
                ->orderByDesc('depannages_count')
                ->limit(10)
                ->get();

        } elseif ($role === 'superviseur_soutarah') {
            // Superviseur Soutarah : stats de SA base/client assigné(e)
            
            if ($assignment) {
                if ($assignment->base_id) {
                    // Filtrage par base AVEC FILTRE MOIS/ANNÉE
                    $resoluesQuery = Depannage::where('statut', 'résolu')
                        ->whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site', function($q) use ($assignment) {
                            $q->where('base_id', $assignment->base_id);
                        });
                    
                    $attenteQuery = Depannage::where('statut', 'en attente')
                        ->whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site', function($q) use ($assignment) {
                            $q->where('base_id', $assignment->base_id);
                        });
                    
                    $enCoursQuery = Depannage::where('statut', 'en cours')
                        ->whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site', function($q) use ($assignment) {
                            $q->where('base_id', $assignment->base_id);
                        });

                    $fichesFroidQuery = FicheFroid::whereYear('date_saisie', $annee)
                        ->whereMonth('date_saisie', $mois)
                        ->whereHas('equipement.site', function($q) use ($assignment) {
                            $q->where('base_id', $assignment->base_id);
                        });

                    $technicienIds = Depannage::whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site', function($q) use ($assignment) {
                            $q->where('base_id', $assignment->base_id);
                        })
                        ->whereNotNull('technicien_id')
                        ->pluck('technicien_id')
                        ->unique();

                } elseif ($assignment->client_id) {
                    // Filtrage par client AVEC FILTRE MOIS/ANNÉE
                    $resoluesQuery = Depannage::where('statut', 'résolu')
                        ->whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                            $q->where('client_id', $assignment->client_id);
                        });
                    
                    $attenteQuery = Depannage::where('statut', 'en attente')
                        ->whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                            $q->where('client_id', $assignment->client_id);
                        });
                    
                    $enCoursQuery = Depannage::where('statut', 'en cours')
                        ->whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                            $q->where('client_id', $assignment->client_id);
                        });

                    $fichesFroidQuery = FicheFroid::whereYear('date_saisie', $annee)
                        ->whereMonth('date_saisie', $mois)
                        ->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                            $q->where('client_id', $assignment->client_id);
                        });

                    $technicienIds = Depannage::whereYear('date_demande', $annee)
                        ->whereMonth('date_demande', $mois)
                        ->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                            $q->where('client_id', $assignment->client_id);
                        })
                        ->whereNotNull('technicien_id')
                        ->pluck('technicien_id')
                        ->unique();
                } else {
                    // Pas de filtre valide
                    $resoluesQuery = Depannage::whereRaw('1 = 0');
                    $attenteQuery = Depannage::whereRaw('1 = 0');
                    $enCoursQuery = Depannage::whereRaw('1 = 0');
                    $fichesFroidQuery = FicheFroid::whereRaw('1 = 0');
                    $technicienIds = collect();
                }

                $stats = [
                    'resolues'    => $resoluesQuery->count(),
                    'en_attente'  => $attenteQuery->count(),
                    'en_cours'    => $enCoursQuery->count(),
                    'fiches_froid'=> $fichesFroidQuery->count(),
                    'techniciens' => User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->count(),
                ];

                $fichesFroid = $fichesFroidQuery
                    ->with('technicien', 'equipement')
                    ->orderBy('date_saisie', 'desc')
                    ->limit(20)
                    ->get();

                $topTechniciens = User::whereIn('id', $technicienIds)
                    ->whereIn('type_utilisateur', ['technicien', 'chef technicien'])
                    ->withCount(['depannages' => function($q) use ($mois, $annee, $assignment) {
                        $q->whereYear('date_demande', $annee)
                          ->whereMonth('date_demande', $mois);
                        if ($assignment->base_id) {
                            $q->whereHas('equipement.site', function($subQ) use ($assignment) {
                                $subQ->where('base_id', $assignment->base_id);
                            });
                        } elseif ($assignment->client_id) {
                            $q->whereHas('equipement.site.baseSite', function($subQ) use ($assignment) {
                                $subQ->where('client_id', $assignment->client_id);
                            });
                        }
                    }])
                    ->orderByDesc('depannages_count')
                    ->limit(10)
                    ->get();
            } else {
                // Pas d'assignment = aucune donnée
                $stats = [
                    'resolues'    => 0,
                    'en_attente'  => 0,
                    'en_cours'    => 0,
                    'fiches_froid'=> 0,
                    'techniciens' => 0,
                ];
                $fichesFroid = collect();
                $topTechniciens = collect();
            }

        } else {
            // Admin & Technicien : toutes les stats FILTRÉES PAR MOIS/ANNÉE
            $stats = [
                'resolues'    => Depannage::where('statut', 'résolu')
                    ->whereYear('date_demande', $annee)
                    ->whereMonth('date_demande', $mois)
                    ->count(),
                'en_attente'  => Depannage::where('statut', 'en attente')
                    ->whereYear('date_demande', $annee)
                    ->whereMonth('date_demande', $mois)
                    ->count(),
                'en_cours'    => Depannage::where('statut', 'en cours')
                    ->whereYear('date_demande', $annee)
                    ->whereMonth('date_demande', $mois)
                    ->count(),
                'fiches_froid'=> FicheFroid::whereYear('date_saisie', $annee)
                    ->whereMonth('date_saisie', $mois)
                    ->count(),
                'techniciens' => User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->count(),
            ];

            $fichesFroid = FicheFroid::whereYear('date_saisie', $annee)
                ->whereMonth('date_saisie', $mois)
                ->with('technicien', 'equipement')
                ->orderBy('date_saisie', 'desc')
                ->limit(20)
                ->get();

            $topTechniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])
                ->withCount(['depannages' => function($q) use ($mois, $annee) {
                    $q->whereYear('date_demande', $annee)
                      ->whereMonth('date_demande', $mois);
                }])
                ->orderByDesc('depannages_count')
                ->limit(10)
                ->get();
        }

        // Calcul des données pour l'évolution mensuelle (12 derniers mois)
        $evolutionMensuelle = [];
        $labelsMois = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            // Compter les interventions créées ce mois
            $count = Depannage::whereYear('date_demande', $date->year)
                ->whereMonth('date_demande', $date->month);
            
            // Appliquer les mêmes filtres selon le rôle
            if ($role === 'superviseur_client' && $user->base_id) {
                $count->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($role === 'superviseur_soutarah' && $assignment) {
                if ($assignment->base_id) {
                    $count->whereHas('equipement.site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $count->whereHas('equipement', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
            
            $evolutionMensuelle[] = $count->count();
            $labelsMois[] = $date->locale('fr')->isoFormat('MMM');
        }

        // Calcul des données pour les fiches F-GAS mensuelles (12 derniers mois)
        $fichesFroidMensuelles = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            // Compter les fiches créées ce mois
            $count = FicheFroid::whereYear('date_saisie', $date->year)
                ->whereMonth('date_saisie', $date->month);
            
            // Appliquer les mêmes filtres selon le rôle
            if ($role === 'superviseur_client' && $user->base_id) {
                $count->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($role === 'superviseur_soutarah' && $assignment) {
                if ($assignment->base_id) {
                    $count->whereHas('equipement.site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $count->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
            
            $fichesFroidMensuelles[] = $count->count();
        }

        // Statistiques par équipement (pour le tableau classé par nombre d'interventions)
        $equipementsQuery = \App\Models\Equipement::withCount(['depannages' => function($q) use ($mois, $annee) {
            $q->whereYear('date_demande', $annee)
              ->whereMonth('date_demande', $mois);
        }]);

        // Appliquer les mêmes filtres selon le rôle pour les équipements
        if ($role === 'superviseur_client' && $user->base_id) {
            $equipementsQuery->whereHas('site', function($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        } elseif ($role === 'superviseur_soutarah' && $assignment) {
            if ($assignment->base_id) {
                $equipementsQuery->whereHas('site', function($q) use ($assignment) {
                    $q->where('base_id', $assignment->base_id);
                });
            } elseif ($assignment->client_id) {
                $equipementsQuery->whereHas('site.baseSite', function($q) use ($assignment) {
                    $q->where('client_id', $assignment->client_id);
                });
            }
        }

        $equipementsStats = $equipementsQuery
            ->with('site')
            ->having('depannages_count', '>', 0)
            ->orderByDesc('depannages_count')
            ->get();

        // Top 5 sites avec le plus d'interventions
        $topSitesQuery = \App\Models\Site::withCount(['equipements as interventions_count' => function($q) use ($mois, $annee) {
            $q->join('depannages', 'equipements.id', '=', 'depannages.equipement_id')
              ->whereYear('depannages.date_demande', $annee)
              ->whereMonth('depannages.date_demande', $mois);
        }]);

        // Appliquer les mêmes filtres selon le rôle
        if ($role === 'superviseur_client' && $user->base_id) {
            $topSitesQuery->where('base_id', $user->base_id);
        } elseif ($role === 'superviseur_soutarah' && $assignment) {
            if ($assignment->base_id) {
                $topSitesQuery->where('base_id', $assignment->base_id);
            } elseif ($assignment->client_id) {
                $topSitesQuery->whereHas('baseSite', function($q) use ($assignment) {
                    $q->where('client_id', $assignment->client_id);
                });
            }
        }

        $topSites = $topSitesQuery
            ->having('interventions_count', '>', 0)
            ->orderByDesc('interventions_count')
            ->limit(5)
            ->get();

        // Statistiques mensuelles pour le graphique (interventions par type)
        $interventionsParType = [
            'depannages' => [],
            'maintenances' => [],
        ];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            // Compter les dépannages
            $depannagesCount = Depannage::whereYear('date_demande', $date->year)
                ->whereMonth('date_demande', $date->month);
            
            if ($role === 'superviseur_client' && $user->base_id) {
                $depannagesCount->whereHas('equipement.site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($role === 'superviseur_soutarah' && $assignment) {
                if ($assignment->base_id) {
                    $depannagesCount->whereHas('equipement.site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $depannagesCount->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
            
            $interventionsParType['depannages'][] = $depannagesCount->count();
            
            // Compter les maintenances
            $maintenancesCount = \App\Models\Maintenance::whereYear('date_debut_prevue', $date->year)
                ->whereMonth('date_debut_prevue', $date->month);
            
            if ($role === 'superviseur_client' && $user->base_id) {
                $maintenancesCount->where('base_id', $user->base_id);
            } elseif ($role === 'superviseur_soutarah' && $assignment) {
                if ($assignment->base_id) {
                    $maintenancesCount->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $maintenancesCount->where('client_id', $assignment->client_id);
                }
            }
            
            $interventionsParType['maintenances'][] = $maintenancesCount->count();
        }

        // Calcul du taux de disponibilité (équipements sans intervention ce mois)
        $totalEquipements = \App\Models\Equipement::query();
        if ($role === 'superviseur_client' && $user->base_id) {
            $totalEquipements->whereHas('site', function($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        } elseif ($role === 'superviseur_soutarah' && $assignment) {
            if ($assignment->base_id) {
                $totalEquipements->whereHas('site', function($q) use ($assignment) {
                    $q->where('base_id', $assignment->base_id);
                });
            } elseif ($assignment->client_id) {
                $totalEquipements->whereHas('site.baseSite', function($q) use ($assignment) {
                    $q->where('client_id', $assignment->client_id);
                });
            }
        }
        $totalEquipements = $totalEquipements->count();

        $equipementsEnPanne = \App\Models\Equipement::whereHas('depannages', function($q) use ($mois, $annee) {
            $q->whereYear('date_demande', $annee)
              ->whereMonth('date_demande', $mois)
              ->whereIn('statut', ['en attente', 'en cours']);
        });
        
        if ($role === 'superviseur_client' && $user->base_id) {
            $equipementsEnPanne->whereHas('site', function($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        } elseif ($role === 'superviseur_soutarah' && $assignment) {
            if ($assignment->base_id) {
                $equipementsEnPanne->whereHas('site', function($q) use ($assignment) {
                    $q->where('base_id', $assignment->base_id);
                });
            } elseif ($assignment->client_id) {
                $equipementsEnPanne->whereHas('site.baseSite', function($q) use ($assignment) {
                    $q->where('client_id', $assignment->client_id);
                });
            }
        }
        
        $equipementsEnPanne = $equipementsEnPanne->count();
        $tauxDisponibilite = $totalEquipements > 0 ? round((($totalEquipements - $equipementsEnPanne) / $totalEquipements) * 100, 1) : 0;

        // Total interventions du mois filtré (seulement le mois sélectionné)
        $totalDepannagesMois = Depannage::whereYear('date_demande', $annee)
            ->whereMonth('date_demande', $mois);
        
        $totalMaintenancesMois = \App\Models\Maintenance::whereYear('date_debut_prevue', $annee)
            ->whereMonth('date_debut_prevue', $mois);
        
        // Appliquer les filtres selon le rôle
        if ($role === 'superviseur_client' && $user->base_id) {
            $totalDepannagesMois->whereHas('equipement.site', function($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
            $totalMaintenancesMois->where('base_id', $user->base_id);
        } elseif ($role === 'superviseur_soutarah' && $assignment) {
            if ($assignment->base_id) {
                $totalDepannagesMois->whereHas('equipement.site', function($q) use ($assignment) {
                    $q->where('base_id', $assignment->base_id);
                });
                $totalMaintenancesMois->where('base_id', $assignment->base_id);
            } elseif ($assignment->client_id) {
                $totalDepannagesMois->whereHas('equipement.site.baseSite', function($q) use ($assignment) {
                    $q->where('client_id', $assignment->client_id);
                });
                $totalMaintenancesMois->where('client_id', $assignment->client_id);
            }
        }
        
        $totalInterventionsMois = $totalDepannagesMois->count() + $totalMaintenancesMois->count();

        // ═══════════════════════════════════════════════════════════════
        // STATS PERSONNELLES POUR LES TECHNICIENS
        // ═══════════════════════════════════════════════════════════════
        $statsTechnicien = null;
        if (in_array($role, ['technicien', 'chef technicien'])) {
            // Récupérer les équipes du technicien (directes et via table pivot)
            $equipesIds = $user->equipes->pluck('id')->toArray();
            if ($user->equipe_id) {
                $equipesIds[] = $user->equipe_id;
            }
            $equipesIds = array_unique(array_filter($equipesIds));

            // 1. DÉPANNAGES & INSTALLATIONS
            $interventionsBase = Depannage::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });

            // Interventions du mois sélectionné (Dépannages & Installations)
            $interventionsMois = (clone $interventionsBase)
                ->whereYear('date_demande', $annee)
                ->whereMonth('date_demande', $mois)
                ->get();

            // Séparer dépannages et installations
            $depannagesMois = $interventionsMois->filter(fn($i) => $i->type_intervention !== 'Installation');
            $installationsMois = $interventionsMois->filter(fn($i) => $i->type_intervention === 'Installation');

            $depannagesResolus = $depannagesMois->filter(fn($i) => in_array($i->statut, ['résolu', 'resolu']))->count();
            $depannagesEnCours = $depannagesMois->filter(fn($i) => $i->statut === 'en cours')->count();

            $installationsResolues = $installationsMois->filter(fn($i) => in_array($i->statut, ['résolu', 'resolu', 'terminée', 'termine']))->count();
            $installationsEnCours = $installationsMois->filter(fn($i) => $i->statut === 'en cours')->count();

            // 2. MAINTENANCES
            $maintenancesBase = Maintenance::where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                    $q->orWhereHas('equipes', function($eqQ) use ($equipesIds) {
                        $eqQ->whereIn('equipes.id', $equipesIds);
                    });
                }
            });

            // Maintenances du mois sélectionné
            $maintenancesMois = (clone $maintenancesBase)->where(function($q) use ($mois, $annee) {
                $q->where(function($sq) use ($mois, $annee) {
                    $sq->whereNotNull('date_debut_prevue')
                       ->whereYear('date_debut_prevue', $annee)
                       ->whereMonth('date_debut_prevue', $mois);
                })->orWhere(function($sq) use ($mois, $annee) {
                    $sq->whereNull('date_debut_prevue')
                       ->whereYear('created_at', $annee)
                       ->whereMonth('created_at', $mois);
                });
            })->get();

            $maintenancesResolues = $maintenancesMois->filter(fn($m) => $m->statut === 'terminée')->count();
            $maintenancesEnCours = $maintenancesMois->filter(fn($m) => in_array($m->statut, ['en_cours', 'confirmée_client', 'planifiée']))->count();

            // Totaux du mois
            $totalMois = $interventionsMois->count() + $maintenancesMois->count();
            $totalResolusMois = $depannagesResolus + $installationsResolues + $maintenancesResolues;
            $tauxResolution = $totalMois > 0 
                ? round(($totalResolusMois / $totalMois) * 100, 1) 
                : 0;

            // 3. CLASSEMENT & TOTAL GÉNÉRAL TOUS TEMPS (Activité globale réelle)
            $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();
            $scores = [];
            foreach ($techniciens as $tech) {
                $techEq = $tech->equipes->pluck('id')->toArray();
                if ($tech->equipe_id) $techEq[] = $tech->equipe_id;
                $techEq = array_unique(array_filter($techEq));

                $cntDep = Depannage::where(function($q) use ($tech, $techEq) {
                    $q->where('technicien_id', $tech->id);
                    if (!empty($techEq)) $q->orWhereIn('equipe_id', $techEq);
                })->count();

                $cntMaint = Maintenance::where(function($q) use ($tech, $techEq) {
                    $q->where('technicien_id', $tech->id);
                    if (!empty($techEq)) {
                        $q->orWhereIn('equipe_id', $techEq);
                        $q->orWhereHas('equipes', function($eqQ) use ($techEq) {
                            $eqQ->whereIn('equipes.id', $techEq);
                        });
                    }
                })->count();

                $scores[$tech->id] = $cntDep + $cntMaint;
            }

            $monTotal = $scores[$user->id] ?? 0;
            // Ne classer que si le technicien a au moins 1 travail enregistré
            $monRang = null;
            if ($monTotal > 0) {
                $strictlyHigher = count(array_filter($scores, fn($s) => $s > $monTotal));
                $monRang = $strictlyHigher + 1;
            }
            $totalTechniciens = count($techniciens);

            // 4. ÉVOLUTION PERSONNELLE (12 derniers mois)
            $monEvolution = [];
            $monEvolutionDep = [];
            $monEvolutionMaint = [];
            for ($i = 11; $i >= 0; $i--) {
                $dateEvolution = now()->subMonths($i);

                $countDep = (clone $interventionsBase)
                    ->whereYear('date_demande', $dateEvolution->year)
                    ->whereMonth('date_demande', $dateEvolution->month)
                    ->count();

                $countMaint = (clone $maintenancesBase)->where(function($q) use ($dateEvolution) {
                    $q->where(function($sq) use ($dateEvolution) {
                        $sq->whereNotNull('date_debut_prevue')
                           ->whereYear('date_debut_prevue', $dateEvolution->year)
                           ->whereMonth('date_debut_prevue', $dateEvolution->month);
                    })->orWhere(function($sq) use ($dateEvolution) {
                        $sq->whereNull('date_debut_prevue')
                           ->whereYear('created_at', $dateEvolution->year)
                           ->whereMonth('created_at', $dateEvolution->month);
                    });
                })->count();

                $monEvolutionDep[] = $countDep;
                $monEvolutionMaint[] = $countMaint;
                $monEvolution[] = $countDep + $countMaint;
            }

            // 5. STATISTIQUES COMPLÉMENTAIRES DU TECHNICIEN
            // Équipements pris en charge ce mois
            $eqDep = $interventionsMois->pluck('equipement_id')->filter()->unique()->count();
            $eqMaint = (int) $maintenancesMois->sum('nombre_equipements_traites');
            if ($eqMaint == 0) {
                $eqMaint = $maintenancesMois->pluck('equipement_id')->filter()->unique()->count();
            }
            $totalEquipementsTraites = $eqDep + $eqMaint;

            // Fiches d'entretien froid rédigées ce mois
            $fichesFroidMois = FicheFroid::where('technicien_id', $user->id)
                ->whereYear('date_saisie', $annee)
                ->whereMonth('date_saisie', $mois)
                ->count();

            // Sites distincts visités ce mois
            $sitesDep = $interventionsMois->map(fn($i) => $i->equipement ? $i->equipement->site_id : null)->filter();
            $sitesMaint = $maintenancesMois->pluck('site_id')->filter();
            $totalSitesVisites = $sitesDep->merge($sitesMaint)->unique()->count();

            // Interventions prioritaires / urgentes ce mois
            $urgencesMois = $interventionsMois->filter(fn($i) => in_array(strtolower($i->urgence ?? ''), ['urgent', 'critique', 'haute', 'tres urgent']))->count();

            // 5 Dernières interventions récentes clôturées
            $dernieresCloturees = (clone $interventionsBase)
                ->whereIn('statut', ['résolu', 'resolu'])
                ->with(['equipement.site', 'demandeur'])
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();

            $statsTechnicien = [
                'interventions_mois' => $totalMois,
                'interventions_resolues' => $totalResolusMois,
                'taux_resolution' => $tauxResolution,
                'mon_rang' => $monRang,
                'total_techniciens' => $totalTechniciens,
                'mon_total' => $monTotal,
                'mon_evolution' => $monEvolution,
                'mon_evolution_dep' => $monEvolutionDep,
                'mon_evolution_maint' => $monEvolutionMaint,
                // Détails par type d'activité
                'depannages_mois' => $depannagesMois->count(),
                'depannages_resolus' => $depannagesResolus,
                'depannages_en_cours' => $depannagesEnCours,
                'installations_mois' => $installationsMois->count(),
                'installations_resolues' => $installationsResolues,
                'installations_en_cours' => $installationsEnCours,
                'maintenances_mois' => $maintenancesMois->count(),
                'maintenances_resolues' => $maintenancesResolues,
                'maintenances_en_cours' => $maintenancesEnCours,
                // Nouvelles statistiques utiles
                'equipements_traites' => $totalEquipementsTraites,
                'fiches_froid_mois' => $fichesFroidMois,
                'sites_visites' => $totalSitesVisites,
                'urgences_mois' => $urgencesMois,
                'dernieres_cloturees' => $dernieresCloturees,
            ];
        }

        return view('rapports.index', compact(
            'stats', 
            'fichesFroid', 
            'topTechniciens', 
            'evolutionMensuelle', 
            'fichesFroidMensuelles', 
            'labelsMois',
            'equipementsStats',
            'topSites',
            'interventionsParType',
            'mois',
            'annee',
            'tauxDisponibilite',
            'equipementsEnPanne',
            'totalInterventionsMois',
            'role',
            'user',
            'statsTechnicien'
        ));
    }
}
