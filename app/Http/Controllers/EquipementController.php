<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Client;
use App\Models\Equipement;
use App\Models\FicheFroid;
use App\Models\Depannage;
use App\Models\Assignment;
use Illuminate\Http\Request;

class EquipementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Equipement::with(['site.baseSite.client', 'zone']); // Charger les relations pour afficher la base, le client et l'emplacement

        // Récupérer l'assignment pour superviseur_soutarah UNE SEULE FOIS
        $assignment = null;
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
        }

        // ═══════════════════════════════════════════════════════════════
        // FILTRAGE PRINCIPAL PAR RÔLE - DOIT ÊTRE APPLIQUÉ EN PREMIER
        // ═══════════════════════════════════════════════════════════════
        
        // Superviseur Client : UNIQUEMENT les équipements de SA base OU de son client direct
        if ($user->type_utilisateur === 'superviseur_client') {
            if ($user->base_id) {
                // Filtrer par base_id via les sites
                $query->whereHas('site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                // Filtrer par client_id direct
                $query->where(function($q) use ($user) {
                    $q->where('client_id', $user->client_id)
                      ->orWhereHas('site', function($s) use ($user) {
                          $s->where('client_id', $user->client_id);
                      });
                });
            } else {
                // Aucune assignation = aucun équipement
                $query->whereRaw('1 = 0');
            }
        }
        
        // Superviseur Soutarah : UNIQUEMENT les équipements de SA base/client assigné(e)
        elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('site.baseSite', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0'); // Pas d'assignment = rien voir
            }
        }

        // Filtres hiérarchiques ADDITIONNELS (Client seulement pour admin/superviseur)
        if ($user->type_utilisateur === 'admin' || $user->type_utilisateur === 'superviseur_soutarah') {
            // Filtre par client
            if ($clientId = $request->input('client_id')) {
                $query->where(function($q) use ($clientId) {
                    $q->where('client_id', $clientId)
                      ->orWhereHas('site', function($s) use ($clientId) {
                          $s->where('client_id', $clientId)
                            ->orWhereHas('baseSite', function($b) use ($clientId) {
                                $b->where('client_id', $clientId);
                            });
                      });
                });
            }
        }

        // Recherche par mot-clé (code, nom, marque)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('equipement_code', 'like', "%{$search}%")
                  ->orWhere('equipement_nom', 'like', "%{$search}%")
                  ->orWhere('marque', 'like', "%{$search}%");
            });
        }

        // Filtre par type (recherche tolérante)
        if ($type = $request->input('type')) {
            $query->where('type', 'like', "%{$type}%");
        }

        // Filtre par emplacement
        if ($emplacement = $request->input('emplacement')) {
            $query->where('emplacement', $emplacement);
        }

        // Filtre par état
        if ($etat = $request->input('etat')) {
            $query->where('etat', $etat);
        }


        $equipements = $query->orderBy('id', 'desc')->paginate(15);
        
        // DEBUG: Log pour comprendre le problème de filtrage
        \Log::info('=== EQUIPEMENTS INDEX DEBUG ===');
        \Log::info('User: ' . $user->email);
        \Log::info('Type: ' . $user->type_utilisateur);
        \Log::info('Base ID: ' . ($user->base_id ?? 'NULL - ⚠️ PROBLÈME SI SUPERVISEUR!'));
        \Log::info('Client ID: ' . ($user->client_id ?? 'NULL'));
        \Log::info('Total filtré: ' . $equipements->total());
        \Log::info('SQL Query: ' . $query->toSql());
        \Log::info('===========================');
        
        // Total selon le rôle
        if ($user->type_utilisateur === 'superviseur_client') {
            if ($user->base_id) {
                $totalCount = Equipement::whereHas('site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                })->count();
            } elseif ($user->client_id) {
                $totalCount = Equipement::where('client_id', $user->client_id)->count();
            } else {
                $totalCount = 0;
            }
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            $baseQuery = Equipement::query();
            if ($assignment) {
                if ($assignment->base_id) {
                    $baseQuery->whereHas('site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $baseQuery->whereHas('site.baseSite', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
            $totalCount = $baseQuery->count();
        } else {
            $totalCount = Equipement::count();
        }

        // Liste des clients, bases et sites pour les filtres (Admin et Superviseur Soutarah)
        $allClients = collect();
        $allBases = collect();
        $allSites = collect();
        
        if ($user->type_utilisateur === 'admin' || $user->type_utilisateur === 'superviseur_soutarah') {
            $allClients = \App\Models\Client::orderBy('nom')->get();
            $allBases = BaseSite::orderBy('nom_base')->get();
            $allSites = \App\Models\Site::orderBy('nom_site')->get();
        }

        // Liste dynamique de TOUS les types d'équipements réels dans la base de données
        $dbTypes = Equipement::distinct()->whereNotNull('type')->where('type', '!=', '')->pluck('type')->toArray();
        $standardTypes = ['Climatiseur Split', 'Groupe Froid', 'CVC', 'Chambre Froide', 'Armoire Réfrigérée', 'Autre'];
        $allTypes = array_unique(array_merge($standardTypes, $dbTypes));
        sort($allTypes);

        // ═══════════════════════════════════════════════════════════════
        // STATISTIQUES POUR LES CARTES
        // ═══════════════════════════════════════════════════════════════
        
        // Créer la requête de base avec les mêmes filtres que la liste
        $statsQuery = Equipement::query();
        
        // Appliquer les mêmes filtres selon le rôle
        if ($user->type_utilisateur === 'superviseur_client') {
            if ($user->base_id) {
                $statsQuery->whereHas('site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $statsQuery->where('client_id', $user->client_id);
            }
        }
        
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            if ($assignment) {
                if ($assignment->base_id) {
                    $statsQuery->whereHas('site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $statsQuery->whereHas('site.baseSite', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                // PAS D'ASSIGNMENT = AUCUNE STAT
                $statsQuery->whereRaw('1 = 0');
            }
        }
        
        
        // Stats par Client - VERSION SIMPLIFIÉE
        $equipementsWithClient = (clone $statsQuery)->with('client', 'site.baseSite.client')->get();
        
        $clientCounts = [];
        $baseCounts = [];
        
        foreach ($equipementsWithClient as $eq) {
            $clientNom = null;
            $baseNom = null;
            
            // Cas 1 : Équipement a un client direct
            if ($eq->client_id && $eq->client) {
                $clientNom = $eq->client->nom;
            }
            // Cas 2 : Équipement via site → base → client
            elseif ($eq->site && $eq->site->baseSite && $eq->site->baseSite->client) {
                $clientNom = $eq->site->baseSite->client->nom;
                $baseNom = $eq->site->baseSite->nom_base;
            }
            // Cas 3 : Équipement via site direct → client
            elseif ($eq->site && $eq->site->client) {
                $clientNom = $eq->site->client->nom;
            }
            
            if ($clientNom) {
                if (!isset($clientCounts[$clientNom])) {
                    $clientCounts[$clientNom] = 0;
                }
                $clientCounts[$clientNom]++;
            }
            
            // Compter par base aussi
            if ($baseNom) {
                if (!isset($baseCounts[$baseNom])) {
                    $baseCounts[$baseNom] = 0;
                }
                $baseCounts[$baseNom]++;
            }
        }
        
        arsort($clientCounts);
        arsort($baseCounts);
        
        $statsByClient = collect(array_slice($clientCounts, 0, 5, true))
            ->map(function($count, $nom) {
                return (object)['nom' => $nom, 'count' => $count];
            })->values();
            
        $statsByBase = collect(array_slice($baseCounts, 0, 5, true))
            ->map(function($count, $nom) {
                return (object)['nom' => $nom, 'count' => $count];
            })->values();
        
        // Stats par Type
        $statsByType = (clone $statsQuery)
            ->selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        
        // Stats par Emplacement
        $statsByEmplacement = (clone $statsQuery)
            ->selectRaw('emplacement, COUNT(*) as count')
            ->whereNotNull('emplacement')
            ->where('emplacement', '!=', '')
            ->groupBy('emplacement')
            ->orderByDesc('count')
            ->get();

        return view('equipements.index', compact(
            'equipements', 
            'totalCount', 
            'allClients', 
            'allBases', 
            'allSites', 
            'allTypes',
            'statsByClient',
            'statsByBase',
            'statsByType',
            'statsByEmplacement'
        ));
    }

    public function show($id)
    {
        $user = auth()->user();
        $equipement = Equipement::findOrFail($id);
        
        // Superviseur Client ne peut voir que les équipements de SA base ou de son client
        if ($user->type_utilisateur === 'superviseur_client') {
            $hasAccess = false;
            
            if ($user->base_id && $equipement->site && $equipement->site->base_id == $user->base_id) {
                $hasAccess = true;
            } elseif ($user->client_id && $equipement->client_id == $user->client_id) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                return redirect()->route('equipements.index')->with('error', 'Cet équipement n\'appartient pas à votre périmètre.');
            }
        }
        
        // Superviseur Soutarah ne peut voir que les équipements de SA base/client
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $equipement->site && $equipement->site->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $equipement->site && $equipement->site->baseSite && $equipement->site->baseSite->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('equipements.index')->with('error', 'Cet équipement n\'appartient pas à votre périmètre.');
            }
        }
        
        // Fiches froid associées
        $fichesFroid = FicheFroid::where('equipement_id', $id)
            ->with('technicien')
            ->orderBy('date_saisie', 'desc')
            ->get();

        // Dépannages associés
        $depannages = Depannage::where('equipement_id', $id)
            ->orderBy('date_demande', 'desc')
            ->get();

        return view('equipements.show', compact('equipement', 'fichesFroid', 'depannages'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Superviseur Client : formulaire simplifié
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            return view('equipements.create_simple');
        }
        
        // Si superviseur client, on récupère uniquement son client et sa base
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            $baseSite = BaseSite::find($user->base_id);
            $clients = $baseSite ? Client::where('id', $baseSite->client_id)->get() : collect();
            $bases = BaseSite::where('id', $user->base_id)->get();
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            // Superviseur Soutarah : uniquement sa base/client assigné(e)
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment && $assignment->base_id) {
                $baseSite = BaseSite::find($assignment->base_id);
                $clients = $baseSite ? Client::where('id', $baseSite->client_id)->get() : collect();
                $bases = BaseSite::where('id', $assignment->base_id)->get();
            } elseif ($assignment && $assignment->client_id) {
                $clients = Client::where('id', $assignment->client_id)->get();
                $bases = BaseSite::where('client_id', $assignment->client_id)->get();
            } else {
                abort(403, 'Vous n\'avez pas d\'assignment.');
            }
        } else {
            // Admin voit tous les clients
            $clients = Client::orderBy('nom')->get();
            $bases = collect(); // Vide au départ, sera rempli par JS
        }
        
        return view('equipements.create', compact('clients', 'bases'));
    }

    /**
     * Créer une demande d'installation simplifiée (SANS créer l'équipement)
     */
    public function storeSimple(Request $request)
    {
        $user = auth()->user();
        
        // Seulement pour Superviseur Client
        if ($user->type_utilisateur !== 'superviseur_client') {
            return redirect()->route('equipements.index')->with('error', 'Accès refusé.');
        }
        
        $validated = $request->validate([
            'client_id' => 'required|integer',
            'base_id' => 'required|integer',
            'site_id' => 'required|integer',
            'equipement_nom' => 'required|string|max:150',
            'type' => 'required|string|max:100',
            'emplacement' => 'required|in:interne,externe',
            'description' => 'nullable|string',
            'date_installation_souhaitee' => 'required|date',
        ]);
        
        // Créer la demande SANS équipement
        $demande = \App\Models\Demande::create([
            'created_by_user_id' => $user->id,
            'created_by_role' => 'superviseur_client',
            'client_id' => $validated['client_id'],
            'base_id' => $validated['base_id'],
            'site_id' => $validated['site_id'],
            'equipement_id' => null, // PAS ENCORE CRÉÉ
            'type_intervention' => 'Installation',
            'description' => $validated['description'] ?? "Demande d'installation : {$validated['equipement_nom']}",
            'niveau_urgence' => 'moyen',
            'date_debut_souhaitee' => $validated['date_installation_souhaitee'],
            'date_validation_client' => now(),
            'validated_by_client_user_id' => $user->id,
            'statut' => 'validated_by_client',
            // Champs temporaires pour complétion ultérieure
            'temp_equipement_nom' => $validated['equipement_nom'],
            'temp_equipement_type' => $validated['type'],
            'temp_equipement_emplacement' => $validated['emplacement'],
            'equipement_needs_completion' => true,
        ]);
        
        // Notifier Admin et Superviseurs Soutarah
        $admins = \App\Models\User::where('type_utilisateur', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\InterventionNotification::create([
                'user_id' => $admin->id,
                'demande_id' => $demande->id,
                'type' => 'nouvelle_demande_installation',
                'message' => "Nouvelle demande d'installation #{$demande->numero_demande} - Équipement à compléter : {$validated['equipement_nom']}",
                'statut' => 'non_lu',
            ]);
        }
        
        // Notifier superviseurs Soutarah assignés
        $soutarahAssignments = \App\Models\Assignment::where(function($q) use ($validated) {
            if (!empty($validated['base_id'])) {
                $q->where('base_id', $validated['base_id']);
            }
            if (!empty($validated['client_id'])) {
                $q->orWhere('client_id', $validated['client_id']);
            }
        })->pluck('superviseur_soutarah_id')->toArray();
        
        $soutarahUsers = \App\Models\User::whereIn('id', $soutarahAssignments)->get();
        foreach ($soutarahUsers as $soutarah) {
            \App\Models\InterventionNotification::create([
                'user_id' => $soutarah->id,
                'demande_id' => $demande->id,
                'type' => 'nouvelle_demande_installation',
                'message' => "Nouvelle demande d'installation #{$demande->numero_demande} - Veuillez compléter les informations : {$validated['equipement_nom']}",
                'statut' => 'non_lu',
            ]);
        }
        
        return redirect()->route('demandes.index')
            ->with('success', 'Votre demande d\'installation a été soumise avec succès ! L\'équipe Soutarah complétera les informations techniques et planifiera l\'installation.');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Extraire automatiquement mois et année à partir de date_installation_souhaitee
        if ($request->has('date_installation_souhaitee') && !empty($request->date_installation_souhaitee)) {
            $time = strtotime($request->date_installation_souhaitee);
            if ($time !== false) {
                $request->merge([
                    'mois_installation'  => (int) date('m', $time),
                    'annee_installation' => (int) date('Y', $time),
                ]);
            }
        }
        
        $validated = $request->validate([
            'equipement_code' => 'required|string|max:50',
            'equipement_numero' => 'nullable|string|max:3',
            'equipement_nom' => 'required|string|max:150',
            'marque' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'emplacement' => 'nullable|in:interne,externe',
            'puissance' => 'nullable|string|max:50',
            'refrigerant' => 'nullable|string|max:50',
            'mois_installation' => 'nullable|numeric|between:1,12',
            'annee_installation' => 'nullable|numeric|between:1900,2100',
            'type_unite' => 'nullable|in:Exterieure,Interieure,Inconnue',
            'num_sur_site' => 'nullable|string|max:10',
            'client_id' => 'required|integer',
            'base_id' => 'nullable|integer',
            'site_id' => 'nullable|integer',
            'zone_id' => 'nullable|integer',
            'date_acquisition' => 'nullable|date',
            'etat' => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ]);
        
        // Vérifier l'unicité du num_sur_site pour le site donné
        if (!empty($validated['num_sur_site']) && !empty($validated['site_id'])) {
            $existeDeja = \App\Models\Equipement::where('site_id', $validated['site_id'])
                ->where('num_sur_site', $validated['num_sur_site'])
                ->exists();
            
            if ($existeDeja) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Le numéro {$validated['num_sur_site']} est déjà utilisé sur ce site. Chaque équipement doit avoir un numéro unique.");
            }
        }

        // Vérifier les permissions selon le rôle
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            // Superviseur Client ne peut créer que dans SA base (si une base est spécifiée)
            if (isset($validated['base_id']) && $validated['base_id'] && $validated['base_id'] != $user->base_id) {
                return redirect()->route('equipements.index')->with('error', 'Vous ne pouvez créer des équipements que dans votre base.');
            }
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            // Superviseur Soutarah ne peut créer que dans SA base/client assigné(e)
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                // Si une base est assignée et spécifiée
                if ($assignment->base_id && isset($validated['base_id']) && $validated['base_id']) {
                    if ($validated['base_id'] == $assignment->base_id) {
                        $hasAccess = true;
                    }
                } 
                // Si un client est assigné
                elseif ($assignment->client_id && $validated['client_id'] == $assignment->client_id) {
                    $hasAccess = true;
                }
                // Si pas de base/site spécifié, on accepte si le client correspond
                elseif ($assignment->client_id && $validated['client_id'] == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess && $assignment) {
                return redirect()->route('equipements.index')->with('error', 'Vous ne pouvez créer des équipements que dans votre périmètre assigné.');
            }
        }
        
        // Nettoyer les valeurs vides pour base_id et site_id
        if (empty($validated['base_id'])) {
            $validated['base_id'] = null;
        }
        if (empty($validated['site_id'])) {
            $validated['site_id'] = null;
        }
        if (empty($validated['equipement_numero'])) {
            $validated['equipement_numero'] = null;
        }
        if (empty($validated['num_sur_site'])) {
            $validated['num_sur_site'] = null;
        }

        // Si la création est faite par un client/superviseur client, l'équipement est mis en attente d'installation
        if ($user->isSuperviseurClient() || $user->isDemandeur()) {
            $validated['etat'] = 'en attente installation';
        }

        try {
            $equipement = Equipement::create($validated);

            // Si la création est initiée par le client, créer automatiquement la Demande d'Installation
            if ($user->isSuperviseurClient() || $user->isDemandeur()) {
                $dateInstallation = $request->input('date_installation_souhaitee', date('Y-m-d'));

                $demande = \App\Models\Demande::create([
                    'created_by_user_id'          => $user->id,
                    'created_by_role'             => $user->isDemandeur() ? 'demandeur' : 'superviseur_client',
                    'client_id'                   => $validated['client_id'],
                    'base_id'                     => $validated['base_id'],
                    'site_id'                     => $validated['site_id'],
                    'equipement_id'               => $equipement->id,
                    'type_intervention'           => 'Installation',
                    'description'                 => "Demande d'installation du nouvel équipement : " . $equipement->equipement_nom . " (" . $equipement->equipement_code . ")",
                    'niveau_urgence'              => 'moyen',
                    'date_debut_souhaitee'        => $dateInstallation,
                    'date_validation_client'      => now(),
                    'validated_by_client_user_id' => $user->id,
                    'statut'                      => 'validated_by_client',
                ]);

                // Notifier l'admin et les superviseurs Soutarah du périmètre
                $admins = \App\Models\User::where('type_utilisateur', 'admin')->get();
                foreach ($admins as $admin) {
                    \App\Models\InterventionNotification::create([
                        'user_id' => $admin->id,
                        'demande_id' => $demande->id,
                        'type' => 'nouvelle_demande_installation',
                        'message' => "Nouvelle demande d'installation #{$demande->numero_demande} pour l'équipement {$equipement->equipement_code}",
                        'statut' => 'non_lu',
                    ]);
                }

                // Notifier les superviseurs Soutarah assignés
                $soutarahAssignments = \App\Models\Assignment::where(function($q) use ($validated) {
                    if (!empty($validated['base_id'])) {
                        $q->where('base_id', $validated['base_id']);
                    }
                    if (!empty($validated['client_id'])) {
                        $q->orWhere('client_id', $validated['client_id']);
                    }
                })->pluck('superviseur_soutarah_id')->toArray();

                $soutarahUsers = \App\Models\User::whereIn('id', $soutarahAssignments)->get();
                foreach ($soutarahUsers as $soutarah) {
                    \App\Models\InterventionNotification::create([
                        'user_id' => $soutarah->id,
                        'demande_id' => $demande->id,
                        'type' => 'nouvelle_demande_installation',
                        'message' => "Nouvelle demande d'installation #{$demande->numero_demande} pour l'équipement {$equipement->equipement_code}",
                        'statut' => 'non_lu',
                    ]);
                }

                return redirect()->route('demandes.index')
                    ->with('success', 'Votre demande d\'installation a été soumise avec succès. Elle a été transmise à Soutarah pour validation.');
            }

            return redirect()->route('equipements.show', $equipement->id)
                ->with('success', 'Équipement créé avec succès !');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[EQUIPEMENT STORE ERROR] ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => $user->id,
                'inputs'    => $request->all(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'équipement : ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = auth()->user();
        if (in_array($user->type_utilisateur, ['superviseur_client', 'demandeur'])) {
            return redirect()->route('equipements.index')->with('error', 'Seuls les administrateurs et le personnel Soutarah peuvent modifier les équipements.');
        }

        $equipement = Equipement::findOrFail($id);
        
        // Superviseur Client ne peut éditer que les équipements de SA base
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            if (!$equipement->site || $equipement->site->base_id != $user->base_id) {
                return redirect()->route('equipements.index')->with('error', 'Cet équipement n\'appartient pas à votre base.');
            }
        }
        
        // Superviseur Soutarah ne peut éditer que les équipements de SA base/client
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $equipement->site && $equipement->site->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $equipement->site && $equipement->site->baseSite && $equipement->site->baseSite->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('equipements.index')->with('error', 'Cet équipement n\'appartient pas à votre périmètre.');
            }
        }
        
        // Si superviseur client
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            $baseSite = BaseSite::find($user->base_id);
            $clients = $baseSite ? Client::where('id', $baseSite->client_id)->get() : collect();
            $bases = BaseSite::where('id', $user->base_id)->get();
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            // Superviseur Soutarah
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment && $assignment->base_id) {
                $baseSite = BaseSite::find($assignment->base_id);
                $clients = $baseSite ? Client::where('id', $baseSite->client_id)->get() : collect();
                $bases = BaseSite::where('id', $assignment->base_id)->get();
            } elseif ($assignment && $assignment->client_id) {
                $clients = Client::where('id', $assignment->client_id)->get();
                $bases = BaseSite::where('client_id', $assignment->client_id)->get();
            }
        } else {
            $clients = Client::orderBy('nom')->get();
            $bases = BaseSite::where('client_id', $equipement->client_id)->get();
        }
        
        return view('equipements.edit', compact('equipement', 'clients', 'bases'));
    }

    // API pour récupérer les bases d'un client (filtrée par rôle)
    public function getBasesByClient($clientId)
    {
        $user = auth()->user();
        $query = BaseSite::where('client_id', $clientId);

        if ($user && $user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment && $assignment->base_id) {
                $query->where('id', $assignment->base_id);
            }
        } elseif ($user && $user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            $query->where('id', $user->base_id);
        }

        $bases = $query->orderBy('nom_base')->get();
        return response()->json($bases);
    }

    // API pour récupérer les sites directs (sans base) d'un client
    public function getSitesDirectsByClient($clientId)
    {
        $sites = \App\Models\Site::where('client_id', $clientId)->whereNull('base_id')->orderBy('nom_site')->get();
        return response()->json($sites);
    }

    // API pour récupérer TOUS les équipements d'un client (directs ou via sites/bases) avec filtrage strict par rôle
    public function getEquipementsByClient($clientId)
    {
        $user = auth()->user();
        $query = Equipement::query();

        // 1. Filtrer par client
        $query->where(function($q) use ($clientId) {
            $q->where('client_id', $clientId)
              ->orWhereHas('site', function($s) use ($clientId) {
                  $s->where('client_id', $clientId)
                    ->orWhereHas('baseSite', function($b) use ($clientId) {
                        $b->where('client_id', $clientId);
                    });
              });
        });

        // 2. Filtrer selon le rôle
        if ($user && $user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->where(function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id)
                          ->orWhereHas('site.baseSite', function($b) use ($assignment) {
                              $b->where('client_id', $assignment->client_id);
                          });
                    });
                }
            }
        } elseif ($user && $user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            $query->whereHas('site', function($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        }

        $equipements = $query->get(['id', 'equipement_code', 'equipement_nom', 'site_id', 'client_id']);

        return response()->json($equipements);
    }
    
    // API pour récupérer les sites d'une base
    public function getSitesByBase($baseId)
    {
        $sites = \App\Models\Site::where('base_id', $baseId)->orderBy('nom_site')->get();
        return response()->json($sites);
    }

    // API pour récupérer les équipements d'une base
    public function getEquipementsByBase($baseId)
    {
        $equipements = \App\Models\Equipement::whereHas('site', function($q) use ($baseId) {
            $q->where('base_id', $baseId);
        })->get(['id', 'equipement_code', 'equipement_nom']);
        
        return response()->json($equipements);
    }

    // API pour récupérer les sites directs d'un client (sans passer par une base)
    public function getClientSitesAll($clientId)
    {
        // Récupérer tous les sites du client (avec ou sans base)
        $sites = \App\Models\Site::where(function($q) use ($clientId) {
            $q->where('client_id', $clientId)
              ->orWhereHas('baseSite', function($b) use ($clientId) {
                  $b->where('client_id', $clientId);
              });
        })->orderBy('nom_site')->get(['id', 'nom_site', 'code_site', 'base_id']);
        
        return response()->json($sites);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (in_array($user->type_utilisateur, ['superviseur_client', 'demandeur'])) {
            return redirect()->route('equipements.index')->with('error', 'Seuls les administrateurs et le personnel Soutarah peuvent modifier les équipements.');
        }

        $equipement = Equipement::findOrFail($id);

        // Vérifier les permissions selon le rôle
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            // Superviseur Client ne peut modifier que les équipements de SA base
            if (!$equipement->site || $equipement->site->base_id != $user->base_id) {
                return redirect()->route('equipements.index')->with('error', 'Vous ne pouvez modifier que les équipements de votre base.');
            }
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            // Superviseur Soutarah ne peut modifier que les équipements de SA base/client
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $equipement->site && $equipement->site->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $equipement->site && $equipement->site->baseSite && $equipement->site->baseSite->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('equipements.index')->with('error', 'Vous ne pouvez modifier que les équipements de votre périmètre.');
            }
        }

        $validated = $request->validate([
            'equipement_code' => 'required|string|max:50',
            'equipement_numero' => 'nullable|string|max:3',
            'equipement_nom' => 'required|string|max:150',
            'marque' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'emplacement' => 'nullable|in:interne,externe',
            'puissance' => 'nullable|string|max:50',
            'refrigerant' => 'nullable|string|max:50',
            'mois_installation' => 'nullable|integer|min:1|max:12',
            'annee_installation' => 'nullable|integer|min:1900|max:2100',
            'type_unite' => 'nullable|in:Exterieure,Interieure,Inconnue',
            'num_sur_site' => 'nullable|integer',
            'base_id' => 'nullable|integer',
            'site_id' => 'nullable|integer',
            'zone_id' => 'nullable|integer',
            'date_acquisition' => 'nullable|date',
            'etat' => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ]);

        $equipement->update($validated);

        return redirect()->route('equipements.show', $equipement->id)
            ->with('success', 'Équipement mis à jour avec succès !');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (in_array($user->type_utilisateur, ['superviseur_client', 'demandeur'])) {
            return redirect()->route('equipements.index')->with('error', 'Seuls les administrateurs et le personnel Soutarah peuvent supprimer des équipements.');
        }

        $equipement = Equipement::findOrFail($id);

        // Vérifier les permissions selon le rôle
        if ($user->type_utilisateur === 'superviseur_client' && $user->base_id) {
            // Superviseur Client ne peut supprimer que les équipements de SA base
            if (!$equipement->site || $equipement->site->base_id != $user->base_id) {
                return redirect()->route('equipements.index')->with('error', 'Vous ne pouvez supprimer que les équipements de votre base.');
            }
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            // Superviseur Soutarah ne peut supprimer que les équipements de SA base/client
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $equipement->site && $equipement->site->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $equipement->site && $equipement->site->baseSite && $equipement->site->baseSite->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('equipements.index')->with('error', 'Vous ne pouvez supprimer que les équipements de votre périmètre.');
            }
        }
        
        $equipement->delete();

        return redirect()->route('equipements.index')
            ->with('success', 'Équipement supprimé avec succès.');
    }

    /**
     * Afficher le formulaire de complétion d'équipement
     * (Pour Admin/Superviseur Soutarah seulement)
     */
    public function completeForm($demandeId)
    {
        $user = auth()->user();
        
        // Seulement Admin et Superviseur Soutarah
        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah'])) {
            return redirect()->route('demandes.index')->with('error', 'Accès refusé.');
        }
        
        $demande = \App\Models\Demande::with(['client', 'base', 'site'])->findOrFail($demandeId);
        
        // Vérifier que la demande nécessite bien une complétion
        if (!$demande->equipement_needs_completion) {
            return redirect()->route('demandes.show', $demandeId)
                ->with('error', 'Cette demande ne nécessite pas de complétion d\'équipement.');
        }
        
        // Vérifier les permissions Superviseur Soutarah
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $demande->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $demande->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('demandes.index')->with('error', 'Cette demande n\'appartient pas à votre périmètre.');
            }
        }
        
        // Récupérer les équipes et techniciens pour l'affectation
        $equipes = \App\Models\Equipe::all();
        $techniciens = \App\Models\User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();
        
        return view('equipements.complete', compact('demande', 'equipes', 'techniciens'));
    }

    /**
     * Compléter l'équipement et créer l'opération technique
     */
    public function completeStore(Request $request, $demandeId)
    {
        $user = auth()->user();
        
        // Seulement Admin et Superviseur Soutarah
        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah'])) {
            return redirect()->route('demandes.index')->with('error', 'Accès refusé.');
        }
        
        $demande = \App\Models\Demande::findOrFail($demandeId);
        
        // Vérifier que la demande nécessite bien une complétion
        if (!$demande->equipement_needs_completion) {
            return redirect()->route('demandes.show', $demandeId)
                ->with('error', 'Cette demande ne nécessite pas de complétion d\'équipement.');
        }
        
        // Vérifier les permissions Superviseur Soutarah
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            $hasAccess = false;
            if ($assignment) {
                if ($assignment->base_id && $demande->base_id == $assignment->base_id) {
                    $hasAccess = true;
                } elseif ($assignment->client_id && $demande->client_id == $assignment->client_id) {
                    $hasAccess = true;
                }
            }
            if (!$hasAccess) {
                return redirect()->route('demandes.index')->with('error', 'Cette demande n\'appartient pas à votre périmètre.');
            }
        }
        
        // Extraire automatiquement mois et année à partir de date_installation_souhaitee
        if ($request->has('date_installation_souhaitee') && !empty($request->date_installation_souhaitee)) {
            $time = strtotime($request->date_installation_souhaitee);
            if ($time !== false) {
                $request->merge([
                    'mois_installation'  => (int) date('m', $time),
                    'annee_installation' => (int) date('Y', $time),
                ]);
            }
        }
        
        $validated = $request->validate([
            'equipement_code' => 'required|string|max:50',
            'equipement_numero' => 'nullable|string|max:3',
            'marque' => 'required|string|max:100',
            'puissance' => 'required|string|max:50',
            'refrigerant' => 'nullable|string|max:50',
            'mois_installation' => 'nullable|numeric|between:1,12',
            'annee_installation' => 'nullable|numeric|between:1900,2100',
            'type_unite' => 'nullable|in:Exterieure,Interieure,Inconnue',
            'num_sur_site' => 'nullable',
            'observations' => 'nullable|string',
            'equipe_id' => 'nullable|integer|exists:equipes,id',
            'technicien_id' => 'nullable|integer|exists:utilisateurs,id',
            'date_installation_souhaitee' => 'required|date',
        ]);
        
        // Vérifier qu'au moins équipe OU technicien est sélectionné
        if (empty($validated['equipe_id']) && empty($validated['technicien_id'])) {
            return back()->withErrors(['equipe_id' => 'Vous devez sélectionner au moins une équipe OU un technicien.'])->withInput();
        }
        
        try {
            \DB::beginTransaction();
            
            // 1. Créer l'équipement avec toutes les informations
            $equipement = Equipement::create([
                'equipement_code' => $validated['equipement_code'],
                'equipement_numero' => $validated['equipement_numero'] ?? null,
                'equipement_nom' => $demande->temp_equipement_nom,
                'type' => $demande->temp_equipement_type,
                'emplacement' => $demande->temp_equipement_emplacement,
                'marque' => $validated['marque'],
                'puissance' => $validated['puissance'],
                'refrigerant' => $validated['refrigerant'] ?? null,
                'mois_installation' => $validated['mois_installation'] ?? null,
                'annee_installation' => $validated['annee_installation'] ?? null,
                'type_unite' => $validated['type_unite'] ?? 'Inconnue',
                'num_sur_site' => $validated['num_sur_site'] ?? null,
                'client_id' => $demande->client_id,
                'base_id' => $demande->base_id,
                'site_id' => $demande->site_id,
                'etat' => 'en attente installation',
                'observations' => $validated['observations'] ?? null,
            ]);
            
            // 2. Lier l'équipement à la demande
            $demande->update([
                'equipement_id' => $equipement->id,
                'equipement_needs_completion' => false,
                'temp_equipement_nom' => null,
                'temp_equipement_type' => null,
                'temp_equipement_emplacement' => null,
            ]);
            
            // 3. Créer l'opération technique (Dépannage/Installation)
            $depannage = \App\Models\Depannage::create([
                'demande_id' => $demande->id,
                'type_intervention' => 'Installation',
                'created_by_role' => 'superviseur_soutarah',
                'client_id' => $demande->client_id,
                'equipement_id' => $equipement->id,
                'equipement_reference' => $equipement->equipement_code,
                'description_panne' => $demande->description,
                'date_debut_prevue' => $validated['date_installation_souhaitee'],
                'date_fin_prevue' => $validated['date_installation_souhaitee'],
                'urgence' => $demande->niveau_urgence ?? 'moyen',
                'statut' => 'en attente',
                'date_demande' => now(),
                'technicien_id' => $validated['technicien_id'] ?? null,
                'equipe_id' => $validated['equipe_id'] ?? null,
            ]);
            
            // 4. Lier l'opération à la demande
            $demande->update([
                'technical_operation_id' => $depannage->id,
                'statut' => 'needs_technical_operation',
            ]);
            
            // 5. Créer la planification
            $equipe = $validated['equipe_id'] ? \App\Models\Equipe::find($validated['equipe_id']) : null;
            
            $planification = \App\Models\Planification::create([
                'depannage_id' => $depannage->id,
                'demande_id' => $demande->id,
                'client_id' => $demande->client_id,
                'site_code' => $demande->site ? $demande->site->code_site : 'N/A',
                'nom_equipe' => $equipe ? $equipe->nom_equipe : 'Technicien individuel',
                'statut' => 'Planifié',
                'date_debut' => $validated['date_installation_souhaitee'],
                'date_fin' => $validated['date_installation_souhaitee'],
                'type_intervention' => 'Installation',
                'equipe_id' => $validated['equipe_id'] ?? null,
                'technicien_id' => $validated['technicien_id'] ?? null,
                'commentaire' => "Installation de l'équipement {$equipement->equipement_code}",
                'created_by' => $user->id,
                'created_at' => now(), // IMPORTANT: Timestamp explicite car le modèle a timestamps = false
            ]);
            
            \Log::info('[PLANIFICATION CREATED]', [
                'planification_id' => $planification->id,
                'depannage_id' => $depannage->id,
                'demande_id' => $demande->id,
                'technicien_id' => $planification->technicien_id,
                'equipe_id' => $planification->equipe_id,
                'date_debut' => $planification->date_debut,
                'date_fin' => $planification->date_fin,
            ]);
            
            // 6. Notifier le technicien/équipe
            $dateDebut = \Carbon\Carbon::parse($validated['date_installation_souhaitee'])->format('d/m/Y');
            
            if ($validated['technicien_id']) {
                $technicien = \App\Models\User::find($validated['technicien_id']);
                if ($technicien) {
                    \App\Models\InterventionNotification::create([
                        'user_id' => $technicien->id,
                        'intervention_id' => $depannage->id,
                        'demande_id' => $demande->id,
                        'type' => 'planification_creee',
                        'message' => "Installation planifiée pour le {$dateDebut} - Équipement {$equipement->equipement_code}",
                        'statut' => 'non_lu',
                    ]);
                }
            }
            
            if ($validated['equipe_id']) {
                $equipe = \App\Models\Equipe::with('membres', 'chef')->find($validated['equipe_id']);
                if ($equipe) {
                    // Notifier le chef d'équipe
                    if ($equipe->chef) {
                        \App\Models\InterventionNotification::create([
                            'user_id' => $equipe->chef->id,
                            'intervention_id' => $depannage->id,
                            'demande_id' => $demande->id,
                            'type' => 'planification_creee',
                            'message' => "Installation planifiée pour votre équipe {$equipe->nom_equipe} le {$dateDebut}",
                            'statut' => 'non_lu',
                        ]);
                    }
                    
                    // Notifier tous les membres
                    foreach ($equipe->membres as $membre) {
                        \App\Models\InterventionNotification::create([
                            'user_id' => $membre->id,
                            'intervention_id' => $depannage->id,
                            'demande_id' => $demande->id,
                            'type' => 'planification_creee',
                            'message' => "Installation planifiée pour votre équipe {$equipe->nom_equipe} le {$dateDebut}",
                            'statut' => 'non_lu',
                        ]);
                    }
                }
            }
            
            \DB::commit();
            
            return redirect()->route('demandes.show', $demande->id)
                ->with('success', "Équipement créé avec succès ! L'installation a été planifiée et l'équipe/technicien a été notifié.");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Illuminate\Support\Facades\Log::error('[EQUIPEMENT COMPLETE ERROR] ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => $user->id,
                'demande_id' => $demandeId,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la complétion de l\'équipement : ' . $e->getMessage());
        }
    }
}
