<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Client;
use App\Models\BaseSite;
use App\Models\Site;
use App\Models\Equipement;
use App\Models\Equipe;
use App\Models\User;
use App\Models\Assignment;
use App\Models\InterventionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    /**
     * Liste des maintenances
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Maintenance::with(['client', 'base', 'site', 'equipe', 'technicien', 'createdBy']);

        // Filtrage selon le rôle
        if ($user->isTechnicien()) {
            // Technicien : ses maintenances ou celles de ses équipes
            $equipesIds = $user->equipes->pluck('id')->toArray();
            if ($user->equipe_id) {
                $equipesIds[] = $user->equipe_id;
            }
            $equipesIds = array_unique(array_filter($equipesIds));

            $query->where(function($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id)
                  ->orWhereIn('equipe_id', $equipesIds)
                  ->orWhereHas('equipes', function($eqQ) use ($equipesIds) {
                      $eqQ->whereIn('equipes.id', $equipesIds);
                  });
            });
        } elseif ($user->isSuperviseurClient()) {
            // Superviseur Client : maintenances de son périmètre
            if ($user->base_id) {
                $query->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $query->where('client_id', $user->client_id);
            }
        } elseif ($user->isSuperviseurSoutarah()) {
            // Superviseur Soutarah : maintenances de ses clients/bases assignés
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $query->where('client_id', $assignment->client_id);
                }
            }
        }
        // Admin voit tout

        // Filtres
        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('numero_maintenance', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $maintenances = $query->orderBy('date_debut_prevue', 'desc')->paginate(15);

        // Compteurs
        $baseQuery = clone $query;
        $totalCount = $baseQuery->count();
        
        $planifieesCount = (clone $query)->where('statut', 'planifiée')->count();
        $confirmeesCount = (clone $query)->where('statut', 'confirmée_client')->count();
        $enCoursCount = (clone $query)->where('statut', 'en_cours')->count();
        $termineesCount = (clone $query)->where('statut', 'terminée')->count();

        return view('maintenances.index', compact(
            'maintenances', 
            'totalCount', 
            'planifieesCount', 
            'confirmeesCount', 
            'enCoursCount', 
            'termineesCount'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403, 'Seuls les administrateurs et superviseurs Soutarah peuvent planifier des maintenances.');
        }

        // Charger les données pour le formulaire
        $baseAssignee = null;
        
        if ($user->isAdmin()) {
            $clients = Client::orderBy('nom')->get();
        } else {
            // Superviseur Soutarah : uniquement SA base assignée
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment && $assignment->base_id) {
                $base = BaseSite::with('client')->find($assignment->base_id);
                $clients = collect([$base->client]);
                $baseAssignee = $base;
            } elseif ($assignment && $assignment->client_id) {
                $clients = Client::where('id', $assignment->client_id)->get();
            } else {
                $clients = collect();
            }
        }

        $equipes = Equipe::with('membres')->orderBy('nom_equipe')->get();

        return view('maintenances.create', compact('clients', 'equipes', 'baseAssignee'));
    }

    /**
     * Enregistrer une nouvelle maintenance
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403);
        }

        // Prise en charge de la sélection par cases à cocher multiples (equipes_ids[]) et compatibilité champ unique (equipe_id)
        $equipesIds = (array) $request->input('equipes_ids', []);
        if (empty($equipesIds) && $request->filled('equipe_id')) {
            $equipesIds = [$request->input('equipe_id')];
        }
        $equipesIds = array_values(array_unique(array_filter($equipesIds)));
        $equipePrincipale = $equipesIds[0] ?? null;
        $equipesSupport = !empty($equipesIds) ? array_slice($equipesIds, 1) : [];
        $request->merge([
            'equipe_id' => $equipePrincipale,
            'equipes_support' => $equipesSupport,
        ]);

        $validated = $request->validate([
            'type_maintenance' => 'required|in:préventive,corrective programmée',
            'client_id' => 'required|exists:clients,id',
            'base_id' => 'nullable|exists:bases,id',
            'site_id' => 'nullable|exists:sites,id',
            'description' => 'required|string',
            'taches_prevues' => 'nullable|string',
            'pieces_prevues' => 'nullable|string',
            'nombre_equipements_prevus' => 'nullable|integer|min:0',
            'equipements_par_jour' => 'nullable|integer|min:1|max:100',
            'date_debut_prevue' => 'required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
            'equipe_id' => 'required|exists:equipes,id', // Au moins une équipe participante
            'equipes_support' => 'nullable|array',
            'equipes_support.*' => 'exists:equipes,id',
        ], [
            'equipe_id.required' => 'Veuillez cocher au moins une équipe participant à la maintenance.',
        ]);

        $equipementsParJour = !empty($validated['equipements_par_jour']) 
            ? (int) $validated['equipements_par_jour'] 
            : (int) $request->input('equipements_par_jour', 8);
        if ($equipementsParJour <= 0) {
            $equipementsParJour = 8;
        }

        // Récupérer la liste des jours non ouvrables (chômés/fériés/repos)
        $rawJours = $request->input('jours_non_ouvrables');
        if (is_string($rawJours)) {
            $joursNonOuvrables = json_decode($rawJours, true) ?: [];
        } elseif (is_array($rawJours)) {
            $joursNonOuvrables = $rawJours;
        } else {
            $joursNonOuvrables = [];
        }

        // Récupérer la liste des mois de répétition périodique
        $rawMois = $request->input('mois_repetition');
        if (is_string($rawMois)) {
            $moisRepetition = json_decode($rawMois, true) ?: [];
        } elseif (is_array($rawMois)) {
            $moisRepetition = $rawMois;
        } else {
            $moisRepetition = [];
        }

        // Auto-migration préventive si les colonnes n'existent pas encore
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('maintenances')) {
                \Illuminate\Support\Facades\Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'equipements_par_jour')) {
                        $table->integer('equipements_par_jour')->default(8)->nullable()->after('nombre_equipements_prevus');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                        $table->json('jours_non_ouvrables')->nullable()->after('equipements_par_jour');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                        $table->integer('jours_ouvrables_count')->nullable()->after('jours_non_ouvrables');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('[MAINTENANCE] Auto-migration : ' . $e->getMessage());
        }

        try {
            DB::transaction(function() use ($request, $validated, $user, $equipementsParJour, $joursNonOuvrables, $moisRepetition) {
                // Comptabiliser automatiquement le nombre d'équipements du site, de la base ou du client
                $siteId = $validated['site_id'] ?? null;
                $baseId = $validated['base_id'] ?? null;
                $clientId = $validated['client_id'] ?? null;

                $nombreEquipementsCalcul = 0;
                if ($siteId) {
                    $nombreEquipementsCalcul = Equipement::where('site_id', $siteId)->count();
                } elseif ($baseId) {
                    $nombreEquipementsCalcul = Equipement::where('base_id', $baseId)->count();
                } elseif ($clientId) {
                    $nombreEquipementsCalcul = Equipement::where('client_id', $clientId)->count();
                }

                // ═══════════════════════════════════════════════════════════════════
                // CALCUL AUTOMATIQUE DE LA DATE DE FIN EN SAUTANT LES JOURS NON OUVRABLES
                // ═══════════════════════════════════════════════════════════════════
                $joursNecessaires = (int) ceil(($nombreEquipementsCalcul > 0 ? $nombreEquipementsCalcul : 8) / $equipementsParJour);
                if ($joursNecessaires < 1) $joursNecessaires = 1;

                if (empty($validated['date_fin_prevue']) || !empty($joursNonOuvrables)) {
                    $dateCurseur = \Carbon\Carbon::parse($validated['date_debut_prevue'])->copy();
                    $joursTravailles = 0;
                    $maxIterations = 365;
                    $iterations = 0;

                    while ($joursTravailles < $joursNecessaires && $iterations < $maxIterations) {
                        $dateStr = $dateCurseur->format('Y-m-d');
                        if (!in_array($dateStr, $joursNonOuvrables)) {
                            $joursTravailles++;
                            if ($joursTravailles >= $joursNecessaires) {
                                break;
                            }
                        }
                        $dateCurseur->addDay();
                        $iterations++;
                    }

                    $validated['date_fin_prevue'] = $dateCurseur->format('Y-m-d H:i:s');
                    
                    \Log::info('[MAINTENANCE] Calcul date_fin avec jours ouvrables', [
                        'equipment_count' => $nombreEquipementsCalcul,
                        'equipements_par_jour' => $equipementsParJour,
                        'jours_ouvrables_requis' => $joursNecessaires,
                        'jours_non_ouvrables_sautes' => count($joursNonOuvrables),
                        'date_debut' => $validated['date_debut_prevue'],
                        'date_fin_calculee' => $validated['date_fin_prevue']
                    ]);
                }

                $maintenanceData = [
                    'numero_maintenance' => Maintenance::genererNumero(),
                    'type_maintenance' => $validated['type_maintenance'],
                    'client_id' => $validated['client_id'],
                    'base_id' => $validated['base_id'] ?? null,
                    'site_id' => $validated['site_id'] ?? null,
                    'description' => $validated['description'],
                    'taches_prevues' => $validated['taches_prevues'] ?? null,
                    'pieces_prevues' => $validated['pieces_prevues'] ?? null,
                    'nombre_equipements_prevus' => $nombreEquipementsCalcul,
                    'nombre_equipements_traites' => 0,
                    'date_debut_prevue' => $validated['date_debut_prevue'],
                    'date_fin_prevue' => $validated['date_fin_prevue'] ?? null,
                    'equipe_id' => $validated['equipe_id'],
                    'technicien_id' => null,
                    'statut' => 'planifiée',
                    'created_by_user_id' => $user->id,
                    'created_by_role' => $user->type_utilisateur,
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'equipements_par_jour')) {
                    $maintenanceData['equipements_par_jour'] = $equipementsParJour;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                    $maintenanceData['jours_non_ouvrables'] = $joursNonOuvrables;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                    $maintenanceData['jours_ouvrables_count'] = $joursNecessaires;
                }

                $maintenance = Maintenance::create($maintenanceData);

                // ═══════════════════════════════════════════════════════════════════
                // NOUVEAU: Affectation des équipes (many-to-many)
                // ═══════════════════════════════════════════════════════════════════
                
                // 1. Attacher l'équipe principale avec role='principale'
                $maintenance->equipes()->attach($validated['equipe_id'], [
                    'role' => 'principale',
                    'date_affectation' => now()
                ]);

                // 2. Attacher les équipes de support si fournies
                if (!empty($validated['equipes_support'])) {
                    foreach ($validated['equipes_support'] as $equipeId) {
                        // Éviter d'attacher l'équipe principale deux fois
                        if ($equipeId != $validated['equipe_id']) {
                            $maintenance->equipes()->attach($equipeId, [
                                'role' => 'support',
                                'date_affectation' => now()
                            ]);
                        }
                    }
                }

                // Notifier les superviseurs client
                $superviseurs = User::where('type_utilisateur', 'superviseur_client')
                    ->where(function($q) use ($validated) {
                        if (!empty($validated['base_id'])) {
                            $q->where('base_id', $validated['base_id']);
                        }
                        if (!empty($validated['client_id'])) {
                            $q->orWhere('client_id', $validated['client_id']);
                        }
                    })->get();

                foreach ($superviseurs as $superviseur) {
                    InterventionNotification::create([
                        'user_id' => $superviseur->id,
                        'maintenance_id' => $maintenance->id,
                        'type' => 'nouvelle_maintenance_planifiee',
                        'message' => "Nouvelle maintenance planifiée #{$maintenance->numero_maintenance} pour votre équipement.",
                        'statut' => 'non_lu',
                    ]);
                }

                // Notifier les demandeurs du site ou du client
                $demandeurs = User::where('type_utilisateur', 'demandeur')
                    ->where(function($q) use ($validated) {
                        if (!empty($validated['site_id'])) {
                            $q->where('site_id', $validated['site_id'])
                              ->orWhereHas('sitesAssignes', function($sq) use ($validated) {
                                  $sq->where('sites.id', $validated['site_id']);
                              });
                        } elseif (!empty($validated['client_id'])) {
                            $q->where('client_id', $validated['client_id']);
                        }
                    })->get();

                foreach ($demandeurs as $demandeur) {
                    InterventionNotification::create([
                        'user_id' => $demandeur->id,
                        'maintenance_id' => $maintenance->id,
                        'type' => 'nouvelle_maintenance_planifiee',
                        'message' => "Nouvelle maintenance planifiée #{$maintenance->numero_maintenance} sur votre site.",
                        'statut' => 'non_lu',
                    ]);
                }

                // ═══════════════════════════════════════════════════════════════════
                // RÉPÉTITION SUR LES MOIS SUIVANTS DE L'ANNÉE (PÉRIODICITÉ)
                // ═══════════════════════════════════════════════════════════════════
                if (!empty($moisRepetition)) {
                    $dateDebutOriginale = \Carbon\Carbon::parse($validated['date_debut_prevue']);
                    $jourDebutMois = (int) $dateDebutOriginale->format('d');
                    $heureDebut = (int) $dateDebutOriginale->format('H');
                    $minuteDebut = (int) $dateDebutOriginale->format('i');

                    // Identifier les jours de la semaine exclus (ex: dimanches, samedis)
                    $excludedDaysOfWeek = [];
                    foreach ($joursNonOuvrables as $jnd) {
                        try {
                            $excludedDaysOfWeek[] = \Carbon\Carbon::parse($jnd)->dayOfWeek;
                        } catch (\Exception $e) {}
                    }
                    $excludedDaysOfWeek = array_unique($excludedDaysOfWeek);

                    foreach ($moisRepetition as $moisTarget) {
                        // $moisTarget format: 'YYYY-MM'
                        try {
                            $parts = explode('-', $moisTarget);
                            if (count($parts) !== 2) continue;
                            $y = (int) $parts[0];
                            $m = (int) $parts[1];

                            $joursDansMois = \Carbon\Carbon::create($y, $m, 1)->daysInMonth;
                            $jourDebutAjuste = min($jourDebutMois, $joursDansMois);

                            $debutReplication = \Carbon\Carbon::create($y, $m, $jourDebutAjuste, $heureDebut, $minuteDebut, 0);

                            // Calculer les jours non ouvrables pour ce mois basé sur les jours récurrents exclus
                            $repJoursNonOuvrables = [];
                            $curRep = $debutReplication->copy();
                            $repJoursTravailles = 0;
                            $repIterations = 0;

                            while ($repJoursTravailles < $joursNecessaires && $repIterations < 365) {
                                $dateStrRep = $curRep->format('Y-m-d');
                                if (in_array($curRep->dayOfWeek, $excludedDaysOfWeek)) {
                                    $repJoursNonOuvrables[] = $dateStrRep;
                                } else {
                                    $repJoursTravailles++;
                                    if ($repJoursTravailles >= $joursNecessaires) {
                                        break;
                                    }
                                }
                                $curRep->addDay();
                                $repIterations++;
                            }

                            $finReplication = $curRep->copy();

                            // Créer la maintenance indépendante pour ce mois
                            $repData = [
                                'numero_maintenance' => Maintenance::genererNumero(),
                                'type_maintenance' => $validated['type_maintenance'],
                                'client_id' => $validated['client_id'],
                                'base_id' => $validated['base_id'] ?? null,
                                'site_id' => $validated['site_id'] ?? null,
                                'description' => $validated['description'],
                                'taches_prevues' => $validated['taches_prevues'] ?? null,
                                'pieces_prevues' => $validated['pieces_prevues'] ?? null,
                                'nombre_equipements_prevus' => $nombreEquipementsCalcul,
                                'nombre_equipements_traites' => 0,
                                'date_debut_prevue' => $debutReplication->format('Y-m-d H:i:s'),
                                'date_fin_prevue' => $finReplication->format('Y-m-d H:i:s'),
                                'equipe_id' => $validated['equipe_id'],
                                'technicien_id' => null,
                                'statut' => 'planifiée',
                                'created_by_user_id' => $user->id,
                                'created_by_role' => $user->type_utilisateur,
                            ];

                            if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'equipements_par_jour')) {
                                $repData['equipements_par_jour'] = $equipementsParJour;
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                                $repData['jours_non_ouvrables'] = $repJoursNonOuvrables;
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                                $repData['jours_ouvrables_count'] = $joursNecessaires;
                            }

                            $repMaintenance = Maintenance::create($repData);

                            // Affecter l'équipe principale et support
                            $repMaintenance->equipes()->attach($validated['equipe_id'], [
                                'role' => 'principale',
                                'date_affectation' => now()
                            ]);

                            if (!empty($validated['equipes_support'])) {
                                foreach ($validated['equipes_support'] as $eqSupId) {
                                    if ($eqSupId != $validated['equipe_id']) {
                                        $repMaintenance->equipes()->attach($eqSupId, [
                                            'role' => 'support',
                                            'date_affectation' => now()
                                        ]);
                                    }
                                }
                            }
                        } catch (\Exception $eRep) {
                            \Log::warning('[MAINTENANCE REPETITION] Erreur sur ' . $moisTarget . ' : ' . $eRep->getMessage());
                        }
                    }
                }
            });

            $nbRep = count($moisRepetition);
            $msg = $nbRep > 0 
                ? "Maintenance planifiée avec succès, ainsi que {$nbRep} maintenance(s) répétée(s) pour les mois sélectionnés !" 
                : 'Maintenance planifiée avec succès.';

            return redirect()->route('planning.index')->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('[MAINTENANCE CREATION ERROR] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la création de la maintenance : ' . $e->getMessage());
        }
    }

    /**
     * Vérifier si l'utilisateur a le droit de gérer (modifier/supprimer) la maintenance
     */
    private function authorizeMaintenanceManage(Maintenance $maintenance)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSuperviseurSoutarah()) {
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id && $maintenance->base_id == $assignment->base_id) {
                    return true;
                }
                if ($assignment->client_id && $maintenance->client_id == $assignment->client_id) {
                    return true;
                }
            }
        }

        abort(403, 'Vous n\'êtes pas autorisé à modifier ou supprimer cette maintenance.');
    }

    /**
     * Formulaire d'édition d'une maintenance
     */
    public function edit(Maintenance $maintenance)
    {
        $this->authorizeMaintenanceManage($maintenance);

        $user = Auth::user();
        $baseAssignee = null;

        if ($user->isAdmin()) {
            $clients = Client::orderBy('nom')->get();
        } else {
            // Superviseur Soutarah : uniquement sa base assignée ou son client
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment && $assignment->base_id) {
                $base = BaseSite::with('client')->find($assignment->base_id);
                $clients = collect([$base->client]);
                $baseAssignee = $base;
            } elseif ($assignment && $assignment->client_id) {
                $clients = Client::where('id', $assignment->client_id)->get();
            } else {
                $clients = collect();
            }
        }

        $equipes = Equipe::with('membres')->orderBy('nom_equipe')->get();

        // Récupérer les IDs des équipes actuellement associées
        $equipesIdsSelected = $maintenance->equipes->pluck('id')->toArray();
        if ($maintenance->equipe_id && !in_array($maintenance->equipe_id, $equipesIdsSelected)) {
            $equipesIdsSelected[] = $maintenance->equipe_id;
        }

        // Récupérer les bases et sites disponibles pour préremplir
        $bases = collect();
        $sites = collect();
        if ($maintenance->client_id) {
            $bases = BaseSite::where('client_id', $maintenance->client_id)->orderBy('nom_base')->get();
            if ($maintenance->base_id) {
                $sites = Site::where('base_id', $maintenance->base_id)->orderBy('nom_site')->get();
            } else {
                $sites = Site::where('client_id', $maintenance->client_id)->orderBy('nom_site')->get();
            }
        }

        return view('maintenances.edit', compact(
            'maintenance',
            'clients',
            'bases',
            'sites',
            'equipes',
            'equipesIdsSelected',
            'baseAssignee'
        ));
    }

    /**
     * Mettre à jour une maintenance existante
     */
    public function update(Request $request, Maintenance $maintenance)
    {
        $this->authorizeMaintenanceManage($maintenance);

        // Prise en charge de la sélection par cases à cocher multiples (equipes_ids[]) et compatibilité champ unique (equipe_id)
        $equipesIds = (array) $request->input('equipes_ids', []);
        if (empty($equipesIds) && $request->filled('equipe_id')) {
            $equipesIds = [$request->input('equipe_id')];
        }
        $equipesIds = array_values(array_unique(array_filter($equipesIds)));
        $equipePrincipale = $equipesIds[0] ?? null;
        $equipesSupport = !empty($equipesIds) ? array_slice($equipesIds, 1) : [];
        $request->merge([
            'equipe_id' => $equipePrincipale,
            'equipes_support' => $equipesSupport,
        ]);

        $validated = $request->validate([
            'type_maintenance' => 'required|in:préventive,corrective programmée',
            'client_id' => 'required|exists:clients,id',
            'base_id' => 'nullable|exists:bases,id',
            'site_id' => 'nullable|exists:sites,id',
            'description' => 'required|string',
            'taches_prevues' => 'nullable|string',
            'pieces_prevues' => 'nullable|string',
            'equipements_par_jour' => 'nullable|integer|min:1|max:100',
            'statut' => 'required|in:planifiée,confirmée_client,en_cours,terminée,annulée',
            'date_debut_prevue' => 'required|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
            'equipe_id' => 'required|exists:equipes,id',
            'equipes_support' => 'nullable|array',
            'equipes_support.*' => 'exists:equipes,id',
        ], [
            'equipe_id.required' => 'Veuillez cocher au moins une équipe participant à la maintenance.',
        ]);

        $equipementsParJour = !empty($validated['equipements_par_jour']) 
            ? (int) $validated['equipements_par_jour'] 
            : (int) $request->input('equipements_par_jour', $maintenance->equipements_par_jour ?? 8);
        if ($equipementsParJour <= 0) {
            $equipementsParJour = 8;
        }

        // Récupérer la liste des jours non ouvrables (chômés/fériés/repos)
        $rawJours = $request->input('jours_non_ouvrables');
        if (is_string($rawJours)) {
            $joursNonOuvrables = json_decode($rawJours, true) ?: [];
        } elseif (is_array($rawJours)) {
            $joursNonOuvrables = $rawJours;
        } else {
            $joursNonOuvrables = $maintenance->jours_non_ouvrables ?? [];
        }

        // Auto-migration préventive si les colonnes n'existent pas encore
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('maintenances')) {
                \Illuminate\Support\Facades\Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'equipements_par_jour')) {
                        $table->integer('equipements_par_jour')->default(8)->nullable()->after('nombre_equipements_prevus');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                        $table->json('jours_non_ouvrables')->nullable()->after('equipements_par_jour');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                        $table->integer('jours_ouvrables_count')->nullable()->after('jours_non_ouvrables');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('[MAINTENANCE] Auto-migration : ' . $e->getMessage());
        }

        try {
            DB::transaction(function() use ($validated, $maintenance, $equipementsParJour, $joursNonOuvrables) {
                // Recomptage des équipements si nécessaire
                $siteId = $validated['site_id'] ?? null;
                $baseId = $validated['base_id'] ?? null;
                $clientId = $validated['client_id'] ?? null;

                $nombreEquipementsCalcul = 0;
                if ($siteId) {
                    $nombreEquipementsCalcul = Equipement::where('site_id', $siteId)->count();
                } elseif ($baseId) {
                    $nombreEquipementsCalcul = Equipement::where('base_id', $baseId)->count();
                } elseif ($clientId) {
                    $nombreEquipementsCalcul = Equipement::where('client_id', $clientId)->count();
                }

                $nbTotal = $nombreEquipementsCalcul > 0 ? $nombreEquipementsCalcul : ($maintenance->nombre_equipements_prevus > 0 ? $maintenance->nombre_equipements_prevus : 8);
                $joursNecessaires = (int) ceil($nbTotal / $equipementsParJour);
                if ($joursNecessaires < 1) $joursNecessaires = 1;

                // Calcul automatique de la date de fin si non fournie ou recalculée avec jours ouvrables
                if (empty($validated['date_fin_prevue']) || !empty($joursNonOuvrables)) {
                    $dateCurseur = \Carbon\Carbon::parse($validated['date_debut_prevue'])->copy();
                    $joursTravailles = 0;
                    $maxIterations = 365;
                    $iterations = 0;

                    while ($joursTravailles < $joursNecessaires && $iterations < $maxIterations) {
                        $dateStr = $dateCurseur->format('Y-m-d');
                        if (!in_array($dateStr, $joursNonOuvrables)) {
                            $joursTravailles++;
                            if ($joursTravailles >= $joursNecessaires) {
                                break;
                            }
                        }
                        $dateCurseur->addDay();
                        $iterations++;
                    }

                    $validated['date_fin_prevue'] = $dateCurseur->format('Y-m-d H:i:s');
                }

                $updateData = [
                    'type_maintenance' => $validated['type_maintenance'],
                    'client_id' => $validated['client_id'],
                    'base_id' => $validated['base_id'] ?? null,
                    'site_id' => $validated['site_id'] ?? null,
                    'description' => $validated['description'],
                    'taches_prevues' => $validated['taches_prevues'] ?? null,
                    'pieces_prevues' => $validated['pieces_prevues'] ?? null,
                    'nombre_equipements_prevus' => $nombreEquipementsCalcul > 0 ? $nombreEquipementsCalcul : $maintenance->nombre_equipements_prevus,
                    'date_debut_prevue' => $validated['date_debut_prevue'],
                    'date_fin_prevue' => $validated['date_fin_prevue'] ?? null,
                    'equipe_id' => $validated['equipe_id'],
                    'statut' => $validated['statut'],
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'equipements_par_jour')) {
                    $updateData['equipements_par_jour'] = $equipementsParJour;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                    $updateData['jours_non_ouvrables'] = $joursNonOuvrables;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                    $updateData['jours_ouvrables_count'] = $joursNecessaires;
                }

                $maintenance->update($updateData);

                // Synchronisation des équipes
                $syncData = [];
                $syncData[$validated['equipe_id']] = [
                    'role' => 'principale',
                    'date_affectation' => now(),
                ];

                if (!empty($validated['equipes_support'])) {
                    foreach ($validated['equipes_support'] as $eqId) {
                        if ($eqId != $validated['equipe_id']) {
                            $syncData[$eqId] = [
                                'role' => 'support',
                                'date_affectation' => now(),
                            ];
                        }
                    }
                }

                $maintenance->equipes()->sync($syncData);
            });

            return redirect()->route('planning.index')->with('success', 'Maintenance mise à jour avec succès.');
        } catch (\Exception $e) {
            \Log::error('[MAINTENANCE UPDATE ERROR] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour de la maintenance : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une maintenance
     */
    public function destroy(Maintenance $maintenance)
    {
        $this->authorizeMaintenanceManage($maintenance);

        try {
            DB::transaction(function() use ($maintenance) {
                // 1. Détacher les équipes
                $maintenance->equipes()->detach();

                // 2. Supprimer les comptes rendus journaliers associés
                if (method_exists($maintenance, 'comptesRendusJournaliers')) {
                    $maintenance->comptesRendusJournaliers()->delete();
                } else {
                    \App\Models\CompteRenduJournalier::where('maintenance_id', $maintenance->id)->delete();
                }

                // 3. Supprimer les notifications liées
                InterventionNotification::where('maintenance_id', $maintenance->id)->delete();

                // 4. Supprimer la maintenance
                $maintenance->delete();
            });

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Maintenance supprimée avec succès.']);
            }

            return redirect()->route('planning.index')->with('success', 'Maintenance supprimée avec succès.');
        } catch (\Exception $e) {
            \Log::error('[MAINTENANCE DELETE ERROR] ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Erreur lors de la suppression de la maintenance : ' . $e->getMessage());
        }
    }

    /**
     * Confirmer la réception par le client
     */
    public function confirmerReception(Maintenance $maintenance)
    {
        $user = Auth::user();
        
        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Seuls les superviseurs client peuvent confirmer la réception.');
        }

        // Vérifier le périmètre
        $hasAccess = false;
        if ($user->base_id && $maintenance->base_id == $user->base_id) {
            $hasAccess = true;
        } elseif ($user->client_id && $maintenance->client_id == $user->client_id) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return back()->with('error', 'Vous ne pouvez confirmer que les maintenances de votre périmètre.');
        }

        $maintenance->update([
            'statut' => 'confirmée_client',
            'date_confirmation_client' => now(),
            'confirme_par_user_id' => $user->id,
        ]);

        // Notifier le créateur
        InterventionNotification::create([
            'user_id' => $maintenance->created_by_user_id,
            'maintenance_id' => $maintenance->id,
            'type' => 'maintenance_confirmee_client',
            'message' => "Le client a confirmé la réception de la maintenance #{$maintenance->numero_maintenance}.",
            'statut' => 'non_lu',
        ]);

        return back()->with('success', 'Réception de la maintenance confirmée.');
    }

    /**
     * Afficher le détail d'une maintenance
     */
    public function show(Maintenance $maintenance)
    {
        $user = Auth::user();
        
        // Vérifier les permissions
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            // Vérifier le périmètre pour les autres rôles
            $hasAccess = false;
            
            if ($user->isSuperviseurClient()) {
                if ($user->base_id && $maintenance->base_id == $user->base_id) {
                    $hasAccess = true;
                } elseif ($user->client_id && $maintenance->client_id == $user->client_id) {
                    $hasAccess = true;
                }
            } elseif ($user->isDemandeur()) {
                // Demandeur : accès si la maintenance concerne ses sites assignés ou son client
                $siteIds = $user->sitesAssignes->pluck('id')->toArray();
                if ($user->site_id) {
                    $siteIds[] = $user->site_id;
                }
                $siteIds = array_unique(array_filter($siteIds));

                if ($maintenance->site_id && in_array($maintenance->site_id, $siteIds)) {
                    $hasAccess = true;
                } elseif ($user->client_id && $maintenance->client_id == $user->client_id) {
                    $hasAccess = true;
                }
            } elseif ($user->isTechnicien()) {
                $equipesIds = $user->equipes->pluck('id')->toArray();
                if ($user->equipe_id) {
                    $equipesIds[] = $user->equipe_id;
                }
                $equipesIds = array_unique(array_filter($equipesIds));

                if ($maintenance->technicien_id == $user->id) {
                    $hasAccess = true;
                } elseif ($maintenance->equipe_id && in_array($maintenance->equipe_id, $equipesIds)) {
                    $hasAccess = true;
                } elseif ($maintenance->equipes()->whereIn('equipes.id', $equipesIds)->exists()) {
                    $hasAccess = true;
                }
            }
            
            if (!$hasAccess) {
                abort(403, 'Vous n\'avez pas accès à cette maintenance.');
            }
        }
        
        $maintenance->load(['client', 'base', 'site', 'equipe', 'equipes.membres', 'equipes.chef', 'technicien', 'createdBy', 'confirmePar', 'comptesRendusJournaliers.equipe', 'comptesRendusJournaliers.responsable']);
        
        $allEquipes = Equipe::with('membres', 'chef')->orderBy('nom_equipe')->get();

        return view('maintenances.show', compact('maintenance', 'allEquipes'));
    }

    /**
     * Ajouter une équipe à une maintenance
     */
    public function addEquipe(Request $request, Maintenance $maintenance)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'equipe_id' => 'required|exists:equipes,id',
        ]);

        if (!$maintenance->equipes()->where('equipes.id', $validated['equipe_id'])->exists()) {
            $maintenance->equipes()->attach($validated['equipe_id'], [
                'role' => 'support',
                'date_affectation' => now(),
            ]);

            if (empty($maintenance->equipe_id)) {
                $maintenance->update(['equipe_id' => $validated['equipe_id']]);
            }
        }

        return back()->with('success', 'Équipe ajoutée à la maintenance avec succès.');
    }

    /**
     * Retirer une équipe d'une maintenance
     */
    public function removeEquipe(Maintenance $maintenance, $equipeId)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403, 'Action non autorisée.');
        }

        if ($maintenance->equipes()->count() <= 1) {
            return back()->with('error', 'Une maintenance doit conserver au moins une équipe.');
        }

        $maintenance->equipes()->detach($equipeId);

        if ($maintenance->equipe_id == $equipeId) {
            $premierRestant = $maintenance->equipes()->first();
            $maintenance->update(['equipe_id' => $premierRestant ? $premierRestant->id : null]);
        }

        return back()->with('success', 'Équipe retirée de la maintenance.');
    }

    /**
     * Démarrer une maintenance (technicien)
     */
    public function demarrer(Maintenance $maintenance)
    {
        $user = Auth::user();
        
        // Seul le chef d'équipe peut démarrer une maintenance
        if (!$user->isChefTechnicien()) {
            return back()->with('error', 'Seul un chef d\'équipe peut démarrer une maintenance.');
        }

        // Vérifier que l'utilisateur est le chef d'une des équipes affectées
        $isChef = ($maintenance->equipe && $maintenance->equipe->chef_equipe == $user->id)
               || ($maintenance->equipes()->where('chef_equipe', $user->id)->exists());

        if (!$isChef) {
            return back()->with('error', 'Vous devez être le chef d\'une des équipes affectées à cette maintenance pour la démarrer.');
        }

        // Permettre le démarrage si planifiée ou confirmée
        if (!in_array($maintenance->statut, ['planifiée', 'confirmée_client'])) {
            return back()->with('error', 'La maintenance doit être planifiée ou confirmée pour être démarrée.');
        }

        // Vérifier que la date de début est atteinte
        $dateDebutPrevue = \Carbon\Carbon::parse($maintenance->date_debut_prevue);
        $aujourdhui = \Carbon\Carbon::now();
        
        if ($aujourdhui->lessThan($dateDebutPrevue)) {
            return back()->with('error', 'Cette maintenance ne peut être démarrée qu\'à partir du ' . $dateDebutPrevue->format('d/m/Y à H:i') . '.');
        }

        $maintenance->update([
            'statut' => 'en_cours',
            'date_debut_reelle' => now(),
        ]);

        // Notifier les superviseurs
        InterventionNotification::create([
            'user_id' => $maintenance->created_by_user_id,
            'maintenance_id' => $maintenance->id,
            'type' => 'maintenance_demarree',
            'message' => "La maintenance #{$maintenance->numero_maintenance} a été démarrée par {$user->nom_complet}.",
            'statut' => 'non_lu',
        ]);

        return back()->with('success', 'Maintenance démarrée avec succès.');
    }

    /**
     * Formulaire de rapport de fin
     */
    public function rapportForm(Maintenance $maintenance)
    {
        $user = Auth::user();
        
        // Seul le chef d'équipe peut soumettre le rapport final
        if (!$user->isChefTechnicien()) {
            abort(403, 'Seul le chef d\'équipe peut soumettre un rapport de maintenance.');
        }

        // Vérifier que l'utilisateur est le chef d'une équipe affectée
        $isChef = ($maintenance->equipe && $maintenance->equipe->chef_equipe == $user->id)
               || ($maintenance->equipes()->where('chef_equipe', $user->id)->exists());

        if (!$isChef) {
            abort(403, 'Vous devez être le chef de l\'équipe affectée à cette maintenance pour soumettre le rapport.');
        }

        if ($maintenance->statut !== 'en_cours') {
            return redirect()->route('planning.index')->with('error', 'La maintenance doit être en cours pour soumettre un rapport.');
        }

        // Vérifier que tous les équipements prévus ont été traités
        if ($maintenance->nombre_equipements_prevus > 0 && $maintenance->nombre_equipements_restants > 0) {
            return redirect()->route('maintenances.show', $maintenance->id)
                ->with('error', "Impossible de soumettre le rapport final : il reste encore {$maintenance->nombre_equipements_restants} équipement(s) à traiter sur les {$maintenance->nombre_equipements_prevus} prévus.");
        }

        $maintenance->load(['client', 'site']);
        
        return view('maintenances.rapport', compact('maintenance'));
    }

    /**
     * Terminer la maintenance avec rapport
     */
    public function terminer(Request $request, Maintenance $maintenance)
    {
        $user = Auth::user();
        
        if (!$user->isTechnicien()) {
            return back()->with('error', 'Seuls les techniciens peuvent terminer une maintenance.');
        }

        // Seul le chef d'équipe peut terminer une maintenance
        $isChef = ($maintenance->equipe && $maintenance->equipe->chef_equipe == $user->id)
               || ($maintenance->equipes()->where('chef_equipe', $user->id)->exists());

        if (!$isChef) {
            return back()->with('error', 'Seul le chef d\'équipe peut terminer la maintenance.');
        }

        // Vérifier que tous les équipements prévus ont été traités
        if ($maintenance->nombre_equipements_prevus > 0 && $maintenance->nombre_equipements_restants > 0) {
            return back()->with('error', "Impossible de terminer la maintenance : il reste encore {$maintenance->nombre_equipements_restants} équipement(s) à traiter sur les {$maintenance->nombre_equipements_prevus} prévus.");
        }

        $validated = $request->validate([
            'rapport_technicien' => 'required|string',
            'pieces_utilisees' => 'nullable|string',
        ]);

        $maintenance->update([
            'statut' => 'terminée',
            'date_fin_reelle' => now(),
            'rapport_technicien' => $validated['rapport_technicien'],
            'pieces_utilisees' => $validated['pieces_utilisees'] ?? null,
            'statut_rapport_technicien' => 'soumis', // Rapport soumis, en attente validation Soutarah
        ]);

        // Notifier le superviseur Soutarah qui a créé la maintenance
        InterventionNotification::create([
            'user_id' => $maintenance->created_by_user_id,
            'maintenance_id' => $maintenance->id,
            'type' => 'maintenance_rapport_soumis',
            'message' => "La maintenance #{$maintenance->numero_maintenance} est terminée. Rapport soumis par {$user->nom_complet} - En attente de validation.",
            'statut' => 'non_lu',
        ]);

        return redirect()->route('planning.index')->with('success', 'Maintenance terminée avec succès. Rapport soumis pour validation.');
    }

    /**
     * Transmettre le rapport au client (Superviseur Soutarah)
     */
    public function transmettreRapportClient(Maintenance $maintenance)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurSoutarah() && !$user->isAdmin()) {
            return back()->with('error', 'Accès non autorisé.');
        }

        if ($maintenance->statut_rapport_technicien !== 'soumis') {
            return back()->with('error', 'Le rapport n\'est pas prêt à être transmis.');
        }

        $maintenance->update([
            'statut_rapport_technicien' => 'transmis_client',
            'date_transmission_rapport_client' => now(),
            'rapport_transmis_par_user_id' => $user->id,
        ]);

        // Notifier le superviseur client
        if ($maintenance->client && $maintenance->client->superviseur) {
            InterventionNotification::create([
                'user_id' => $maintenance->client->superviseur->id,
                'maintenance_id' => $maintenance->id,
                'type' => 'maintenance_rapport_client',
                'message' => "Rapport de maintenance #{$maintenance->numero_maintenance} transmis pour validation.",
                'statut' => 'non_lu',
            ]);
        }

        return back()->with('success', 'Rapport validé et transmis au superviseur client.');
    }

    /**
     * Rejeter le rapport du technicien (Superviseur Soutarah)
     */
    public function rejeterRapportTechnicien(Request $request, Maintenance $maintenance)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurSoutarah() && !$user->isAdmin()) {
            return back()->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'raison_rejet' => 'required|string|min:5',
        ]);

        $maintenance->update([
            'statut_rapport_technicien' => 'rejete',
            'raison_rejet_rapport' => $validated['raison_rejet'],
            'date_rejet_rapport' => now(),
        ]);

        // Notifier le technicien
        if ($maintenance->technicien_id) {
            InterventionNotification::create([
                'user_id' => $maintenance->technicien_id,
                'maintenance_id' => $maintenance->id,
                'type' => 'maintenance_rapport_rejete',
                'message' => "Votre rapport pour la maintenance #{$maintenance->numero_maintenance} a été rejeté. Corrections requises.",
                'statut' => 'non_lu',
            ]);
        }

        return back()->with('success', 'Rapport renvoyé au technicien pour correction.');
    }

    /**
     * Valider et clôturer la maintenance (Superviseur Client)
     */
    public function validerParClient(Request $request, Maintenance $maintenance)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Seul le superviseur client peut valider la maintenance.');
        }

        if ($maintenance->statut_rapport_technicien !== 'transmis_client') {
            return back()->with('error', 'Le rapport n\'a pas encore été transmis.');
        }

        $validated = $request->validate([
            'commentaire_validation' => 'nullable|string',
        ]);

        $maintenance->update([
            'statut_validation_client' => 'validé',
            'commentaire_validation_client' => $validated['commentaire_validation'] ?? null,
            'date_validation_client' => now(),
            'validated_by_client_user_id' => $user->id,
        ]);

        // Notifier le créateur (Soutarah)
        InterventionNotification::create([
            'user_id' => $maintenance->created_by_user_id,
            'maintenance_id' => $maintenance->id,
            'type' => 'maintenance_validee_client',
            'message' => "Maintenance #{$maintenance->numero_maintenance} validée et clôturée par le client.",
            'statut' => 'non_lu',
        ]);

        return back()->with('success', 'Maintenance validée et clôturée avec succès.');
    }

    /**
     * Rejeter le rapport (Superviseur Client)
     */
    public function rejeterParClient(Request $request, Maintenance $maintenance)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'raison_rejet' => 'required|string|min:5',
        ]);

        $maintenance->update([
            'statut_validation_client' => 'non_conforme',
            'commentaire_validation_client' => $validated['raison_rejet'],
            'date_validation_client' => now(),
            'validated_by_client_user_id' => $user->id,
            'statut_rapport_technicien' => 'rejete', // Retour au technicien
            'raison_rejet_rapport' => $validated['raison_rejet'],
        ]);

        // Notifier Soutarah
        InterventionNotification::create([
            'user_id' => $maintenance->created_by_user_id,
            'maintenance_id' => $maintenance->id,
            'type' => 'maintenance_rejetee_client',
            'message' => "Rapport maintenance #{$maintenance->numero_maintenance} refusé par le client.",
            'statut' => 'non_lu',
        ]);

        return back()->with('success', 'Rapport refusé et renvoyé pour correction.');
    }
}
