<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Equipe;
use App\Models\Planification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;
        
        // Charger les planifications (anciennes interventions)
        $query = Planification::with(['demande', 'depannage', 'client', 'equipe', 'technicien']);
        
        // Charger les maintenances
        $maintenancesQuery = \App\Models\Maintenance::with(['client', 'base', 'site', 'equipement', 'equipe', 'equipes', 'technicien']);
        
        // Filtrage selon le rôle
        if (in_array($role, ['technicien', 'chef technicien'])) {
            // Technicien : uniquement ses interventions
            $equipesIds = $user->equipes->pluck('id')->toArray();
            if ($user->equipe_id) {
                $equipesIds[] = $user->equipe_id;
            }
            $equipesIds = array_unique(array_filter($equipesIds));
            
            $query->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });
            
            $maintenancesQuery->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                    // Relation many-to-many via equipe_maintenance
                    $q->orWhereHas('equipes', function($eq) use ($equipesIds) {
                        $eq->whereIn('equipes.id', $equipesIds);
                    });
                }
            });
        } elseif ($role === 'superviseur_client') {
            // Superviseur Client : interventions de SA base OU de son client (entreprise directe)
            if ($user->base_id) {
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
                $maintenancesQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                // Pour entreprises directes (sans base)
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
                $maintenancesQuery->where('client_id', $user->client_id);
            } else {
                $query->whereRaw('1 = 0');
                $maintenancesQuery->whereRaw('1 = 0');
            }
        } elseif ($role === 'superviseur_soutarah') {
            // Superviseur Soutarah : interventions de son périmètre
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                    $maintenancesQuery->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                    $maintenancesQuery->where('client_id', $assignment->client_id);
                }
            } else {
                $query->whereRaw('1 = 0');
                $maintenancesQuery->whereRaw('1 = 0');
            }
        } elseif ($role === 'demandeur') {
            // Demandeur : interventions et maintenances de ses sites assignés ou de son client
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $baseIds = [];
            $clientIdsSites = [];
            if (!empty($siteIds)) {
                $sitesData = \App\Models\Site::whereIn('id', $siteIds)->get();
                $baseIds = $sitesData->pluck('base_id')->filter()->unique()->values()->toArray();
                $clientIdsSites = $sitesData->pluck('client_id')->filter()->unique()->values()->toArray();
            }

            $query->where(function($q) use ($user, $siteIds) {
                $q->whereHas('demande', function($dq) use ($user, $siteIds) {
                    $dq->where('created_by_user_id', $user->id);
                    if (!empty($siteIds)) {
                        $dq->orWhereIn('site_id', $siteIds);
                    }
                });
                if (!empty($siteIds)) {
                    $q->orWhereIn('site_code', $siteIds);
                }
            });

            $maintenancesQuery->where(function($q) use ($user, $siteIds, $baseIds, $clientIdsSites) {
                if (!empty($siteIds)) {
                    $q->whereIn('site_id', $siteIds);
                }
                if (!empty($baseIds)) {
                    $q->orWhereIn('base_id', $baseIds);
                }
                if (!empty($clientIdsSites)) {
                    $q->orWhereIn('client_id', $clientIdsSites);
                }
                if ($user->client_id) {
                    $q->orWhere('client_id', $user->client_id);
                }
            });
        }
        // Admin : voit tout
        
        $planifications = $query->orderBy('date_debut', 'desc')->paginate(15);
        $maintenances = $maintenancesQuery->orderBy('date_debut_prevue', 'desc')->get();

        return view('planning.index', compact('planifications', 'maintenances'));
    }

    public function create()
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;
        
        // Nouvelle logique : Charger les OPÉRATIONS (depannages) qui ne sont pas encore planifiées
        // Au lieu des demandes non transformées
        $operationsQuery = \App\Models\Depannage::with(['demande.client', 'demande.base', 'demande.site', 'demande.equipement'])
            ->whereIn('statut', ['en attente', 'en cours'])
            ->whereDoesntHave('planifications'); // Opérations sans planification
        
        // Filtrer selon le rôle
        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $operationsQuery->whereHas('demande', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $operationsQuery->whereHas('demande', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $operationsQuery->whereRaw('1 = 0');
            }
        }
        
        $operations = $operationsQuery->orderBy('id', 'desc')->get();
        $equipes = Equipe::all();
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();

        return view('planning.create', compact('operations', 'equipes', 'techniciens'));
    }

    public function eventsJson()
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;
        
        $query = Planification::with([
            'demande.client',
            'demande.site.baseSite',
            'demande.equipement',
            'demande.createdBy',
            'depannage.client',
            'depannage.demandeur',
            'depannage.equipement.site.baseSite',
            'equipe.chef',
            'technicien'
        ]);
        
        // Filtrage selon le rôle
        if (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            
            $query->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id)
                  ->orWhereIn('equipe_id', $equipesIds);
            });
        } elseif ($role === 'superviseur_client') {
            // Superviseur Client : interventions de SA base OU de son client (entreprise directe)
            if ($user->base_id) {
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                // Pour entreprises directes (sans base)
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'demandeur') {
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $query->where(function($q) use ($user, $siteIds) {
                $q->whereHas('demande', function($dq) use ($user, $siteIds) {
                    $dq->where('created_by_user_id', $user->id);
                    if (!empty($siteIds)) {
                        $dq->orWhereIn('site_id', $siteIds);
                    }
                });
                if (!empty($siteIds)) {
                    $q->orWhereIn('site_code', $siteIds);
                }
            });
        }
        
        $events = $query->get()->map(function ($p) {
            $type  = $p->type_intervention ?? ($p->demande ? $p->demande->type_intervention : 'Dépannage');
            
            // Déterminer le statut réel et lisible de l'intervention
            $statutDisplay = 'Planifié';
            $isTermine = false;
            
            if ($p->depannage) {
                $depStatut = strtolower(trim($p->depannage->statut ?? ''));
                $depValid = strtolower(trim($p->depannage->statut_validation_finale_client ?? ''));
                
                if (in_array($depValid, ['validé', 'valide', 'conforme'])) {
                    $statutDisplay = 'Validé Client';
                    $isTermine = true;
                } elseif (in_array($depStatut, ['resolu', 'résolu', 'termine', 'terminé', 'closed', 'cloture', 'clôturé'])) {
                    $statutDisplay = 'Terminé';
                    $isTermine = true;
                } elseif ($depStatut === 'en cours') {
                    $statutDisplay = 'En cours';
                } elseif (in_array($depStatut, ['en attente', 'en attente assignation', 'approuve - en attente assignation', 'en_attente_piece'])) {
                    $statutDisplay = 'En attente';
                } elseif (!empty($p->depannage->statut)) {
                    $statutDisplay = ucfirst(str_replace('_', ' ', $p->depannage->statut));
                } else {
                    $statutDisplay = ucfirst($p->statut ?? 'Planifié');
                }
            } elseif ($p->demande) {
                $demStatut = strtolower(trim($p->demande->statut ?? ''));
                $demValid = strtolower(trim($p->demande->statut_validation_finale_client ?? ''));
                
                if (in_array($demValid, ['validé', 'valide', 'conforme']) || in_array($demStatut, ['closed', 'cloturee', 'clôturée'])) {
                    $statutDisplay = 'Validé Client';
                    $isTermine = true;
                } elseif (in_array($demStatut, ['terminee', 'terminée', 'resolu', 'résolu'])) {
                    $statutDisplay = 'Terminé';
                    $isTermine = true;
                } elseif ($demStatut === 'en cours') {
                    $statutDisplay = 'En cours';
                } elseif (in_array($demStatut, ['pending_client_validation', 'validated_by_client', 'needs_technical_operation'])) {
                    $statutDisplay = 'Planifié';
                } else {
                    $statutDisplay = ucfirst(str_replace('_', ' ', $p->demande->statut));
                }
            } else {
                $pStatut = strtolower(trim($p->statut ?? ''));
                if (in_array($pStatut, ['termine', 'terminé', 'resolu', 'résolu', 'closed', 'cloture', 'clôturé'])) {
                    $statutDisplay = 'Terminé';
                    $isTermine = true;
                } elseif ($pStatut === 'en cours') {
                    $statutDisplay = 'En cours';
                } else {
                    $statutDisplay = ucfirst($p->statut ?? 'Planifié');
                }
            }
            
            // Sécurité : si l'opération ou la demande est explicitement en cours ou en attente, ce n'est pas terminé
            if ($p->depannage) {
                $depStatut = strtolower(trim($p->depannage->statut ?? ''));
                if (in_array($depStatut, ['en attente', 'en cours', 'en_attente_piece', 'partiellement_resolu', 'non_resolu', 'en attente assignation', 'approuve - en attente assignation'])) {
                    $isTermine = false;
                }
            }
            if ($p->demande) {
                $demStatut = strtolower(trim($p->demande->statut ?? ''));
                if (in_array($demStatut, ['pending_client_validation', 'validated_by_client', 'needs_technical_operation', 'en cours'])) {
                    $isTermine = false;
                }
            }
            
            $normType = strtolower(trim($type));
            if (in_array($normType, ['dépannage', 'depannage'])) {
                $color = $isTermine ? '#10b981' : '#dc2626'; // Rouge tant que pas terminé, Vert si terminé
            } elseif (in_array($normType, ['installation', 'installations'])) {
                $color = $isTermine ? '#10b981' : '#f59e0b'; // Orange tant que pas terminée, Vert si terminée
            } elseif (in_array($normType, ['maintenance', 'maintenances'])) {
                $color = $isTermine ? '#10b981' : '#3b82f6'; // Bleu tant que pas terminée, Vert si terminée
            } else {
                $color = $isTermine ? '#10b981' : '#dc2626';
            }

            // Informations détaillées associées
            $dep = $p->depannage;
            $dem = $p->demande;

            $eq = $dep?->equipement ?? $dem?->equipement;
            $eqCode = $eq?->equipement_code ?? null;
            $eqNom = $eq?->equipement_nom ?? ($dep?->equipement_reference ?? null);
            $eqMarque = $eq?->marque ?? null;
            $eqModele = $eq?->modele ?? null;

            $clientNom = $dem?->client?->nom ?? $p->client?->nom ?? $dep?->client_nom ?? 'Client';
            $siteNom = $dem?->site?->nom_site ?? $dep?->equipement?->site?->nom_site ?? $p->site_code ?? 'N/A';
            $baseNom = $dem?->site?->baseSite?->nom_base ?? $dep?->equipement?->site?->baseSite?->nom_base ?? null;

            $demandeurNom = $dep?->demandeur?->nom_complet ?? $dem?->createdBy?->nom_complet ?? $dep?->demandeur_nom ?? null;
            $demandeurTel = $dep?->demandeur?->telephone ?? $dem?->createdBy?->telephone ?? null;

            $riSoutarah = $dep?->ri_soutarah ?? $dem?->numero_reference_externe ?? null;
            $urgence = $dep?->urgence ?? ($dem?->niveau_urgence ? ucfirst($dem->niveau_urgence) : 'Normal');
            $description = $dep?->description_panne ?? $dem?->description ?? $p->commentaire ?? '';

            $nomEquipe = $p->nom_equipe ?? ($p->equipe ? $p->equipe->nom_equipe : ($dep?->equipe ? $dep->equipe->nom_equipe : 'Non assigné'));
            $chefEquipe = $p->equipe?->chef?->nom_complet ?? $dep?->equipe?->chef?->nom_complet ?? null;
            $technicienNom = $p->technicien?->nom_complet ?? $dep?->technicien?->nom_complet ?? null;

            // Titre propre de l'intervention
            $title = ucfirst($type);
            if ($dem) {
                $title .= ' - ' . $dem->numero_demande;
            }

            return [
                'id' => 'planif_' . $p->id,
                'title' => $title,
                'start' => $p->date_debut,
                'end' => $p->date_fin ?? $p->date_debut,
                'color' => $color,
                'extendedProps' => [
                    'type' => $type,
                    'demande_id' => $p->demande_id,
                    'depannage_id' => $p->depannage_id ?? ($dem?->technical_operation_id ?? null),
                    'demande_numero' => $dem ? $dem->numero_demande : null,
                    'nom_equipe' => $nomEquipe,
                    'chef_equipe' => $chefEquipe,
                    'technicien' => $technicienNom,
                    'client_nom' => $clientNom,
                    'site' => $siteNom,
                    'base' => $baseNom,
                    'equipement_code' => $eqCode,
                    'equipement_nom' => $eqNom,
                    'equipement_marque' => $eqMarque,
                    'equipement_modele' => $eqModele,
                    'ri_soutarah' => $riSoutarah,
                    'urgence' => $urgence,
                    'demandeur_nom' => $demandeurNom,
                    'demandeur_tel' => $demandeurTel,
                    'statut' => $statutDisplay,
                    'commentaire' => $description,
                ]
            ];
        });

        // Inclure les Demandes SEULEMENT si elles ont une opération technique créée avec équipe/technicien affecté
        // ET qu'elles n'ont PAS ENCORE de planification créée (pour éviter les doublons)
        $demandesQuery = \App\Models\Demande::with([
            'client',
            'site.baseSite',
            'equipement',
            'createdBy',
            'technicalOperation.equipe.chef',
            'technicalOperation.technicien',
            'technicalOperation.equipement.site.baseSite'
        ])
            ->whereHas('technicalOperation', function($q) {
                // La demande DOIT avoir une opération technique créée
                // ET cette opération DOIT avoir soit une équipe soit un technicien affecté
                $q->where(function($subQ) {
                    $subQ->whereNotNull('equipe_id')
                         ->orWhereNotNull('technicien_id');
                });
            })
            ->whereDoesntHave('planifications') // EXCLURE les demandes qui ont déjà une planification
            ->whereNotNull('date_debut_souhaitee');

        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $demandesQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $demandesQuery->where('client_id', $user->client_id);
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $demandesQuery->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $demandesQuery->where('client_id', $assignment->client_id);
                }
            }
        } elseif ($role === 'demandeur') {
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $demandesQuery->where(function($q) use ($user, $siteIds) {
                $q->where('created_by_user_id', $user->id);
                if (!empty($siteIds)) {
                    $q->orWhereIn('site_id', $siteIds);
                }
            });
        }

        $demandeEvents = $demandesQuery->get()->map(function ($d) {
            $type = $d->type_intervention ?? 'Dépannage';
            
            // Déterminer le statut réel et lisible de la demande
            $statutDisplay = 'Planifié';
            $isTermine = false;
            $demStatut = strtolower(trim($d->statut ?? ''));
            $demValid = strtolower(trim($d->statut_validation_finale_client ?? ''));
            
            if (in_array($demValid, ['validé', 'valide', 'conforme']) || in_array($demStatut, ['closed', 'cloturee', 'clôturée'])) {
                $statutDisplay = 'Validé Client';
                $isTermine = true;
            } elseif ($d->technicalOperation) {
                $opStatut = strtolower(trim($d->technicalOperation->statut ?? ''));
                $opValid = strtolower(trim($d->technicalOperation->statut_validation_finale_client ?? ''));
                
                if (in_array($opValid, ['validé', 'valide', 'conforme'])) {
                    $statutDisplay = 'Validé Client';
                    $isTermine = true;
                } elseif (in_array($opStatut, ['resolu', 'résolu', 'termine', 'terminé', 'closed', 'cloture', 'clôturé'])) {
                    $statutDisplay = 'Terminé';
                    $isTermine = true;
                } elseif ($opStatut === 'en cours') {
                    $statutDisplay = 'En cours';
                } elseif (in_array($opStatut, ['en attente', 'en attente assignation', 'approuve - en attente assignation', 'en_attente_piece'])) {
                    $statutDisplay = 'En attente';
                } elseif (!empty($d->technicalOperation->statut)) {
                    $statutDisplay = ucfirst(str_replace('_', ' ', $d->technicalOperation->statut));
                } else {
                    $statutDisplay = 'Planifié';
                }
            } elseif (in_array($demStatut, ['terminee', 'terminée', 'resolu', 'résolu'])) {
                $statutDisplay = 'Terminé';
                $isTermine = true;
            } elseif ($demStatut === 'en cours') {
                $statutDisplay = 'En cours';
            } else {
                $statutDisplay = 'Planifié';
            }
            
            $normType = strtolower(trim($type));
            if (in_array($normType, ['dépannage', 'depannage'])) {
                $color = $isTermine ? '#10b981' : '#dc2626'; // Rouge tant que pas terminé, Vert si terminé
            } elseif (in_array($normType, ['installation', 'installations'])) {
                $color = $isTermine ? '#10b981' : '#f59e0b'; // Orange tant que pas terminée, Vert si terminée
            } elseif (in_array($normType, ['maintenance', 'maintenances'])) {
                $color = $isTermine ? '#10b981' : '#3b82f6'; // Bleu tant que pas terminée, Vert si terminée
            } else {
                $color = $isTermine ? '#10b981' : '#dc2626';
            }

            $top = $d->technicalOperation;
            $eq = $top?->equipement ?? $d->equipement;
            $eqCode = $eq?->equipement_code ?? null;
            $eqNom = $eq?->equipement_nom ?? ($top?->equipement_reference ?? null);
            $eqMarque = $eq?->marque ?? null;
            $eqModele = $eq?->modele ?? null;

            $clientNom = $d->client ? $d->client->nom : 'Client';
            $siteNom = $d->site ? $d->site->nom_site : 'N/A';
            $baseNom = $d->site?->baseSite?->nom_base ?? null;

            $demandeurNom = $d->createdBy?->nom_complet ?? null;
            $demandeurTel = $d->createdBy?->telephone ?? null;

            $riSoutarah = $top?->ri_soutarah ?? $d->numero_reference_externe ?? null;
            $urgence = $top?->urgence ?? ($d->niveau_urgence ? ucfirst($d->niveau_urgence) : 'Normal');
            $description = $top?->description_panne ?? $d->description ?? '';

            $nomEquipe = 'Non assigné';
            $chefEquipe = null;
            $technicienNom = null;
            if ($top) {
                if ($top->equipe) {
                    $nomEquipe = $top->equipe->nom_equipe;
                    $chefEquipe = $top->equipe->chef?->nom_complet;
                }
                if ($top->technicien) {
                    $technicienNom = $top->technicien->nom_complet;
                }
            }

            $title = ucfirst($type) . ' - ' . $d->numero_demande;
            
            // Plage de dates : date_debut_souhaitee
            $startDate = $d->date_debut_souhaitee ? $d->date_debut_souhaitee->format('Y-m-d') : date('Y-m-d');
            $endDate = $d->date_debut_souhaitee ? $d->date_debut_souhaitee->copy()->addDay()->format('Y-m-d') : $startDate;

            return [
                'id' => 'demande_' . $d->id,
                'title' => $title,
                'start' => $startDate,
                'end' => $endDate,
                'color' => $color,
                'extendedProps' => [
                    'type' => $type,
                    'demande_id' => $d->id,
                    'depannage_id' => $top ? $top->id : null,
                    'demande_numero' => $d->numero_demande,
                    'nom_equipe' => $nomEquipe,
                    'chef_equipe' => $chefEquipe,
                    'technicien' => $technicienNom,
                    'client_nom' => $clientNom,
                    'site' => $siteNom,
                    'base' => $baseNom,
                    'equipement_code' => $eqCode,
                    'equipement_nom' => $eqNom,
                    'equipement_marque' => $eqMarque,
                    'equipement_modele' => $eqModele,
                    'ri_soutarah' => $riSoutarah,
                    'urgence' => $urgence,
                    'demandeur_nom' => $demandeurNom,
                    'demandeur_tel' => $demandeurTel,
                    'statut' => $statutDisplay,
                    'commentaire' => $description,
                ]
            ];
        });

        // Ajouter les maintenances au calendrier
        $maintenancesQuery = \App\Models\Maintenance::with(['client', 'site.baseSite', 'equipe.chef', 'equipes.chef', 'technicien']);
        
        // Filtrer selon le rôle (même logique que dans index)
        $user = Auth::user();
        if (in_array($user->type_utilisateur, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            if ($user->equipe_id) {
                $equipesIds[] = $user->equipe_id;
            }
            $equipesIds = array_unique(array_filter($equipesIds));

            $maintenancesQuery->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id)
                  ->orWhereIn('equipe_id', $equipesIds)
                  ->orWhereHas('equipes', function($eqQ) use ($equipesIds) {
                      $eqQ->whereIn('equipes.id', $equipesIds);
                  });
            });
        } elseif ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                $maintenancesQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $maintenancesQuery->where('client_id', $user->client_id);
            }
        } elseif ($user->isDemandeur()) {
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $baseIds = [];
            $clientIdsSites = [];
            if (!empty($siteIds)) {
                $sitesData = \App\Models\Site::whereIn('id', $siteIds)->get();
                $baseIds = $sitesData->pluck('base_id')->filter()->unique()->values()->toArray();
                $clientIdsSites = $sitesData->pluck('client_id')->filter()->unique()->values()->toArray();
            }

            $maintenancesQuery->where(function($q) use ($user, $siteIds, $baseIds, $clientIdsSites) {
                if (!empty($siteIds)) {
                    $q->whereIn('site_id', $siteIds);
                }
                if (!empty($baseIds)) {
                    $q->orWhereIn('base_id', $baseIds);
                }
                if (!empty($clientIdsSites)) {
                    $q->orWhereIn('client_id', $clientIdsSites);
                }
                if ($user->client_id) {
                    $q->orWhere('client_id', $user->client_id);
                }
            });
        }
        
        $maintenanceEvents = collect();
        $nonWorkingEvents = collect();

        foreach ($maintenancesQuery->get() as $m) {
            $mStatut = strtolower(trim($m->statut ?? ''));
            $isTerminee = in_array($mStatut, ['terminée', 'terminee', 'clôturée', 'cloturee']);
            if (strtolower(trim($m->statut_validation_client ?? '')) === 'validé' && !in_array($mStatut, ['planifiée', 'planifiee', 'en cours', 'en_cours'])) {
                $isTerminee = true;
            }
            $color = $isTerminee ? '#10b981' : '#3b82f6';

            $nomEquipes = ($m->equipes && $m->equipes->isNotEmpty())
                ? $m->equipes->pluck('nom_equipe')->join(', ')
                : ($m->equipe ? $m->equipe->nom_equipe : 'Non affecté');
            
            $equipesCount = ($m->equipes && $m->equipes->isNotEmpty())
                ? $m->equipes->count()
                : ($m->equipe ? 1 : 0);

            $clientNom = $m->client?->nom ?? 'Client';
            $siteNom = $m->site ? $m->site->nom_site : 'N/A';
            $baseNom = $m->site?->baseSite?->nom_base ?? null;
            $chefEquipe = $m->equipe?->chef?->nom_complet ?? null;

            $joursNonOuvrables = $m->jours_non_ouvrables ?? [];
            if (is_string($joursNonOuvrables)) {
                $joursNonOuvrables = json_decode($joursNonOuvrables, true) ?? [];
            }
            if (!is_array($joursNonOuvrables)) {
                $joursNonOuvrables = [];
            }

            $dateDebut = $m->date_debut_prevue ? \Carbon\Carbon::parse($m->date_debut_prevue) : null;
            $dateFin = $m->date_fin_prevue ? \Carbon\Carbon::parse($m->date_fin_prevue) : ($dateDebut ? $dateDebut->copy() : null);

            $commonExtendedProps = [
                'type' => 'Maintenance ' . $m->type_maintenance,
                'type_maintenance' => $m->type_maintenance,
                'maintenance_id' => $m->id,
                'numero' => $m->numero_maintenance,
                'client_nom' => $clientNom,
                'nom_equipe' => $nomEquipes,
                'chef_equipe' => $chefEquipe,
                'equipes_count' => $equipesCount,
                'technicien' => $m->technicien ? $m->technicien->nom_complet : 'Non affecté',
                'site' => $siteNom,
                'base' => $baseNom,
                'statut' => ucfirst($m->statut),
                'commentaire' => $m->description ?? '',
                'nombre_equipements_prevus' => $m->nombre_equipements_prevus ?? 0,
                'nombre_equipements_traites' => $m->nombre_equipements_traites ?? 0,
                'pourcentage_avancement' => $m->pourcentage_avancement ?? 0,
                'jours_non_ouvrables' => $joursNonOuvrables,
                'jours_ouvrables_count' => $m->jours_ouvrables_count,
            ];

            if (!$dateDebut) {
                continue;
            }

            // Si aucun jour non ouvrable n'est configuré, afficher l'événement d'un seul bloc
            if (empty($joursNonOuvrables)) {
                $title = $dateDebut->format('H:i') . ' Maintenance ' . $m->type_maintenance . ' - ' . $m->numero_maintenance;
                $maintenanceEvents->push([
                    'id' => 'maintenance_' . $m->id,
                    'title' => $title,
                    'start' => $dateDebut->format('Y-m-d\TH:i:s'),
                    'end' => $dateFin ? $dateFin->copy()->addDay()->format('Y-m-d\T00:00:00') : null,
                    'color' => $color,
                    'extendedProps' => $commonExtendedProps
                ]);
            } else {
                // Découpage en blocs consécutifs de jours ouvrables (sautant les jours non ouvrables)
                $cur = $dateDebut->copy()->startOfDay();
                $endLimit = $dateFin->copy()->startOfDay();
                $segmentStart = null;
                $segmentEnd = null;
                $segmentIndex = 1;

                while ($cur->lte($endLimit)) {
                    $dateStr = $cur->format('Y-m-d');
                    $isExcluded = in_array($dateStr, $joursNonOuvrables);

                    if ($isExcluded) {
                        // Si un segment ouvrable était en cours, le fermer et l'enregistrer
                        if ($segmentStart !== null) {
                            $title = $segmentStart->format('H:i') . ' Maint. ' . $m->type_maintenance . ' - ' . $m->numero_maintenance;
                            $maintenanceEvents->push([
                                'id' => 'maintenance_' . $m->id . '_seg_' . $segmentIndex++,
                                'title' => $title,
                                'start' => $segmentStart->format('Y-m-d\TH:i:s'),
                                'end' => $segmentEnd->copy()->addDay()->format('Y-m-d\T00:00:00'),
                                'color' => $color,
                                'extendedProps' => $commonExtendedProps
                            ]);
                            $segmentStart = null;
                            $segmentEnd = null;
                        }

                        // Créer l'étiquette / badge visuel pour le jour non ouvrable sauté
                        $nonWorkingKey = 'maint_non_working_' . $m->id . '_' . $dateStr;
                        $nonWorkingEvents->push([
                            'id' => $nonWorkingKey,
                            'title' => '🚫 Repos / Férié (' . $m->numero_maintenance . ')',
                            'start' => $cur->format('Y-m-d'),
                            'allDay' => true,
                            'color' => '#ef4444',
                            'textColor' => '#ffffff',
                            'extendedProps' => array_merge($commonExtendedProps, [
                                'is_non_working_day' => true,
                                'non_working_date' => $dateStr,
                                'statut' => 'Jour non ouvrable (sauté)',
                            ])
                        ]);
                    } else {
                        // Jour ouvrable
                        if ($segmentStart === null) {
                            $segmentStart = ($cur->isSameDay($dateDebut)) ? $dateDebut->copy() : $cur->copy();
                        }
                        $segmentEnd = $cur->copy();
                    }

                    $cur->addDay();
                }

                // Fermer le dernier segment s'il existe
                if ($segmentStart !== null) {
                    $title = $segmentStart->format('H:i') . ' Maint. ' . $m->type_maintenance . ' - ' . $m->numero_maintenance;
                    $maintenanceEvents->push([
                        'id' => 'maintenance_' . $m->id . '_seg_' . $segmentIndex++,
                        'title' => $title,
                        'start' => $segmentStart->format('Y-m-d\TH:i:s'),
                        'end' => $segmentEnd->copy()->addDay()->format('Y-m-d\T00:00:00'),
                        'color' => $color,
                        'extendedProps' => $commonExtendedProps
                    ]);
                }
            }
        }

        $allEvents = $events->concat($demandeEvents)->concat($maintenanceEvents)->concat($nonWorkingEvents);

        return response()->json($allEvents);
    }

    /**
     * API endpoint pour obtenir le nombre d'équipements sur un site
     */
    public function getSiteEquipmentCount($siteId)
    {
        $site = \App\Models\Site::findOrFail($siteId);
        $equipmentCount = $site->equipements()->count();
        
        return response()->json([
            'success' => true,
            'site_id' => $siteId,
            'equipment_count' => $equipmentCount
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'depannage_id' => 'required|exists:depannages,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'type_intervention' => 'nullable|string|in:Maintenance,Dépannage,Installation',
            'equipe_id' => 'nullable|integer|exists:equipes,id',
            'technicien_id' => 'nullable|integer|exists:utilisateurs,id',
            'equipements_par_jour' => 'nullable|integer|min:1|max:100',
            'commentaire' => 'nullable|string',
        ]);

        // Récupérer l'opération (depannage)
        $depannage = \App\Models\Depannage::with(['demande.client', 'demande.base', 'demande.site', 'demande.equipement'])->findOrFail($validated['depannage_id']);

        // Si la date_debut n'est pas fournie, prendre automatiquement la date convenue de la demande/opération
        if (empty($validated['date_debut'])) {
            $validated['date_debut'] = $depannage->date_prevue ?? $depannage->date_demande ?? date('Y-m-d');
        }
        
        // ═══════════════════════════════════════════════════════════════════
        // CALCUL AUTOMATIQUE DE LA DATE DE FIN POUR MAINTENANCE (AVEC JOURS OUVRABLES)
        // ═══════════════════════════════════════════════════════════════════
        $typeIntervention = $validated['type_intervention'] ?? $depannage->type_intervention ?? 'Maintenance';
        
        if (strtolower($typeIntervention) === 'maintenance' && $depannage->demande && $depannage->demande->site_id) {
            $siteId = $depannage->demande->site_id;
            $equipmentCount = \App\Models\Equipement::where('site_id', $siteId)->count();
            $equipementsParJour = (int) ($request->input('equipements_par_jour', 8));
            if ($equipementsParJour <= 0) {
                $equipementsParJour = 8;
            }
            
            // Jours non ouvrables
            $joursNonOuvrables = $request->input('jours_non_ouvrables', []);
            if (is_string($joursNonOuvrables)) {
                $joursNonOuvrables = json_decode($joursNonOuvrables, true) ?? [];
            }
            if (!is_array($joursNonOuvrables)) {
                $joursNonOuvrables = [];
            }

            if ($equipmentCount > 0) {
                $daysNeeded = (int) ceil($equipmentCount / $equipementsParJour);
                
                $cursor = \Carbon\Carbon::parse($validated['date_debut']);
                $workingDaysCount = 0;
                $lastActiveDate = $cursor->copy();
                $maxSafety = 365;

                while ($workingDaysCount < $daysNeeded && $maxSafety > 0) {
                    $maxSafety--;
                    $dateStr = $cursor->format('Y-m-d');
                    if (!in_array($dateStr, $joursNonOuvrables)) {
                        $workingDaysCount++;
                        $lastActiveDate = $cursor->copy();
                    }
                    if ($workingDaysCount < $daysNeeded) {
                        $cursor->addDay();
                    }
                }
                
                $validated['date_fin'] = $lastActiveDate->format('Y-m-d');
                
                \Log::info('[PLANNING MAINTENANCE] Calcul auto date_fin avec jours ouvrables', [
                    'site_id' => $siteId,
                    'equipment_count' => $equipmentCount,
                    'equipements_par_jour' => $equipementsParJour,
                    'days_needed' => $daysNeeded,
                    'date_debut' => $validated['date_debut'],
                    'date_fin' => $validated['date_fin'],
                    'jours_non_ouvrables' => $joursNonOuvrables
                ]);
            } else {
                $validated['date_fin'] = $validated['date_debut'];
            }
        } else {
            // Pour dépannage et installation : date_fin = date_debut si non fournie
            if (empty($validated['date_fin'])) {
                $validated['date_fin'] = $validated['date_debut'];
            }
        }

        // Vérifier que le superviseur_soutarah a accès à cette opération
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment && $depannage->demande) {
                if ($assignment->base_id && $depannage->demande->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $depannage->demande->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('planning.index')->with('error', 'Vous ne pouvez planifier que les opérations de votre périmètre.');
            }
        }

        // Vérifier qu'au moins équipe OU technicien est sélectionné
        if (empty($validated['equipe_id']) && empty($validated['technicien_id'])) {
            return back()->withErrors(['equipe_id' => 'Vous devez sélectionner au moins une équipe OU un technicien.'])->withInput();
        }

        // Déterminer automatiquement le type d'intervention si non fourni
        if (empty($validated['type_intervention'])) {
            $validated['type_intervention'] = $depannage->type_intervention ?? 'Maintenance';
        }

        $equipe = null;
        if ($validated['equipe_id']) {
            $equipe = Equipe::find($validated['equipe_id']);
        }

        // Créer la planification
        $planification = new Planification();
        $planification->depannage_id = $depannage->id;
        $planification->demande_id = $depannage->demande_id; // Lien vers la demande d'origine
        $planification->client_id = $depannage->demande ? $depannage->demande->client_id : $depannage->client_id;
        $planification->site_code = $depannage->demande && $depannage->demande->site ? $depannage->demande->site->code_site : 'N/A';
        $planification->nom_equipe = $equipe ? $equipe->nom_equipe : 'Technicien individuel';
        $planification->statut = 'Planifié';
        $planification->date_debut = $validated['date_debut'];
        $planification->date_fin = $validated['date_fin'];
        $planification->type_intervention = $validated['type_intervention'];
        $planification->equipe_id = $validated['equipe_id'];
        $planification->technicien_id = $validated['technicien_id'];
        $planification->commentaire = $validated['commentaire'];
        $planification->created_by = Auth::id();
        $planification->created_at = now();
        $planification->save();

        // ═══════════════════════════════════════════════════════════════════
        // NOTIFICATIONS : Notifier les techniciens/équipes de la planification
        // ═══════════════════════════════════════════════════════════════════
        $dateDebut = \Carbon\Carbon::parse($validated['date_debut'])->format('d/m/Y');
        
        if ($validated['technicien_id']) {
            // Notifier le technicien assigné
            $technicien = User::find($validated['technicien_id']);
            if ($technicien) {
                \App\Models\InterventionNotification::create([
                    'user_id' => $technicien->id,
                    'intervention_id' => $depannage->id,
                    'demande_id' => $depannage->demande_id,
                    'type' => 'planification_creee',
                    'message' => "Nouvelle intervention planifiée pour le {$dateDebut} - Opération #{$depannage->id} ({$validated['type_intervention']})",
                    'statut' => 'non_lu',
                ]);
            }
        }
        
        if ($validated['equipe_id']) {
            // Notifier tous les membres de l'équipe
            $equipe = Equipe::with('membres', 'chef')->find($validated['equipe_id']);
            if ($equipe) {
                // Notifier le chef d'équipe
                if ($equipe->chef) {
                    \App\Models\InterventionNotification::create([
                        'user_id' => $equipe->chef->id,
                        'intervention_id' => $depannage->id,
                        'demande_id' => $depannage->demande_id,
                        'type' => 'planification_creee',
                        'message' => "Nouvelle intervention planifiée pour votre équipe {$equipe->nom_equipe} le {$dateDebut} - Opération #{$depannage->id}",
                        'statut' => 'non_lu',
                    ]);
                }
                
                // Notifier tous les membres
                foreach ($equipe->membres as $membre) {
                    \App\Models\InterventionNotification::create([
                        'user_id' => $membre->id,
                        'intervention_id' => $depannage->id,
                        'demande_id' => $depannage->demande_id,
                        'type' => 'planification_creee',
                        'message' => "Nouvelle intervention planifiée pour votre équipe {$equipe->nom_equipe} le {$dateDebut} - Opération #{$depannage->id}",
                        'statut' => 'non_lu',
                    ]);
                }
            }
        }

        // Notifier également le demandeur créateur de la demande
        if ($depannage->demande_id) {
            $demandeOrigine = \App\Models\Demande::find($depannage->demande_id);
            if ($demandeOrigine && $demandeOrigine->created_by_user_id) {
                \App\Models\InterventionNotification::create([
                    'user_id' => $demandeOrigine->created_by_user_id,
                    'intervention_id' => $depannage->id,
                    'demande_id' => $depannage->demande_id,
                    'type' => 'planification_creee',
                    'message' => "Votre intervention pour la demande #{$demandeOrigine->numero_demande} est planifiée pour le {$dateDebut} (Opération #{$depannage->id}).",
                    'statut' => 'non_lu',
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'planification' => $planification]);
        }

        return redirect()->route('planning.index')->with('success', 'Opération #' . $depannage->id . ' planifiée avec succès !');
    }

    public function updateDate(Request $request, $id)
    {
        $user = Auth::user();
        $planification = Planification::with('demande')->findOrFail($id);

        // Vérifier que le superviseur_soutarah a accès à cette planification
        if ($user->type_utilisateur === 'superviseur_soutarah' && $planification->demande) {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $planification->demande->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $planification->demande->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return response()->json(['error' => 'Accès non autorisé'], 403);
            }
        }

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date',
        ]);

        $planification->date_debut = $request->date_debut;
        if ($request->filled('date_fin')) {
            $planification->date_fin = $request->date_fin;
        }
        $planification->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $planification = Planification::with('demande')->findOrFail($id);

        // Vérifier que le superviseur_soutarah a accès à cette planification
        if ($user->type_utilisateur === 'superviseur_soutarah' && $planification->demande) {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $planification->demande->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $planification->demande->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('planning.index')->with('error', 'Vous ne pouvez supprimer que les planifications de votre périmètre.');
            }
        }

        $planification->delete();

        return redirect()->route('planning.index')->with('success', 'Planification annulée.');
    }

    /**
     * Exporter le planning mensuel en PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;
        
        // Récupérer le mois et l'année depuis la requête
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        // Dates de début et fin du mois
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Récupérer les planifications du mois avec filtres selon le rôle
        $query = Planification::with(['demande.client', 'demande.site', 'depannage', 'equipe', 'technicien'])
            ->whereBetween('date_debut', [$startDate, $endDate]);
        
        // Appliquer les mêmes filtres que dans eventsJson()
        if (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            $query->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id)
                  ->orWhereIn('equipe_id', $equipesIds);
            });
        } elseif ($role === 'superviseur_client') {
            if ($user->base_id) {
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $planifications = $query->orderBy('date_debut')->get();
        
        // Récupérer aussi les demandes avec opération technique affectée
        $demandesQuery = \App\Models\Demande::with(['client', 'site', 'technicalOperation'])
            ->whereHas('technicalOperation', function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNotNull('equipe_id')
                         ->orWhereNotNull('technicien_id');
                });
            })
            ->whereNotNull('date_debut_souhaitee')
            ->whereBetween('date_debut_souhaitee', [$startDate, $endDate]);

        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $demandesQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $demandesQuery->where('client_id', $user->client_id);
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $demandesQuery->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $demandesQuery->where('client_id', $assignment->client_id);
                }
            }
        }

        $demandes = $demandesQuery->orderBy('date_debut_souhaitee')->get();
        
        // Récupérer les maintenances
        $maintenancesQuery = \App\Models\Maintenance::with(['client', 'site', 'equipe', 'technicien'])
            ->where(function($q) use ($startDate, $endDate) {
                // Maintenances qui chevauchent le mois (début avant fin de mois ET fin après début de mois)
                $q->where('date_debut_prevue', '<=', $endDate)
                  ->where(function($q2) use ($startDate) {
                      $q2->where('date_fin_prevue', '>=', $startDate)
                         ->orWhereNull('date_fin_prevue');
                  });
            });
        
        if (in_array($user->type_utilisateur, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            if ($user->equipe_id) {
                $equipesIds[] = $user->equipe_id;
            }
            $equipesIds = array_unique(array_filter($equipesIds));

            $maintenancesQuery->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                    $q->orWhereHas('equipes', function($eqQ) use ($equipesIds) {
                        $eqQ->whereIn('equipes.id', $equipesIds);
                    });
                }
            });
        } elseif ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                $maintenancesQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $maintenancesQuery->where('client_id', $user->client_id);
            }
        } elseif ($user->isDemandeur()) {
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $baseIds = [];
            $clientIdsSites = [];
            if (!empty($siteIds)) {
                $sitesData = \App\Models\Site::whereIn('id', $siteIds)->get();
                $baseIds = $sitesData->pluck('base_id')->filter()->unique()->values()->toArray();
                $clientIdsSites = $sitesData->pluck('client_id')->filter()->unique()->values()->toArray();
            }

            $maintenancesQuery->where(function($q) use ($user, $siteIds, $baseIds, $clientIdsSites) {
                if (!empty($siteIds)) {
                    $q->whereIn('site_id', $siteIds);
                }
                if (!empty($baseIds)) {
                    $q->orWhereIn('base_id', $baseIds);
                }
                if (!empty($clientIdsSites)) {
                    $q->orWhereIn('client_id', $clientIdsSites);
                }
                if ($user->client_id) {
                    $q->orWhere('client_id', $user->client_id);
                }
            });
        }
        
        $maintenances = $maintenancesQuery->orderBy('date_debut_prevue')->get();
        
        // Construire le calendrier mensuel
        $calendar = $this->buildMonthCalendar($year, $month, $planifications, $demandes, $maintenances);
        
        // Générer le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('planning.export-pdf', [
            'user' => $user,
            'month' => $month,
            'year' => $year,
            'monthName' => $startDate->translatedFormat('F'),
            'calendar' => $calendar,
            'planifications' => $planifications,
            'demandes' => $demandes,
            'maintenances' => $maintenances,
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'planning_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '_' . $user->nom . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Vue d'impression du planning (pour impression navigateur / PDF)
     */
    public function printView(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;
        
        // Récupérer le mois et l'année depuis la requête
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        // Dates de début et fin du mois
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Récupérer les planifications du mois avec filtres selon le rôle
        $query = Planification::with(['demande.client', 'demande.site', 'depannage', 'equipe', 'technicien'])
            ->whereBetween('date_debut', [$startDate, $endDate]);
        
        // Appliquer les mêmes filtres que dans eventsJson()
        if (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            $query->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id)
                  ->orWhereIn('equipe_id', $equipesIds);
            });
        } elseif ($role === 'superviseur_client') {
            if ($user->base_id) {
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $query->whereHas('demande', function($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('demande', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $planifications = $query->orderBy('date_debut')->get();
        
        // NE PAS inclure les demandes non planifiées dans le PDF pour éviter doublons
        $demandes = collect();
        
        // Récupérer les maintenances
        $maintenancesQuery = \App\Models\Maintenance::with(['client', 'site', 'equipe', 'technicien'])
            ->where(function($q) use ($startDate, $endDate) {
                // Maintenances qui chevauchent le mois (début avant fin de mois ET fin après début de mois)
                $q->where('date_debut_prevue', '<=', $endDate)
                  ->where(function($q2) use ($startDate) {
                      $q2->where('date_fin_prevue', '>=', $startDate)
                         ->orWhereNull('date_fin_prevue');
                  });
            });
        
        if (in_array($user->type_utilisateur, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            if ($user->equipe_id) {
                $equipesIds[] = $user->equipe_id;
            }
            $equipesIds = array_unique(array_filter($equipesIds));

            $maintenancesQuery->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                    $q->orWhereHas('equipes', function($eqQ) use ($equipesIds) {
                        $eqQ->whereIn('equipes.id', $equipesIds);
                    });
                }
            });
        } elseif ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                $maintenancesQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $maintenancesQuery->where('client_id', $user->client_id);
            }
        } elseif ($user->isDemandeur()) {
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $baseIds = [];
            $clientIdsSites = [];
            if (!empty($siteIds)) {
                $sitesData = \App\Models\Site::whereIn('id', $siteIds)->get();
                $baseIds = $sitesData->pluck('base_id')->filter()->unique()->values()->toArray();
                $clientIdsSites = $sitesData->pluck('client_id')->filter()->unique()->values()->toArray();
            }

            $maintenancesQuery->where(function($q) use ($user, $siteIds, $baseIds, $clientIdsSites) {
                if (!empty($siteIds)) {
                    $q->whereIn('site_id', $siteIds);
                }
                if (!empty($baseIds)) {
                    $q->orWhereIn('base_id', $baseIds);
                }
                if (!empty($clientIdsSites)) {
                    $q->orWhereIn('client_id', $clientIdsSites);
                }
                if ($user->client_id) {
                    $q->orWhere('client_id', $user->client_id);
                }
            });
        }
        
        $maintenances = $maintenancesQuery->orderBy('date_debut_prevue')->get();
        
        // Construire le calendrier mensuel
        $calendar = $this->buildMonthCalendar($year, $month, $planifications, $demandes, $maintenances);
        
        // Retourner la vue d'impression
        return view('planning.print', [
            'user' => $user,
            'month' => $month,
            'year' => $year,
            'monthName' => $startDate->translatedFormat('F'),
            'calendar' => $calendar,
            'planifications' => $planifications,
            'demandes' => $demandes,
            'maintenances' => $maintenances,
        ]);
    }
    
    /**
     * Construire la structure du calendrier mensuel
     */
    private function buildMonthCalendar($year, $month, $planifications, $demandes, $maintenances)
    {
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;
        $firstDayOfWeek = $startDate->dayOfWeek; // 0 = Dimanche, 6 = Samedi
        
        // Ajuster pour commencer par Lundi (1 = Lundi, 7 = Dimanche)
        $firstDayOfWeek = $firstDayOfWeek == 0 ? 7 : $firstDayOfWeek;
        
        $calendar = [];
        $currentWeek = [];
        
        // Remplir les jours vides avant le premier jour du mois
        for ($i = 1; $i < $firstDayOfWeek; $i++) {
            $currentWeek[] = null;
        }
        
        // Remplir les jours du mois
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::createFromDate($year, $month, $day)->startOfDay();
            
            // Collecter les interventions de ce jour
            $dayInterventions = [];
            
            // Planifications
            foreach ($planifications as $p) {
                if (!$p->date_debut) continue;
                $pStart = \Carbon\Carbon::parse($p->date_debut)->startOfDay();
                $pEnd = $p->date_fin ? \Carbon\Carbon::parse($p->date_fin)->startOfDay() : $pStart;
                
                if ($date->between($pStart, $pEnd)) {
                    $type = $p->type_intervention ?? ($p->demande ? $p->demande->type_intervention : 'Dépannage');
                    $time = \Carbon\Carbon::parse($p->date_debut)->format('H:i');
                    $label = $p->demande ? $p->demande->numero_demande : 'P-' . $p->id;
                    $clientNom = $p->demande && $p->demande->client ? $p->demande->client->nom : ($p->client ? $p->client->nom : '');
                    
                    $isMultiDay = $pStart->ne($pEnd);
                    $isStart = $date->isSameDay($pStart);
                    $isEnd = $date->isSameDay($pEnd);
                    
                    $position = 'single';
                    if ($isMultiDay) {
                        if ($isStart) $position = 'start';
                        elseif ($isEnd) $position = 'end';
                        else $position = 'middle';
                    }

                    $dayInterventions[] = [
                        'type' => $type,
                        'time' => $time,
                        'label' => $label,
                        'client' => $clientNom,
                        'equipe' => $p->equipe ? $p->equipe->nom_equipe : ($p->technicien ? $p->technicien->nom_complet : ''),
                        'is_multi_day' => $isMultiDay,
                        'position' => $position,
                        'is_start' => $isStart,
                        'is_end' => $isEnd,
                    ];
                }
            }
            
            // Demandes
            foreach ($demandes as $d) {
                if ($d->date_debut_souhaitee && \Carbon\Carbon::parse($d->date_debut_souhaitee)->startOfDay()->isSameDay($date)) {
                    $type = $d->type_intervention ?? 'Dépannage';
                    $time = \Carbon\Carbon::parse($d->date_debut_souhaitee)->format('H:i');
                    $label = $d->numero_demande;
                    $clientNom = $d->client ? $d->client->nom : '';
                    
                    $dayInterventions[] = [
                        'type' => $type,
                        'time' => $time,
                        'label' => $label,
                        'client' => $clientNom,
                        'equipe' => '',
                        'is_multi_day' => false,
                        'position' => 'single',
                        'is_start' => true,
                        'is_end' => true,
                    ];
                }
            }
            
            // Maintenances — afficher sur TOUS les jours de la plage date_debut_prevue → date_fin_prevue
            foreach ($maintenances as $m) {
                if (!$m->date_debut_prevue) continue;
                
                $mStart = \Carbon\Carbon::parse($m->date_debut_prevue)->startOfDay();
                $mEnd = $m->date_fin_prevue ? \Carbon\Carbon::parse($m->date_fin_prevue)->startOfDay() : $mStart;
                
                // Vérifier si le jour courant est dans la plage [début, fin]
                if ($date->between($mStart, $mEnd)) {
                    $time = \Carbon\Carbon::parse($m->date_debut_prevue)->format('H:i');
                    $label = $m->numero_maintenance;
                    $clientNom = $m->client ? $m->client->nom : ($m->site ? $m->site->nom_site : '');
                    $equipeNom = $m->equipe ? $m->equipe->nom_equipe : ($m->technicien ? $m->technicien->nom_complet : '');
                    
                    $isMultiDay = $mStart->ne($mEnd);
                    $isStart = $date->isSameDay($mStart);
                    $isEnd = $date->isSameDay($mEnd);
                    
                    $position = 'single';
                    if ($isMultiDay) {
                        if ($isStart) $position = 'start';
                        elseif ($isEnd) $position = 'end';
                        else $position = 'middle';
                    }
                    
                    $dayInterventions[] = [
                        'type' => 'Maintenance',
                        'time' => $time,
                        'label' => $label,
                        'client' => $clientNom,
                        'equipe' => $equipeNom,
                        'is_multi_day' => $isMultiDay,
                        'position' => $position,
                        'is_start' => $isStart,
                        'is_end' => $isEnd,
                        'nombre_equipements_prevus' => $m->nombre_equipements_prevus ?? 0,
                        'nombre_equipements_traites' => $m->nombre_equipements_traites ?? 0,
                        'pourcentage_avancement' => $m->pourcentage_avancement ?? 0,
                        'maintenance_id' => $m->id,
                    ];
                }
            }

            $currentWeek[] = [
                'day' => $day,
                'date' => $date,
                'events' => count($dayInterventions),
                'interventions' => $dayInterventions,
                'isWeekend' => $date->isWeekend(),
                'isToday' => $date->isToday(),
            ];
            
            // Si on est dimanche (fin de semaine), on passe à la semaine suivante
            if ($date->dayOfWeek == 0) {
                $calendar[] = $currentWeek;
                $currentWeek = [];
            }
        }
        
        // Compléter la dernière semaine si nécessaire
        if (!empty($currentWeek)) {
            while (count($currentWeek) < 7) {
                $currentWeek[] = null;
            }
            $calendar[] = $currentWeek;
        }
        
        return $calendar;
    }
}

