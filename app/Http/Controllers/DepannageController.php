<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Client;
use App\Models\Depannage;
use App\Models\Equipe;
use App\Models\Equipement;
use App\Models\User;
use App\Models\InterventionNotification;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepannageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // ── Les techniciens ont leur propre page ──────────────────────────
        if (in_array($role, ['technicien', 'chef technicien'])) {
            return redirect()->route('technicien.interventions');
        }

        $query = Depannage::with(['technicien', 'equipe', 'demandeur', 'equipement.site', 'client']);

        // ── Filtrage strict par rôle ──────────────────────────────────────
        if ($role === 'superviseur_client') {
            // Superviseur Client : uniquement les interventions de SA base ou de son client
            if ($user->base_id) {
                $query->whereHas('equipement.site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $query->whereHas('equipement', function ($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            } else {
                // Si ni base_id ni client_id, ne voir aucune intervention
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'superviseur_soutarah') {
            // Superviseur Soutarah : uniquement les interventions de SA base/company assignée
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                // Si pas d'assignment, ne voir aucune intervention
                $query->whereRaw('1 = 0');
            }
        }
        // Admin : voit tout (pas de filtre)

        // ── Filtres dynamiques ────────────────────────────────────────────
        // Par défaut, afficher les Installations si aucun filtre n'est appliqué
        if (!$request->has('type') && !$request->has('statut') && !$request->has('urgence') && !$request->has('search')) {
            $query->where('type_intervention', 'Installation');
        } elseif ($type = $request->input('type')) {
            $query->where('type_intervention', $type);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }
        if ($urgence = $request->input('urgence')) {
            $query->where('urgence', $urgence);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('equipement_reference', 'like', "%{$search}%")
                    ->orWhere('description_panne', 'like', "%{$search}%")
                    ->orWhere('demandeur_nom', 'like', "%{$search}%");
            });
        }

        $depannages   = $query->orderBy('id', 'desc')->paginate(15);
        $totalCount   = (clone $query)->count();

        // Compteurs par type selon le rôle
        $baseQuery = Depannage::query();

        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $baseQuery->whereHas('equipement.site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $baseQuery->whereHas('equipement', function ($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $baseQuery->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $baseQuery->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
        }

        $installationsCount = (clone $baseQuery)->where('type_intervention', 'Installation')->count();
        $depannagesCount    = (clone $baseQuery)->where('type_intervention', 'Dépannage')->count();

        // Compteur d'attente selon le rôle
        $attenteCount = (clone $baseQuery)->where('statut', 'en attente')->count();

        return view('depannages.index', compact('depannages', 'totalCount', 'attenteCount', 'installationsCount', 'depannagesCount'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        $depannage = Depannage::with(['demande', 'technicien', 'equipe.chef', 'equipe.membres', 'demandeur', 'equipement.site.baseSite', 'client'])
            ->findOrFail($id);

        // Si le RI Soutarah est vide mais présent sur la demande d'origine, synchroniser automatiquement
        if (empty($depannage->ri_soutarah) && $depannage->demande && !empty($depannage->demande->numero_reference_externe)) {
            $depannage->ri_soutarah = $depannage->demande->numero_reference_externe;
            try {
                \Illuminate\Support\Facades\DB::table('depannages')->where('id', $depannage->id)->update([
                    'ri_soutarah' => $depannage->ri_soutarah
                ]);
            } catch (\Throwable $e) {}
        }

        // ── Droits d'accès ────────────────────────────────────────────────
        // Superviseur Client ne peut voir que les interventions de SA base ou de son client
        if ($role === 'superviseur_client') {
            $hasAccess = false;

            if ($user->base_id && $depannage->equipement && $depannage->equipement->site && $depannage->equipement->site->base_id == $user->base_id) {
                $hasAccess = true;
            } elseif ($user->client_id && $depannage->equipement && $depannage->equipement->client_id == $user->client_id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                return redirect()->route('dashboard')->with('error', 'Cette intervention n\'appartient pas à votre périmètre.');
            }
        }

        // Superviseur Soutarah ne peut voir que les interventions de SA base/company
        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if (!$assignment) {
                return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas d\'assignation.');
            }

            $hasAccess = false;
            if ($assignment->base_id && $depannage->equipement && $depannage->equipement->site) {
                $hasAccess = ($depannage->equipement->site->base_id == $assignment->base_id);
            } elseif ($assignment->client_id && $depannage->equipement && $depannage->equipement->site && $depannage->equipement->site->baseSite) {
                $hasAccess = ($depannage->equipement->site->baseSite->client_id == $assignment->client_id);
            }

            if (!$hasAccess) {
                return redirect()->route('dashboard')->with('error', 'Cette intervention n\'appartient pas à votre périmètre.');
            }
        }

        // Technicien ne peut voir que ses propres interventions ou celles de ses équipes
        if (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            $appartientAEquipe = in_array($depannage->equipe_id, $equipesIds);

            if ($depannage->technicien_id != $user->id && !$appartientAEquipe) {
                return redirect()->route('dashboard')->with('error', 'Cette intervention ne vous est pas assignée.');
            }
        }

        // ── Le technicien peut soumettre un compte-rendu si :
        //    - Il est le technicien assigné directement, OU
        //    - Il est le chef de l'équipe assignée, OU
        //    - Il est membre de l'équipe assignée
        $peutSoumettreCR = false;
        if (in_array($role, ['technicien', 'chef technicien'])) {
            if ($depannage->technicien_id == $user->id) {
                $peutSoumettreCR = true;
            }
            if ($depannage->equipe_id && $depannage->equipe) {
                // Vérifier si l'utilisateur est LE chef de cette équipe
                if ($depannage->equipe->chef_equipe == $user->id) {
                    $peutSoumettreCR = true;
                }
                // Vérifier si l'utilisateur est membre de cette équipe
                if ($depannage->equipe->membres->contains('id', $user->id)) {
                    $peutSoumettreCR = true;
                }
            }
        }

        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();
        $equipes     = Equipe::with(['chef', 'membres'])->get();

        return view('depannages.show', compact('depannage', 'techniciens', 'equipes', 'peutSoumettreCR'));
    }

    public function create()
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // Les techniciens ont leur propre page - redirection
        if (in_array($role, ['technicien', 'chef technicien'])) {
            return redirect()->route('technicien.interventions')
                ->with('info', 'Consultez vos interventions assignées ci-dessous.');
        }

        // Superviseur Client: équipements de SA base ou de son client
        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $equipements = Equipement::whereHas('site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                })->orderBy('equipement_code')->get();
            } elseif ($user->client_id) {
                $equipements = Equipement::where('client_id', $user->client_id)
                    ->orderBy('equipement_code')->get();
            } else {
                $equipements = collect();
            }
        } else {
            // Admin: tous les équipements
            $equipements = Equipement::with('baseSite')->orderBy('equipement_code')->get();
        }

        // Techniciens disponibles (seulement pour l'admin)
        $techniciens = [];
        if ($role === 'admin') {
            $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();
        }

        return view('depannages.create', compact('equipements', 'techniciens'));
    }

    /**
     * Créer une opération technique à partir d'une demande validée
     */
    public function createFromDemande($demandeId)
    {
        $user = Auth::user();

        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah'])) {
            abort(403);
        }

        $demande = \App\Models\Demande::with(['site', 'equipement', 'client', 'base'])->findOrFail($demandeId);

        if ($demande->statut != 'needs_technical_operation') {
            return redirect()->route('demandes.show', $demande)
                ->with('error', 'Cette demande n\'est pas prête pour une opération technique.');
        }

        // Pré-remplir les données depuis la demande
        $equipements = Equipement::with('baseSite')->orderBy('equipement_code')->get();
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();
        $equipes = \App\Models\Equipe::with('chef')->get();

        return view('depannages.create_from_demande', compact('demande', 'equipements', 'techniciens', 'equipes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // Bloquer les techniciens
        if (in_array($role, ['technicien', 'chef technicien'])) {
            return redirect()->route('depannages.index')
                ->with('error', 'Vous n\'êtes pas autorisé à créer des demandes d\'intervention.');
        }

        $rules = [
            'type_intervention'     => 'required|string|in:Dépannage,Installation',
            'equipement_id'         => 'nullable|integer',
            'equipement_reference'  => 'nullable|string|max:100',
            'description_panne'     => 'required|string',
            'urgence'               => 'required|string|in:Urgent,Normal,Faible',
            'date_debut_souhaitee'  => 'nullable|date',
            'photo_panne'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'fichier_joint'         => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,zip|max:10240',
            'base_code'             => 'nullable|string|max:100',
        ];

        // Admin peut assigner technicien, pas superviseur
        if ($role === 'admin') {
            $rules['technicien_id'] = 'nullable|integer';
            $rules['equipe_id']     = 'nullable|integer';
            $rules['date_prevue']   = 'nullable|date';
        }

        $validated = $request->validate($rules);

        // Résolution équipement
        if (!empty($validated['equipement_id'])) {
            $eq = Equipement::find($validated['equipement_id']);
            if ($eq) {
                $validated['equipement_reference'] = $eq->equipement_code . ' - ' . $eq->equipement_nom;
                if ($eq->site_id) {
                    $validated['base_code'] = (string)$eq->site_id;
                }
            }
        }
        if (empty($validated['equipement_reference'])) {
            $validated['equipement_reference'] = 'Équipement non spécifié';
        }

        // Résolution client & demandeur
        $clientId  = $user->client_id;
        $clientNom = null;
        if ($clientId) {
            $cl = Client::find($clientId);
            $clientNom = $cl ? $cl->nom : null;
        }

        // Uploads
        if ($request->hasFile('photo_panne')) {
            $photo = $request->file('photo_panne');
            $photoName = 'panne_' . time() . '_' . rand(100, 999) . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/depannages'), $photoName);
            $validated['photo_panne'] = '/uploads/depannages/' . $photoName;
        }
        if ($request->hasFile('fichier_joint')) {
            $file = $request->file('fichier_joint');
            $fileName = 'doc_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/depannages'), $fileName);
            $validated['fichier_joint'] = '/uploads/depannages/' . $fileName;
        }

        $validated['client_id']     = $clientId;
        $validated['client_nom']    = $clientNom ?? $user->nom_complet;
        $validated['demandeur_id']  = $user->id;
        $validated['demandeur_nom'] = $user->nom_complet;
        $validated['date_demande']  = now()->toDateString();
        $validated['etat_demande']  = 'Soumise';
        $validated['created_by_role'] = $role; // Enregistrer qui a créé

        // WORKFLOW DE VALIDATION - NOUVELLE LOGIQUE
        if ($role === 'admin') {
            // Admin crée → Doit être validé par le superviseur (SANS ASSIGNATION)
            $validated['statut'] = 'en attente validation superviseur';
            $validated['statut_validation_superviseur'] = 'en attente';
            // PAS d'assignation à la création
            $validated['technicien_id'] = null;
            $validated['equipe_id'] = null;
        } else if ($role === 'superviseur_client') {
            // Superviseur Client crée → Doit être validé par l'admin (SANS ASSIGNATION)
            $validated['statut'] = 'en attente validation admin';
            $validated['statut_validation_admin'] = 'en attente';
            // PAS d'assignation à la création
            $validated['technicien_id'] = null;
            $validated['equipe_id'] = null;
        }

        // Gérer le lien avec une demande si présent
        if ($request->has('demande_id')) {
            $validated['demande_id'] = $request->demande_id;
            $validated['statut'] = 'assigned'; // Directement assigné si depuis demande
            $validated['statut_operation'] = 'assigned';
        }

        if (!empty($validated['date_debut_souhaitee'])) {
            $validated['date_debut_prevue'] = $validated['date_debut_souhaitee'];
            $validated['date_fin_prevue'] = $validated['date_debut_souhaitee'];
        }

        $depannage = Depannage::create($validated);

        // Si créé depuis une demande, mettre à jour la demande
        if ($request->has('demande_id')) {
            $demande = \App\Models\Demande::find($request->demande_id);
            if ($demande) {
                $demande->update([
                    'technical_operation_id' => $depannage->id,
                ]);
            }
        }

        // ENVOI DE NOTIFICATION
        if ($role === 'admin') {
            // Notifier le superviseur client de la base concernée
            $equipement = Equipement::find($validated['equipement_id']);
            if ($equipement && $equipement->site_id) {
                $superviseur = User::where('type_utilisateur', 'superviseur_client')
                    ->where('base_id', $equipement->site_id)
                    ->first();

                if ($superviseur) {
                    NotificationController::create(
                        $superviseur->id,
                        $depannage->id,
                        'demande_validation_superviseur_client',
                        "Nouvelle intervention à valider",
                        "L'Admin souhaite planifier une intervention {$validated['type_intervention']} dans votre base le " . ($validated['date_prevue'] ?? 'à définir')
                    );
                }
            }
        } else if ($role === 'superviseur_client') {
            // Notifier l'admin
            $admin = User::where('type_utilisateur', 'admin')->first();
            if ($admin) {
                NotificationController::create(
                    $admin->id,
                    $depannage->id,
                    'demande_validation_admin',
                    "Nouvelle demande d'intervention",
                    "Le superviseur client {$user->nom_complet} demande une intervention {$validated['type_intervention']} pour sa base"
                );
            }
        }

        return redirect()->route('depannages.show', $depannage->id)
            ->with('success', 'Intervention enregistrée avec succès ! En attente de validation.');
    }

    /**
     * Assignation : technicien seul OU équipe entière
     * Réservée aux Admins & Superviseurs uniquement
     */
    public function assignTechnician(Request $request, $id)
    {
        $depannage = Depannage::findOrFail($id);

        $request->validate([
            'mode_affectation'  => 'required|string|in:technicien,equipe',
            'technicien_id'     => 'nullable|integer',
            'equipe_id'         => 'nullable|integer',
            'date_debut_prevue' => 'nullable|date',
            'date_fin_prevue'   => 'nullable|date|after_or_equal:date_debut_prevue',
            'date_prevue'       => 'nullable|date',
            'ri_soutarah'       => 'nullable|string|max:50',
        ]);

        // Réinitialiser les deux avant d'assigner
        $depannage->technicien_id = null;
        $depannage->equipe_id     = null;

        $technicienAssigne = null;
        $equipeAssignee = null;

        if ($request->mode_affectation === 'technicien') {
            $request->validate(['technicien_id' => 'required|integer']);
            $depannage->technicien_id = $request->technicien_id;
            $technicienAssigne = User::find($request->technicien_id);

            // Notifier le technicien assigné
            if ($technicienAssigne) {
                NotificationController::create(
                    $technicienAssigne->id,
                    $depannage->id,
                    'assignation_technicien',
                    "Nouvelle intervention assignée #$id",
                    "Une intervention {$depannage->type_intervention} vous a été assignée pour {$depannage->equipement_reference}"
                );
            }
        } else {
            $request->validate(['equipe_id' => 'required|integer']);
            $depannage->equipe_id = $request->equipe_id;
            $equipeAssignee = Equipe::with('membres', 'chef')->find($request->equipe_id);

            // Notifier tous les membres de l'équipe
            if ($equipeAssignee) {
                // Notifier le chef
                if ($equipeAssignee->chef) {
                    NotificationController::create(
                        $equipeAssignee->chef->id,
                        $depannage->id,
                        'assignation_equipe',
                        "Nouvelle intervention pour votre équipe #$id",
                        "L'équipe {$equipeAssignee->nom_equipe} a été assignée à une intervention {$depannage->type_intervention}"
                    );
                }

                // Notifier tous les membres
                foreach ($equipeAssignee->membres as $membre) {
                    NotificationController::create(
                        $membre->id,
                        $depannage->id,
                        'assignation_equipe',
                        "Nouvelle intervention d'équipe #$id",
                        "Votre équipe {$equipeAssignee->nom_equipe} a été assignée à une intervention {$depannage->type_intervention}"
                    );
                }
            }
        }

        // Gérer la date d'intervention
        if ($request->filled('date_prevue')) {
            $depannage->date_prevue = $request->date_prevue;
            $depannage->date_debut_prevue = $request->date_prevue;
            $depannage->date_fin_prevue = $request->date_prevue;
        } elseif ($request->filled('date_debut_prevue')) {
            $depannage->date_debut_prevue = $request->date_debut_prevue;
            $depannage->date_prevue = $request->date_debut_prevue;
            if ($request->filled('date_fin_prevue')) {
                $depannage->date_fin_prevue = $request->date_fin_prevue;
            } else {
                $depannage->date_fin_prevue = $request->date_debut_prevue;
            }
        } else {
            // Si aucune date n'est fournie, utiliser la date de la demande
            if ($depannage->demande && $depannage->demande->date_debut_souhaitee) {
                $dateFromDemande = optional($depannage->demande->date_debut_souhaitee)->toDateString();
                $depannage->date_debut_prevue = $dateFromDemande;
                $depannage->date_fin_prevue = $dateFromDemande;
                $depannage->date_prevue = $dateFromDemande;
            }
        }

        // Gérer le RI Soutarah si renseigné lors de l'affectation
        if ($request->filled('ri_soutarah')) {
            $depannage->ri_soutarah = trim($request->ri_soutarah);
        } elseif (empty($depannage->ri_soutarah) && $depannage->demande && !empty($depannage->demande->numero_reference_externe)) {
            $depannage->ri_soutarah = $depannage->demande->numero_reference_externe;
        }

        $depannage->statut = 'en cours';
        $depannage->save();

        // ═══════════════════════════════════════════════════════════════════
        // CRÉER AUTOMATIQUEMENT UNE PLANIFICATION DANS LE CALENDRIER
        // ═══════════════════════════════════════════════════════════════════
        if ($depannage->demande_id) {
            $demande = \App\Models\Demande::with(['client', 'site'])->find($depannage->demande_id);

            if ($demande) {
                // Créer une planification automatique avec la plage de dates
                $planification = new \App\Models\Planification();
                $planification->demande_id = $demande->id;
                $planification->depannage_id = $depannage->id;
                $planification->client_id = $demande->client_id;
                $planification->site_code = $demande->site ? $demande->site->code_site : 'N/A';
                $planification->type_intervention = $depannage->type_intervention;
                $dateDeb = $depannage->date_debut_prevue ?? $depannage->date_prevue ?? now()->addDay();
                $dateFin = $depannage->date_fin_prevue ?? $depannage->date_prevue ?? $dateDeb;
                $planification->date_debut = $dateDeb;
                $planification->date_fin = $dateFin;

                if ($request->mode_affectation === 'technicien') {
                    $planification->technicien_id = $request->technicien_id;
                    $planification->nom_equipe = 'Technicien individuel';
                } else {
                    $planification->equipe_id = $request->equipe_id;
                    $planification->nom_equipe = $equipeAssignee ? $equipeAssignee->nom_equipe : 'Équipe';
                }

                $planification->statut = 'Planifié';
                $planification->commentaire = "Affectation automatique depuis l'intervention #{$depannage->id}";
                $planification->created_by = Auth::id();
                $planification->save();
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // FEATURE 2: NOTIFICATION AU SUPERVISEUR CLIENT APRÈS ASSIGNATION
        // ═══════════════════════════════════════════════════════════════════
        // Notifier le superviseur client de la base concernée
        if ($depannage->equipement && $depannage->equipement->site && $depannage->equipement->site->base_id) {
            $superviseur = User::where('type_utilisateur', 'superviseur_client')
                ->where('base_id', $depannage->equipement->site->base_id)
                ->first();

            if ($superviseur) {
                $techInfo = '';
                if ($technicienAssigne) {
                    $techInfo = "Technicien {$technicienAssigne->nom_complet} assigné";
                } elseif ($equipeAssignee) {
                    $techInfo = "Équipe {$equipeAssignee->nom_equipe} assignée";
                }

                $dateInfo = $depannage->date_prevue
                    ? " — Prévue le " . \Carbon\Carbon::parse($depannage->date_prevue)->format('d/m/Y')
                    : " — Date à définir";

                NotificationController::create(
                    $superviseur->id,
                    $depannage->id,
                    'technicien_assigne',
                    "Intervention #{$id} : Technicien assigné",
                    $techInfo . $dateInfo
                );
            }
        }

        $label = $request->mode_affectation === 'technicien' ? 'Technicien' : 'Équipe';
        return redirect()->route('depannages.show', $id)
            ->with('success', $label . ' affecté(e) avec succès à l\'intervention #' . $id . ' !');
    }

    /**
     * Modifier le RI Soutarah d'une intervention (champ éditable à tout moment)
     * Autorisé : Admin, Superviseur Soutarah, Technicien assigné / membre équipe
     */
    public function updateRiSoutarah(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->back()->with('error', 'Session expirée. Reconnectez-vous.');
            }

            $depannage = Depannage::with(['equipe.membres', 'equipe.chef'])->find($id);
            if (!$depannage) {
                return redirect()->back()->with('error', 'Intervention #' . $id . ' introuvable.');
            }

            $role = $user->type_utilisateur;

            // Seul l'Administrateur et le Superviseur Soutarah ont le droit de définir ou modifier le RI Soutarah
            if (!in_array($role, ['admin', 'superviseur_soutarah'])) {
                return redirect()->back()->with('error', 'Seul un Administrateur ou un Superviseur Soutarah est autorisé à définir ou modifier le RI Soutarah.');
            }

            $request->validate([
                'ri_soutarah' => 'required|string|max:50',
            ], [
                'ri_soutarah.required' => 'Le RI Soutarah est obligatoire.',
                'ri_soutarah.max'      => 'Le RI Soutarah ne peut pas dépasser 50 caractères.',
            ]);

            $nouveauRi = trim((string)$request->ri_soutarah);

            $hasRiCol = false;
            try {
                $sqlCheckCol = "SELECT COUNT(*) AS nb FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_SCHEMA = DATABASE()
                                  AND TABLE_NAME   = 'depannages'
                                  AND COLUMN_NAME  = 'ri_soutarah'";
                $checkRes = \Illuminate\Support\Facades\DB::selectOne($sqlCheckCol);
                $hasRiCol = $checkRes && ($checkRes->nb ?? 0) > 0;
            } catch (\Throwable $eCol) {
                $hasRiCol = false;
            }

            if (!$hasRiCol) {
                return redirect()->back()->with('error', 'La colonne ri_soutarah n\'existe pas encore dans la base de données. Veuillez lancer la migration sur le serveur (php artisan migrate --force).');
            }

            \Illuminate\Support\Facades\DB::table('depannages')->where('id', $id)->update([
                'ri_soutarah' => $nouveauRi,
            ]);

            return redirect()->route('depannages.show', $id)
                ->with('success', 'RI Soutarah mis à jour avec succès : <strong>' . htmlspecialchars($nouveauRi, ENT_QUOTES, 'UTF-8') . '</strong>');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du RI : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour du statut & compte-rendu
     * RÉSERVÉE au technicien assigné ou membre/chef de l'équipe assignée
     * 
     * ═══════════════════════════════════════════════════════════════════
     * FEATURE 5: NOTIFICATIONS QUAND CR SOUMIS
     * Notifier Admin + Superviseur quand le CR est soumis
     * ═══════════════════════════════════════════════════════════════════
     */
    public function updateStatus(Request $request, $id)
    {
        $user      = Auth::user();
        $role      = $user->type_utilisateur;
        $depannage = Depannage::with(['equipe.membres', 'equipe.chef', 'equipement'])->findOrFail($id);

        // Vérification stricte : seul le technicien assigné ou un membre/chef de l'équipe peut écrire le CR
        $autorise = false;
        if (in_array($role, ['technicien', 'chef technicien'])) {
            if ($depannage->technicien_id == $user->id) {
                $autorise = true;
            }
            if ($depannage->equipe_id && $depannage->equipe) {
                // Vérifier si l'utilisateur est LE chef de cette équipe
                if ($depannage->equipe->chef_equipe == $user->id) {
                    $autorise = true;
                }
                // Vérifier si l'utilisateur est membre de cette équipe
                if ($depannage->equipe->membres->contains('id', $user->id)) {
                    $autorise = true;
                }
            }
        }

        if (!$autorise) {
            return redirect()->route('depannages.show', $id)
                ->with('error', 'Seul le technicien assigné ou un membre de l\'équipe assignée peut rédiger le compte-rendu.');
        }

        $request->validate([
            'statut'             => 'required|string|in:en attente,en cours,résolu',
            'rapport_technicien' => 'nullable|string',
            'ri_soutarah'        => 'nullable|string|max:50',
        ]);

        $ancienStatut = $depannage->statut;
        $depannage->statut = $request->statut;

        if ($request->filled('rapport_technicien')) {
            $depannage->rapport = $request->rapport_technicien;
        }

        if ($request->filled('ri_soutarah')) {
            try {
                $sqlRiCheck = "SELECT COUNT(*) AS nb FROM INFORMATION_SCHEMA.COLUMNS
                               WHERE TABLE_SCHEMA = DATABASE()
                                 AND TABLE_NAME   = 'depannages'
                                 AND COLUMN_NAME  = 'ri_soutarah'";
                $chkRi = \Illuminate\Support\Facades\DB::selectOne($sqlRiCheck);
                if ($chkRi && ($chkRi->nb ?? 0) > 0) {
                    $depannage->ri_soutarah = trim((string)$request->ri_soutarah);
                }
            } catch (\Throwable $eColRi) {
            }
        }

        if ($request->statut === 'résolu') {
            $depannage->date_realisation = now()->toDateString();
        }

        $depannage->save();

        // ═══════════════════════════════════════════════════════════════════
        // NOTIFIER ADMIN + SUPERVISEUR QUAND LE CR EST SOUMIS
        // ═══════════════════════════════════════════════════════════════════
        if ($request->filled('rapport_technicien') && $request->rapport_technicien !== '') {
            // Notifier l'admin
            $admin = User::where('type_utilisateur', 'admin')->first();
            if ($admin) {
                NotificationController::create(
                    $admin->id,
                    $depannage->id,
                    'compte_rendu_soumis',
                    "Compte-rendu soumis pour intervention #{$id}",
                    "{$user->nom_complet} a soumis le compte-rendu de l'intervention"
                );
            }

            // Notifier le superviseur client de la base concernée
            if ($depannage->equipement && $depannage->equipement->site && $depannage->equipement->site->base_id) {
                $superviseur = User::where('type_utilisateur', 'superviseur_client')
                    ->where('base_id', $depannage->equipement->site->base_id)
                    ->first();

                if ($superviseur) {
                    NotificationController::create(
                        $superviseur->id,
                        $depannage->id,
                        'compte_rendu_soumis',
                        "Compte-rendu disponible pour intervention #{$id}",
                        "{$user->nom_complet} a soumis le compte-rendu de l'intervention dans votre base"
                    );
                }
            }
        }

        return redirect()->route('depannages.show', $id)
            ->with('success', 'Compte-rendu et statut mis à jour avec succès !');
    }

    /**
     * Admin approuve une demande créée par un Superviseur
     * SANS ASSIGNATION À CETTE ÉTAPE
     */
    public function approveAdmin(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->type_utilisateur !== 'admin') {
            return redirect()->back()->with('error', 'Seul l\'Admin peut approuver cette demande.');
        }

        $depannage = Depannage::findOrFail($id);

        $request->validate([
            'message' => 'nullable|string|max:500'
        ]);

        $depannage->statut_validation_admin = 'approuvé';
        $depannage->message_admin = $request->input('message', 'Demande approuvée');
        $depannage->date_reponse_admin = now();
        $depannage->statut = 'approuvé - en attente assignation'; // NOUVEAU STATUT
        $depannage->save();

        // Notifier le superviseur
        if ($depannage->demandeur) {
            NotificationController::create(
                $depannage->demandeur_id,
                $depannage->id,
                'intervention_approuvee',
                "Votre demande #{$depannage->id} a été approuvée",
                "L'Admin a approuvé votre demande d'intervention et va assigner un technicien"
            );
        }

        // Rediriger vers la même page pour afficher l'assignation
        return redirect()->route('depannages.show', $id)
            ->with('success', 'Demande approuvée! Vous pouvez maintenant assigner un technicien.');
    }

    /**
     * Admin rejette une demande créée par un Superviseur
     */
    public function rejectAdmin(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $user = Auth::user();

        if ($user->type_utilisateur !== 'admin') {
            return redirect()->back()->with('error', 'Seul l\'Admin peut rejeter cette demande.');
        }

        $depannage = Depannage::findOrFail($id);

        $depannage->statut_validation_admin = 'rejeté';
        $depannage->message_admin = $request->input('message');
        $depannage->date_reponse_admin = now();
        $depannage->statut = 'annulé';
        $depannage->save();

        // Notifier le superviseur
        if ($depannage->demandeur) {
            NotificationController::create(
                $depannage->demandeur_id,
                $depannage->id,
                'intervention_rejetee',
                "Votre demande #{$depannage->id} a été rejetée",
                "L'Admin a rejeté votre demande: {$request->input('message')}"
            );
        }

        return redirect()->route('depannages.show', $id)
            ->with('success', 'Demande rejetée. Le superviseur a été notifié.');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * ONGLET OPÉRATIONS
     * Affiche les demandes/interventions EN ATTENTE (non encore affectées)
     * Permet d'assigner équipe/technicien + date pour lancer l'opération
     * ═══════════════════════════════════════════════════════════════════
     */
    public function operationsIndex(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // ── DEMANDES PRÊTES (validées Soutarah, en attente de création d'opération technique) ──
        $queryDemandes = \App\Models\Demande::with(['client', 'base', 'site', 'equipement'])
            ->where('statut', 'needs_technical_operation')
            ->whereNull('technical_operation_id');

        // Filtrer par périmètre superviseur Soutarah
        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $queryDemandes->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $queryDemandes->where('client_id', $assignment->client_id);
                }
            } else {
                $queryDemandes->whereRaw('1 = 0');
            }
        }

        $demandesPretes = $queryDemandes->orderBy('niveau_urgence', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // ── Interventions EN ATTENTE (non affectées) ──
        $query = Depannage::with(['equipement.site', 'demandeur', 'client'])
            ->where('statut', 'en attente')
            ->whereNull('technicien_id')
            ->whereNull('equipe_id');

        // Filtrer par périmètre superviseur Soutarah
        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $demandesEnAttente = $query->orderBy('urgence', 'desc')
            ->orderBy('date_demande', 'asc')
            ->get();

        // ── Opérations EN COURS (déjà affectées) ──
        $queryEnCours = Depannage::with(['equipe.chef', 'equipe.membres', 'technicien', 'equipement.site', 'client'])
            ->where(function ($q) {
                $q->where('statut', 'en cours')
                    ->orWhere(function ($sq) {
                        $sq->where('statut', 'en attente')
                            ->where(function ($asq) {
                                $asq->whereNotNull('technicien_id')
                                    ->orWhereNotNull('equipe_id');
                            });
                    });
            });

        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $queryEnCours->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $queryEnCours->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
        }

        // Filtres
        $statutFilter = $request->input('statut_operation');
        if ($statutFilter === 'en_cours') {
            $queryEnCours->where('statut', 'en cours');
        } elseif ($statutFilter === 'resolu') {
            $queryEnCours->where('statut', 'résolu');
        }

        $operationsEnCours = $queryEnCours->orderBy('date_prevue', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'page_op');

        // ── Opérations TERMINÉES ──
        $queryTerminees = Depannage::with(['equipe.chef', 'technicien', 'equipement.site', 'client'])
            ->whereIn('statut', ['résolu', 'resolu']);

        if ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $queryTerminees->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $queryTerminees->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
        }

        $operationsTerminees = $queryTerminees->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'page_term');

        // ── Stats ──
        $stats = [
            'pretesACreer' => $demandesPretes->count(), // NOUVEAU
            'enAttente' => $demandesEnAttente->count(),
            'urgent' => $demandesEnAttente->where('urgence', 'Urgent')->count(),
            'enCours' => Depannage::where('statut', 'en cours')->count(),
            'terminees' => Depannage::where('statut', 'résolu')->count(),
        ];

        // Récupérer équipes et techniciens pour assignation
        $equipes = \App\Models\Equipe::with('chef')->get();
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();

        return view('operations.index', compact('demandesPretes', 'demandesEnAttente', 'operationsEnCours', 'operationsTerminees', 'stats', 'statutFilter', 'equipes', 'techniciens'));
    }

    /**
     * Formulaire enrichi : Créer une Opération à partir d'une Demande
     * (avant on allait sur depannages.create-from-demande — maintenant formulaire dédié)
     */
    public function createOperationFromDemande($demandeId)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_client', 'superviseur_soutarah'])) {
            abort(403);
        }

        $demande = \App\Models\Demande::with(['site', 'equipement', 'client', 'base', 'createdBy'])->findOrFail($demandeId);

        if ($demande->statut != 'needs_technical_operation') {
            return redirect()->route('operations.index')
                ->with('error', 'Cette demande n\'est pas prête pour une opération.');
        }

        if (!$this->demandeBelongsToUserScope($user, $demande)) {
            return redirect()->route('operations.index')
                ->with('error', 'Vous n\'êtes pas autorisé à créer une opération pour cette demande.');
        }

        if ($demande->technical_operation_id) {
            return redirect()->route('depannages.show', $demande->technical_operation_id)
                ->with('info', 'Une opération existe déjà pour cette demande.');
        }

        $equipes = \App\Models\Equipe::with(['chef', 'membres'])->orderBy('nom_equipe')->get();

        return view('operations.create', compact('demande', 'equipes'));
    }

    protected function demandeBelongsToUserScope($user, $demande): bool
    {
        if ($user->type_utilisateur === 'admin') {
            return true;
        }

        if ($user->type_utilisateur === 'superviseur_client') {
            return $user->base_id && $demande->base_id === $user->base_id;
        }

        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if (!$assignment) {
                return false;
            }
            if ($assignment->base_id && $demande->base_id === $assignment->base_id) {
                return true;
            }
            if ($assignment->client_id && $demande->client_id === $assignment->client_id) {
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * Enregistrer l'Opération depuis la Demande :
     *   → crée depannage (avec equipe)
     *   → crée planification (si date)
     *   → lie la demande
     *   → notifications
     */
    public function storeOperationFromDemande(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_client', 'superviseur_soutarah'])) {
            abort(403);
        }

        $validated = $request->validate([
            'demande_id' => 'required|exists:demandes,id',
            'equipe_id' => 'required|exists:equipes,id',
            'date_prevue' => 'nullable|date',
            'ri_soutarah' => 'nullable|string|max:50',
            'commentaire_operation' => 'nullable|string|max:2000',
        ]);

        $demande = \App\Models\Demande::with(['site', 'equipement', 'client', 'base', 'createdBy'])->findOrFail($validated['demande_id']);

        if ($demande->statut != 'needs_technical_operation' || $demande->technical_operation_id) {
            return redirect()->route('operations.index')
                ->with('error', 'Demande non éligible à la création d\'opération.');
        }

        if (!$this->demandeBelongsToUserScope($user, $demande)) {
            return redirect()->route('operations.index')
                ->with('error', 'Vous n\'êtes pas autorisé à créer une opération pour cette demande.');
        }

        $equipe = \App\Models\Equipe::with(['chef', 'membres'])->findOrFail($validated['equipe_id']);

        \Illuminate\Support\Facades\DB::transaction(function () use ($demande, $equipe, $validated, $user) {
            // 1. Créer le dépannage/opération
            $urgenceMap = ['faible' => 'Faible', 'moyen' => 'Normal', 'urgent' => 'Urgent', 'critique' => 'Urgent'];
            $equipementRef = $demande->equipement
                ? ($demande->equipement->equipement_code ? $demande->equipement->equipement_code . ' - ' : '') . ($demande->equipement->equipement_nom ?? '')
                : 'Équipement non spécifié';

            $descriptionPanne = trim(($validated['commentaire_operation'] ?? '') . "\n\n--- [Description demande d'origine ---\n" . ($demande->description ?? ''));

            $riSoutarah = null;
            if (!empty($validated['ri_soutarah'])) {
                $riSoutarah = trim($validated['ri_soutarah']);
            } elseif (!empty($demande->numero_reference_externe)) {
                $riSoutarah = $demande->numero_reference_externe;
            }

            $depannage = Depannage::create([
                'type_intervention' => 'Dépannage',
                'ri_soutarah' => $riSoutarah,
                'created_by_role' => $user->type_utilisateur,
                'client_id' => $demande->client_id,
                'client_nom' => $demande->client->nom ?? null,
                'base_code' => (string)($demande->base_id ?? ''),
                'demandeur_id' => $demande->created_by_user_id,
                'demandeur_nom' => $demande->createdBy->nom_complet ?? null,
                'equipement_id' => $demande->equipement_id,
                'equipement_reference' => $equipementRef,
                'description_panne' => trim($descriptionPanne),
                'photo_panne' => $demande->photo_panne ? ('storage/' . $demande->photo_panne) : null,
                'date_debut_prevue' => optional($demande->date_debut_souhaitee)->toDateString(),
                'date_fin_prevue' => optional($demande->date_debut_souhaitee)->toDateString(),
                'urgence' => $urgenceMap[$demande->niveau_urgence] ?? 'Normal',
                'date_demande' => now()->toDateString(),
                'etat_demande' => 'Assignée',
                'statut' => 'en cours',
                'statut_operation' => 'assigned',
                'equipe_id' => $equipe->id,
                'date_prevue' => $validated['date_prevue'] ?? null,
                'demande_id' => $demande->id,
            ]);

            // 2. Lier la demande à l'opération
            $demande->update(['technical_operation_id' => $depannage->id]);

            // 3. Créer une planification si date_prevue
            $dateDebutStr = $validated['date_prevue'] ?? optional($demande->date_debut_souhaitee)->toDateString();
            $dateFinStr = optional($demande->date_debut_souhaitee)->toDateString() ?? $dateDebutStr;
            if ($dateDebutStr) {
                $dateDebut = $dateDebutStr . ' 09:00:00';
                $dateFin = ($dateFinStr ?? $dateDebutStr) . ' 17:00:00';
                \App\Models\Planification::create([
                    'client_id' => $demande->client_id,
                    'site_code' => (string)($demande->base_id ?? ''),
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                    'type_intervention' => 'Dépannage',
                    'nom_equipe' => $equipe->nom_equipe,
                    'equipe_id' => $equipe->id,
                    'commentaire' => "Demande #{$demande->numero_demande} - " . ($demande->equipement->equipement_nom ?? '') . ($validated['commentaire_operation'] ? " | " . Str::limit($validated['commentaire_operation'], 150) : ''),
                    'statut' => 'Planifié',
                    'created_by' => $user->id,
                    'created_at' => now(),
                ]);
            }

            // 4. Notifications
            // 4a. Chef d'équipe (si existe)
            if ($equipe->chef) {
                \App\Models\InterventionNotification::create([
                    'user_id' => $equipe->chef->id,
                    'intervention_id' => $depannage->id,
                    'demande_id' => $demande->id,
                    'type' => 'nouvelle_operation',
                    'message' => "🚨 Nouvelle opération #{$depannage->id} pour votre équipe {$equipe->nom_equipe} (Demande {$demande->numero_demande})" . (!empty($validated['date_prevue']) ? " - Prévue le " . date('d/m/Y', strtotime($validated['date_prevue'])) : ''),
                    'statut' => 'non_lu',
                ]);
            }
            // 4b. Tous les membres de l'équipe
            foreach ($equipe->membres as $membre) {
                \App\Models\InterventionNotification::create([
                    'user_id' => $membre->id,
                    'intervention_id' => $depannage->id,
                    'demande_id' => $demande->id,
                    'type' => 'nouvelle_operation',
                    'message' => "Nouvelle intervention pour équipe {$equipe->nom_equipe} - Demande #{$demande->numero_demande}",
                    'statut' => 'non_lu',
                ]);
            }
            // 4c. Demandeur
            \App\Models\InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => $depannage->id,
                'demande_id' => $demande->id,
                'type' => 'operation_creee',
                'message' => "✅ Votre demande #{$demande->numero_demande} est programmée" . (!empty($validated['date_prevue']) ? " pour le " . date('d/m/Y', strtotime($validated['date_prevue'])) : '') . " - Équipe : {$equipe->nom_equipe}",
                'statut' => 'non_lu',
            ]);
            // 4d. Superviseurs client de la base ou du client
            if ($demande->base_id || $demande->client_id) {
                $superviseursClient = \App\Models\User::where('type_utilisateur', 'superviseur_client')
                    ->where(function ($q) use ($demande) {
                        if ($demande->base_id) {
                            $q->where('base_id', $demande->base_id);
                        }
                        if ($demande->client_id) {
                            $q->orWhere('client_id', $demande->client_id);
                        }
                    })
                    ->get();
                foreach ($superviseursClient as $svc) {
                    \App\Models\InterventionNotification::create([
                        'user_id' => $svc->id,
                        'intervention_id' => $depannage->id,
                        'demande_id' => $demande->id,
                        'type' => 'operation_creee',
                        'message' => "Opération programmée pour demande #{$demande->numero_demande} - Équipe {$equipe->nom_equipe}" . (!empty($validated['date_prevue']) ? " le " . date('d/m/Y', strtotime($validated['date_prevue'])) : ''),
                        'statut' => 'non_lu',
                    ]);
                }
            }
        });

        return redirect()->route('operations.index')
            ->with('success', "✅ Opération créée avec succès ! Équipe {$equipe->nom_equipe} assignée à la demande #{$demande->numero_demande}.");
    }

    /**
     * Page spécifique pour les techniciens
     */
    public function technicienInterventions(Request $request)
    {
        $user = Auth::user();

        // Récupérer tous les IDs des équipes dont l'utilisateur fait partie
        $equipesIds = $user->equipes->pluck('id')->toArray();
        if ($user->equipe_id) {
            $equipesIds[] = $user->equipe_id;
        }
        $equipesIds = array_unique(array_filter($equipesIds));

        // Si on demande les maintenances
        if ($request->input('type') === 'Maintenance') {
            $query = \App\Models\Maintenance::with(['client', 'base', 'site', 'equipement', 'equipe', 'equipes', 'technicien']);

            // Filtrer les maintenances assignées au technicien (direct, equipe legacy, ou equipe many-to-many)
            $query->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    // Colonne equipe_id legacy
                    $q->orWhereIn('equipe_id', $equipesIds);
                    // Relation many-to-many via table equipe_maintenance
                    $q->orWhereHas('equipes', function ($eq) use ($equipesIds) {
                        $eq->whereIn('equipes.id', $equipesIds);
                    });
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
                    $q->orWhereHas('equipes', function ($eq) use ($equipesIds) {
                        $eq->whereIn('equipes.id', $equipesIds);
                    });
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
            $totalCount = $maintenancesCount;

            return view('depannages.technicien', compact('maintenances', 'totalCount', 'installationsCount', 'depannagesCount', 'maintenancesCount'));
        }

        // Sinon, logique normale pour Installations et Dépannages
        $query = Depannage::with(['technicien', 'equipe', 'demandeur', 'equipement.baseSite', 'client']);

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
        $totalCount = (clone $query)->count();

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
                $q->orWhereHas('equipes', function ($eq) use ($equipesIds) {
                    $eq->whereIn('equipes.id', $equipesIds);
                });
                $q->orWhereIn('equipe_id', $equipesIds);
            }
        });
        $maintenancesCount = $baseMaintenanceQuery->count();

        return view('depannages.technicien', compact('depannages', 'totalCount', 'installationsCount', 'depannagesCount', 'maintenancesCount'));
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * FEATURE 3: CONFIRMATION DE RÉCEPTION PAR LE TECHNICIEN
     * ═══════════════════════════════════════════════════════════════════
     */
    public function confirmReception($id)
    {
        $user = Auth::user();
        $depannage = Depannage::with(['equipe.membres', 'equipe.chef'])->findOrFail($id);

        // Vérifier que l'utilisateur est le technicien assigné ou membre de l'équipe
        $autorise = false;
        if (in_array($user->type_utilisateur, ['technicien', 'chef technicien'])) {
            if ($depannage->technicien_id == $user->id) {
                $autorise = true;
            }
            if ($depannage->equipe_id && $depannage->equipe) {
                if ($depannage->equipe->chef_equipe == $user->id) {
                    $autorise = true;
                }
                if ($depannage->equipe->membres->contains('id', $user->id)) {
                    $autorise = true;
                }
            }
        }

        if (!$autorise) {
            return redirect()->route('depannages.show', $id)
                ->with('error', 'Vous n\'êtes pas autorisé à confirmer cette intervention.');
        }

        // Marquer comme confirmé (l'ENUM est maintenant correct)
        $depannage->update([
            'confirmation_reception' => 'confirmé',
            'date_confirmation_reception' => now(),
        ]);

        // Notifier l'admin et le superviseur
        $admin = User::where('type_utilisateur', 'admin')->first();
        if ($admin) {
            NotificationController::create(
                $admin->id,
                $depannage->id,
                'confirmation_technicien',
                "Intervention #{$id} confirmée par le technicien",
                "{$user->nom_complet} a confirmé la réception et la prise en charge de l'intervention"
            );
        }

        if ($depannage->equipement && $depannage->equipement->site && $depannage->equipement->site->base_id) {
            $superviseur = User::where('type_utilisateur', 'superviseur_client')
                ->where('base_id', $depannage->equipement->site->base_id)
                ->first();

            if ($superviseur) {
                NotificationController::create(
                    $superviseur->id,
                    $depannage->id,
                    'confirmation_technicien',
                    "Intervention #{$id} confirmée",
                    "{$user->nom_complet} a confirmé la réception de l'intervention"
                );
            }
        }

        return redirect()->route('depannages.show', $id)
            ->with('success', 'Réception de la tâche confirmée avec succès !');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * FEATURE 4: PAGE DÉDIÉE POUR LES COMPTES-RENDUS
     * ═══════════════════════════════════════════════════════════════════
     */
    public function rapportsIndex(Request $request)
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // Query de base: interventions avec rapport uniquement
        $query = Depannage::with(['technicien', 'equipe', 'demandeur', 'equipement.site', 'client', 'rapportSoumisPar'])
            ->where('statut', 'résolu')
            ->whereNotNull('rapport')
            ->where('rapport', '!=', '');

        // ══════════════════════════════════════════════════════════════════
        // FILTRAGE PAR RÔLE
        // ══════════════════════════════════════════════════════════════════

        // Superviseur Client : uniquement les rapports de SA base ou de son client
        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $query->whereHas('equipement.site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $query->whereHas('equipement', function ($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            } else {
                $query->whereRaw('1 = 0'); // Aucun rapport
            }
        }

        // Superviseur Soutarah : uniquement les rapports de SA base/company assignée
        elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0'); // Aucun rapport
            }
        }

        // Technicien : uniquement SES rapports ou ceux de SES équipes
        elseif (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            $query->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });
        }

        // Legacy superviseur : filtrer par base
        elseif ($role === 'superviseur' && $user->base_id) {
            $query->whereHas('equipement.site', function ($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        }

        // Admin : voit tout (pas de filtre)

        // Filtres dynamiques
        if ($type = $request->input('type')) {
            // Si le type est "Maintenance", on récupère les maintenances au lieu des interventions
            if ($type === 'Maintenance') {
                // On ne filtre PAS la query actuelle, on la laisse vide
                $query->whereRaw('1 = 0'); // Aucune intervention
            } else {
                $query->where('type_intervention', $type);
            }
        }

        if ($dateDebut = $request->input('date_debut')) {
            $query->where('date_realisation', '>=', $dateDebut);
        }

        if ($dateFin = $request->input('date_fin')) {
            $query->where('date_realisation', '<=', $dateFin);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('equipement_reference', 'like', "%{$search}%")
                    ->orWhere('description_panne', 'like', "%{$search}%")
                    ->orWhere('rapport', 'like', "%{$search}%");
            });
        }

        // ══════════════════════════════════════════════════════════════════
        // RÉCUPÉRATION DES MAINTENANCES SI TYPE = MAINTENANCE
        // ══════════════════════════════════════════════════════════════════
        if ($request->input('type') === 'Maintenance') {
            // Query pour les maintenances
            $maintenancesQuery = \App\Models\Maintenance::with(['technicien', 'equipe', 'client', 'site', 'equipement'])
                ->where('statut', 'terminée')
                ->whereNotNull('rapport_technicien')
                ->where('rapport_technicien', '!=', '');

            // Appliquer les mêmes filtres de rôle
            if ($role === 'superviseur_client') {
                if ($user->base_id) {
                    $maintenancesQuery->whereHas('site', function ($q) use ($user) {
                        $q->where('base_id', $user->base_id);
                    });
                } elseif ($user->client_id) {
                    $maintenancesQuery->where('client_id', $user->client_id);
                }
            } elseif ($role === 'superviseur_soutarah') {
                $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
                if ($assignment) {
                    if ($assignment->base_id) {
                        $maintenancesQuery->whereHas('site', function ($q) use ($assignment) {
                            $q->where('base_id', $assignment->base_id);
                        });
                    } elseif ($assignment->client_id) {
                        $maintenancesQuery->where('client_id', $assignment->client_id);
                    }
                }
            } elseif (in_array($role, ['technicien', 'chef technicien'])) {
                $equipesIds = $user->equipes->pluck('id')->toArray();
                $maintenancesQuery->where(function ($q) use ($user, $equipesIds) {
                    $q->where('technicien_id', $user->id);
                    if (!empty($equipesIds)) {
                        $q->orWhereIn('equipe_id', $equipesIds);
                    }
                });
            } elseif ($role === 'superviseur' && $user->base_id) {
                $maintenancesQuery->whereHas('site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            }

            // Filtres de date
            if ($dateDebut = $request->input('date_debut')) {
                $maintenancesQuery->where('date_fin_reelle', '>=', $dateDebut);
            }
            if ($dateFin = $request->input('date_fin')) {
                $maintenancesQuery->where('date_fin_reelle', '<=', $dateFin);
            }

            // Filtre de recherche
            if ($search = $request->input('search')) {
                $maintenancesQuery->where(function ($q) use ($search) {
                    $q->where('numero_maintenance', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('rapport_technicien', 'like', "%{$search}%");
                });
            }

            $rapports = $maintenancesQuery->orderBy('date_fin_reelle', 'desc')->paginate(20);
        } else {
            $rapports = $query->orderBy('date_realisation', 'desc')->paginate(20);
        }

        // Compteurs par type selon le rôle
        $baseQuery = Depannage::query()
            ->where('statut', 'résolu')
            ->whereNotNull('rapport')
            ->where('rapport', '!=', '');

        // Appliquer les mêmes filtres de rôle pour les compteurs
        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $baseQuery->whereHas('equipement.site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $baseQuery->whereHas('equipement', function ($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                });
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $baseQuery->whereHas('equipement.site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $baseQuery->whereHas('equipement.site.baseSite', function ($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            }
        } elseif (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            $baseQuery->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });
        } elseif ($role === 'superviseur' && $user->base_id) {
            $baseQuery->whereHas('equipement.site', function ($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        }

        $installationsCount = (clone $baseQuery)->where('type_intervention', 'Installation')->count();
        $depannagesCount    = (clone $baseQuery)->where('type_intervention', 'Dépannage')->count();

        // Compteur des maintenances terminées avec rapport
        $maintenancesQuery = \App\Models\Maintenance::query()
            ->where('statut', 'terminée')
            ->whereNotNull('rapport_technicien')
            ->where('rapport_technicien', '!=', '');

        // Appliquer les mêmes filtres de rôle pour les maintenances
        if ($role === 'superviseur_client') {
            if ($user->base_id) {
                $maintenancesQuery->whereHas('site', function ($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $maintenancesQuery->where('client_id', $user->client_id);
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $maintenancesQuery->whereHas('site', function ($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $maintenancesQuery->where('client_id', $assignment->client_id);
                }
            }
        } elseif (in_array($role, ['technicien', 'chef technicien'])) {
            $equipesIds = $user->equipes->pluck('id')->toArray();
            $maintenancesQuery->where(function ($q) use ($user, $equipesIds) {
                $q->where('technicien_id', $user->id);
                if (!empty($equipesIds)) {
                    $q->orWhereIn('equipe_id', $equipesIds);
                }
            });
        } elseif ($role === 'superviseur' && $user->base_id) {
            $maintenancesQuery->whereHas('site', function ($q) use ($user) {
                $q->where('base_id', $user->base_id);
            });
        }

        $maintenancesCount = $maintenancesQuery->count();

        // Total = Interventions + Maintenances
        $totalCount = $installationsCount + $depannagesCount + $maintenancesCount;

        $isMaintenanceView = $request->input('type') === 'Maintenance';

        return view('comptes-rendus.index', compact('rapports', 'totalCount', 'installationsCount', 'depannagesCount', 'maintenancesCount', 'isMaintenanceView'));
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * WORKFLOW DE CLÔTURE - Technicien soumet son rapport
     * ═══════════════════════════════════════════════════════════════════
     */
    public function soumettreRapport(Request $request, $id)
    {
        $user = Auth::user();
        $depannage = Depannage::with(['equipe', 'demande'])->findOrFail($id);

        // Vérifier que l'utilisateur est bien le technicien assigné ou membre de l'équipe
        $autorise = false;
        if ($depannage->technicien_id == $user->id) {
            $autorise = true;
        }
        if ($depannage->equipe_id && $depannage->equipe) {
            if ($depannage->equipe->chef_equipe == $user->id || $depannage->equipe->membres->contains('id', $user->id)) {
                $autorise = true;
            }
        }

        if (!$autorise) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à soumettre le rapport pour cette intervention.');
        }

        $validated = $request->validate([
            'photo_carnet_rapport' => 'required|image|max:10240',
            'photo_equipement_apres' => 'required|image|max:10240',
            'rapport_texte' => 'nullable|string',
        ]);

        // Upload photo carnet
        if ($request->hasFile('photo_carnet_rapport')) {
            $photo = $request->file('photo_carnet_rapport');
            $photoName = 'carnet_' . time() . '_' . rand(100, 999) . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/rapports'), $photoName);
            $validated['photo_carnet_rapport'] = '/uploads/rapports/' . $photoName;
        }

        // Upload photo équipement
        if ($request->hasFile('photo_equipement_apres')) {
            $photo = $request->file('photo_equipement_apres');
            $photoName = 'equipement_' . time() . '_' . rand(100, 999) . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/rapports'), $photoName);
            $validated['photo_equipement_apres'] = '/uploads/rapports/' . $photoName;
        }

        // Mettre à jour le dépannage
        $depannage->update([
            'photo_carnet_rapport' => $validated['photo_carnet_rapport'],
            'photo_equipement_apres' => $validated['photo_equipement_apres'],
            'rapport' => $request->rapport_texte,
            'date_rapport_technicien' => now(),
            'rapport_soumis_par_user_id' => $user->id,
            'statut_rapport_technicien' => 'soumis',
            'statut' => 'résolu', // Intervention terminée côté technicien
        ]);

        // Notifier le superviseur Soutarah et l'admin
        $superviseurSoutarah = null;
        if ($depannage->equipement && $depannage->equipement->site) {
            $assignment = \App\Models\Assignment::whereHas('superviseur', function ($q) use ($depannage) {
                // Trouver le superviseur Soutarah qui gère cette base
            })->first();

            if ($assignment && $assignment->superviseur) {
                $superviseurSoutarah = $assignment->superviseur;
            }
        }

        // Notifier admin
        $admin = User::where('type_utilisateur', 'admin')->first();
        if ($admin) {
            NotificationController::create(
                $admin->id,
                $depannage->id,
                'rapport_technicien_soumis',
                "Rapport technicien soumis pour intervention #{$id}",
                "{$user->nom_complet} a soumis le rapport de l'intervention avec photos"
            );
        }

        // Notifier superviseur Soutarah si trouvé
        if ($superviseurSoutarah) {
            NotificationController::create(
                $superviseurSoutarah->id,
                $depannage->id,
                'rapport_technicien_soumis',
                "Rapport technicien soumis pour intervention #{$id}",
                "{$user->nom_complet} a soumis le rapport. Vous pouvez le transmettre au superviseur client."
            );
        }

        return redirect()->route('depannages.show', $id)
            ->with('success', 'Rapport soumis avec succès! Les photos ont été enregistrées.');
    }

    /**
     * Superviseur Soutarah/Admin transmet le rapport au superviseur client
     */
    public function transmettreRapportClient(Request $request, $id)
    {
        $user = Auth::user();
        $depannage = Depannage::with(['demande', 'equipement.site'])->findOrFail($id);

        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah'])) {
            abort(403);
        }

        if ($depannage->statut_rapport_technicien != 'soumis') {
            return redirect()->back()->with('error', 'Le rapport du technicien n\'a pas encore été soumis.');
        }

        // Mettre à jour le statut
        DB::table('depannages')->where('id', $id)->update([
            'statut_rapport_technicien'        => 'transmis_client',
            'statut_validation_finale_client' => 'en_attente',
            'date_transmission_rapport_client' => now(),
            'rapport_transmis_par_user_id'     => $user->id,
        ]);

        // Notifier le superviseur client
        $superviseurClient = null;

        if ($depannage->equipement && $depannage->equipement->site) {
            $site = $depannage->equipement->site;

            // Structure 1 ou 3 : Site avec base_id
            if ($site->base_id) {
                $superviseurClient = User::where('type_utilisateur', 'superviseur_client')
                    ->where('base_id', $site->base_id)
                    ->first();
            }
            // Structure 3 : Site rattaché directement au client (sans base)
            elseif ($site->client_id) {
                $superviseurClient = User::where('type_utilisateur', 'superviseur_client')
                    ->where('client_id', $site->client_id)
                    ->first();
            }
        }
        // Structure 2 : Équipement rattaché directement au client (sans site)
        elseif ($depannage->equipement && $depannage->equipement->client_id) {
            $superviseurClient = User::where('type_utilisateur', 'superviseur_client')
                ->where('client_id', $depannage->equipement->client_id)
                ->first();
        }

        if ($superviseurClient) {
            NotificationController::create(
                $superviseurClient->id,
                $depannage->id,
                'rapport_technicien_disponible',
                "Rapport technicien disponible pour intervention #{$id}",
                "Le rapport du technicien avec photos est maintenant disponible. Comparez-le avec la confirmation du demandeur pour clôturer l'intervention."
            );
        }

        return redirect()->route('depannages.show', $id)
            ->with('success', 'Rapport transmis au superviseur client avec succès!');
    }

    /**
     * Superviseur Soutarah/Admin rejette le rapport du technicien (pour correction)
     */
    public function rejeterRapportTechnicien(Request $request, $id)
    {
        $user = Auth::user();
        $depannage = Depannage::findOrFail($id);

        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah'])) {
            abort(403);
        }

        $validated = $request->validate([
            'raison_rejet' => 'required|string|min:5|max:1000',
        ]);

        // Mise à jour de l'enregistrement via DB pour éviter tout problème d'enum / fillable
        \Illuminate\Support\Facades\DB::table('depannages')
            ->where('id', $id)
            ->update([
                'statut_rapport_technicien' => 'rejete',
                'statut'                    => 'en cours',
                'raison_rejet_rapport'      => $validated['raison_rejet'],
                'date_rejet_rapport'        => now(),
                'rapport_rejete_par_user_id' => $user->id,
            ]);

        // Notifier le technicien (non bloquant)
        try {
            $targetUserId = $depannage->technicien_id ?? $depannage->rapport_soumis_par_user_id;
            if ($targetUserId) {
                InterventionNotification::create([
                    'user_id'         => $targetUserId,
                    'intervention_id' => $depannage->id,
                    'demande_id'      => $depannage->demande_id ?: null,
                    'type'            => 'rapport_rejete',
                    'message'         => "Votre rapport pour l'intervention #{$id} a été rejeté. Motif : {$validated['raison_rejet']}. Merci de le corriger et de le resoumettre.",
                    'statut'          => 'non_lu',
                ]);
            }
        } catch (\Throwable $e) {
            // La notification n'est pas bloquante
        }

        return redirect()->route('depannages.show', $id)
            ->with('warning', 'Le rapport du technicien a été rejeté avec succès. Le technicien a été notifié pour correction.');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * CLÔTURE FINALE : Superviseur Client valide le rapport et clôture
     * Met à jour le statut de l'intervention et de la demande liée
     * ═══════════════════════════════════════════════════════════════════
     */
    public function cloturerIntervention(Request $request, $id)
    {
        $user = Auth::user();
        $depannage = Depannage::with(['demande', 'equipement.site'])->findOrFail($id);

        // Vérifier que c'est bien le Superviseur Client
        if ($user->type_utilisateur !== 'superviseur_client') {
            abort(403, 'Seul le Superviseur Client peut clôturer l\'intervention.');
        }

        // Vérifier que le rapport a bien été transmis
        if ($depannage->statut_rapport_technicien !== 'transmis_client') {
            return redirect()->back()->with('error', 'Le rapport n\'a pas encore été transmis par le Superviseur Soutarah.');
        }

        // Vérifier que le superviseur client a bien accès à cette intervention
        $hasAccess = false;
        if ($depannage->equipement && $depannage->equipement->site) {
            $site = $depannage->equipement->site;
            if ($site->base_id && $user->base_id == $site->base_id) {
                $hasAccess = true;
            } elseif ($site->client_id && $user->client_id == $site->client_id) {
                $hasAccess = true;
            }
        } elseif ($depannage->equipement && $depannage->equipement->client_id == $user->client_id) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return redirect()->back()->with('error', 'Cette intervention n\'appartient pas à votre périmètre.');
        }

        // Vérifier que ni l'intervention ni la demande liée ne sont déjà clôturées
        if (in_array($depannage->statut_validation_finale_client, ['validé', 'conforme'])) {
            return redirect()->back()->with('error', 'Cette intervention est déjà clôturée.');
        }
        if ($depannage->demande && $depannage->demande->statut === 'closed') {
            return redirect()->back()->with('error', 'La demande liée est déjà clôturée.');
        }

        $validated = $request->validate([
            'commentaire_validation' => 'nullable|string|max:500',
        ]);

        // Mettre à jour l'intervention via DB::table
        DB::table('depannages')->where('id', $id)->update([
            'statut'                          => 'résolu',
            'statut_validation_finale_client' => 'validé',
            'commentaire_validation_client'   => $validated['commentaire_validation'] ?? null,
            'date_validation_finale_client'   => now(),
            'validated_by_client_user_id'     => $user->id,
        ]);

        // Si une demande est liée, la clôturer aussi
        if ($depannage->demande_id) {
            DB::table('demandes')->where('id', $depannage->demande_id)->update([
                'statut'                          => 'closed',
                'date_cloture_finale'             => now(),
                'statut_validation_finale_client' => 'validé',
                'cloture_par_user_id'             => $user->id,
            ]);
        }

        // Notifier l'admin et le superviseur Soutarah (non bloquant)
        try {
            $admin = User::where('type_utilisateur', 'admin')->first();
            if ($admin) {
                NotificationController::create(
                    $admin->id,
                    $depannage->id,
                    'intervention_cloturee',
                    "Intervention #{$id} clôturée par le client",
                    "Le Superviseur Client {$user->nom_complet} a validé et clôturé l'intervention"
                );
            }

            // Trouver le superviseur Soutarah
            if ($depannage->equipement && $depannage->equipement->site) {
                $assignment = null;
                if ($depannage->equipement->site->base_id) {
                    $assignment = \App\Models\Assignment::with('superviseur')
                        ->where('base_id', $depannage->equipement->site->base_id)
                        ->first();
                } elseif ($depannage->equipement->site->baseSite && $depannage->equipement->site->baseSite->client_id) {
                    $assignment = \App\Models\Assignment::with('superviseur')
                        ->where('client_id', $depannage->equipement->site->baseSite->client_id)
                        ->first();
                }

                if ($assignment && $assignment->superviseur) {
                    NotificationController::create(
                        $assignment->superviseur->id,
                        $depannage->id,
                        'intervention_cloturee',
                        "Intervention #{$id} validée et clôturée",
                        "Le client a validé le rapport et clôturé l'intervention avec succès"
                    );
                }
            }
        } catch (\Throwable $e) {
            // Non bloquant
        }

        return redirect()->route('depannages.show', $id)
            ->with('success', 'Intervention validée et clôturée avec succès ! La demande liée a également été fermée.');
    }

    /**
     * Superviseur Client refuse / rejette le rapport d'intervention
     */
    public function rejeterRapportClient(Request $request, $id)
    {
        $user = Auth::user();
        $depannage = Depannage::with(['demande', 'equipement.site'])->findOrFail($id);

        if (!in_array($user->type_utilisateur, ['superviseur_client', 'admin'])) {
            return redirect()->back()->with('error', 'Seul le Superviseur Client ou l\'Admin peut refuser le rapport.');
        }

        $validated = $request->validate([
            'raison_rejet' => 'required|string|min:5|max:1000',
        ], [
            'raison_rejet.required' => 'Le motif du refus est obligatoire.',
            'raison_rejet.min'      => 'Le motif doit contenir au moins 5 caractères.',
        ]);

        // Mise à jour en base de données : Le rapport revient chez le Superviseur Soutarah
        DB::table('depannages')->where('id', $id)->update([
            'statut_rapport_technicien'       => 'soumis',
            'statut'                          => 'en cours',
            'statut_validation_finale_client' => 'non_conforme',
            'raison_rejet_rapport'            => '[Refus par Superviseur Client] ' . $validated['raison_rejet'],
            'date_rejet_rapport'              => now(),
            'rapport_rejete_par_user_id'      => $user->id,
            'commentaire_validation_client'   => 'Rapport refusé : ' . $validated['raison_rejet'],
            'date_validation_finale_client'   => now(),
            'validated_by_client_user_id'     => $user->id,
        ]);

        // Notifications (non bloquantes)
        try {
            // 1. Notifier le superviseur Soutarah
            if ($depannage->equipement && $depannage->equipement->site) {
                $assignment = null;
                if ($depannage->equipement->site->base_id) {
                    $assignment = \App\Models\Assignment::with('superviseur')
                        ->where('base_id', $depannage->equipement->site->base_id)
                        ->first();
                }
                if ($assignment && $assignment->superviseur) {
                    NotificationController::create(
                        $assignment->superviseur->id,
                        $depannage->id,
                        'rapport_rejete_client',
                        "Rapport refusé par le client - Intervention #{$id}",
                        "Le client {$user->nom_complet} a refusé le rapport. Motif : {$validated['raison_rejet']}. Veuillez examiner et rejeter au technicien si nécessaire."
                    );
                }
            }

            // 2. Notifier l'admin
            $admin = User::where('type_utilisateur', 'admin')->first();
            if ($admin) {
                NotificationController::create(
                    $admin->id,
                    $depannage->id,
                    'rapport_rejete_client',
                    "Rapport refusé par le client - Intervention #{$id}",
                    "Le client {$user->nom_complet} a refusé le rapport. Motif : {$validated['raison_rejet']}"
                );
            }
        } catch (\Throwable $e) {
            // Non bloquant
        }

        return redirect()->route('depannages.show', $id)
            ->with('warning', 'Le rapport a été refusé et transmis au Superviseur Soutarah pour traitement.');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * NOUVELLE PAGE: Afficher le formulaire de rapport complet unifié
     * (Compte-rendu + Photos en une seule page)
     * ═══════════════════════════════════════════════════════════════════
     */
    public function showRapportForm($id)
    {
        $user = Auth::user();
        $depannage = Depannage::with(['equipe', 'technicien', 'equipement'])->findOrFail($id);

        // Autoriser admin, soutarah et tous les techniciens
        if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah', 'technicien', 'chef technicien'])) {
            return redirect()->route('depannages.show', $id)
                ->with('error', 'Seul un technicien ou le personnel Soutarah peut accéder au rapport.');
        }

        // Bloquer l'accès au formulaire uniquement si l'intervention est complètement résolue ET transmise/validée
        $estCompletementResolu = in_array($depannage->statut, ['resolu', 'résolu']);
        if ($estCompletementResolu && in_array($depannage->statut_rapport_technicien, ['soumis', 'transmis_client']) && $depannage->statut_rapport_technicien !== 'rejete') {
            return redirect()->route('depannages.show', $id)
                ->with('info', 'Le rapport final a déjà été soumis et validé pour cette intervention.');
        }

        return view('depannages.rapport', compact('depannage'));
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * MÉTHODE UNIFIÉE: Soumettre le rapport complet (Texte + Photos base64)
     * Les images sont compressées côté client et envoyées en base64
     * Contourne le WAF Hostinger qui bloque les gros fichiers multipart
     * ═══════════════════════════════════════════════════════════════════
     */
    public function soumettreRapportComplet(Request $request, $id)
    {
        $stepLog = function ($msg, $ctx = []) {
            try {
                \Illuminate\Support\Facades\Log::error('[soumettreRapportComplet#' . $id . '] ' . $msg, $ctx);
            } catch (\Throwable $e) {
            }
        };
        $safeRedirectBack = function ($flashType, $flashMsg, $input = true) use ($id, $stepLog) {
            try {
                $resp = $input ? redirect()->back()->withInput() : redirect()->back();
                return $resp->with($flashType, $flashMsg);
            } catch (\Throwable $eRedirect) {
                $stepLog('redirect back failed: ' . $eRedirect->getMessage());
                $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Erreur Intervention #' . (int)$id . '</title>'
                    . '<meta name="viewport" content="width=device-width,initial-scale=1">'
                    . '<style>body{font-family:system-ui,Arial,sans-serif;max-width:640px;margin:40px auto;padding:0 16px;line-height:1.5}'
                    . '.box{padding:18px;border-radius:12px}.err{background:#fee2e2;border:2px solid #dc2626;color:#7f1d1d}'
                    . '.ok{background:#d1fae5;border:2px solid #059669;color:#064e3b}'
                    . 'a{color:#1d4ed8;font-weight:700}</style></head><body>'
                    . '<div class="box ' . ($flashType === 'success' ? 'ok' : 'err') . '">'
                    . '<strong>' . ($flashType === 'success' ? '✅ ' : '❌ ERREUR ') . 'Intervention #' . (int)$id . '</strong><br><br>'
                    . htmlspecialchars($flashMsg, ENT_QUOTES, 'UTF-8')
                    . '</div><br><a href="' . htmlspecialchars(route('depannages.show', $id), ENT_QUOTES) . '">'
                    . '← Retour à l\'intervention</a></body></html>';
                header('Content-Type: text/html; charset=utf-8');
                die($html);
            }
        };

        try {
            $stepLog('0_debut');
            $user = Auth::user();
            if (!$user) {
                return $safeRedirectBack('error', 'Session expirée. Reconnectez-vous.');
            }
            $depannage = Depannage::with(['equipe', 'equipement.site'])->find($id);
            if (!$depannage) {
                return $safeRedirectBack('error', 'Intervention #' . $id . ' introuvable.');
            }

            if (!in_array($user->type_utilisateur, ['admin', 'superviseur_soutarah', 'technicien', 'chef technicien'])) {
                return redirect()->route('depannages.show', $id)
                    ->with('error', 'Seul un technicien ou le personnel Soutarah peut soumettre le rapport.');
            }
            $stepLog('1_auth_ok', ['user_id' => $user->id, 'role' => $user->type_utilisateur]);

            try {
                $request->validate([
                    'ri_soutarah'             => 'required|string|max:50',
                    'statut'                  => 'required|string|in:resolu,en_attente_piece,partiellement_resolu,non_resolu',
                    'details_statut_terrain'  => 'nullable|string|max:1000',
                    'rapport_technicien'      => 'nullable|string|max:2000',
                    'photo_carnet_b64'        => 'required|string',
                    'photo_equipement_b64'    => 'required|string',
                ], [
                    'ri_soutarah.required'              => 'Le RI Soutarah est obligatoire.',
                    'ri_soutarah.max'                   => 'Le RI Soutarah ne peut pas dépasser 50 caractères.',
                    'statut.required'                   => 'Veuillez sélectionner le statut de l\'intervention.',
                    'statut.in'                         => 'Statut d\'intervention invalide.',
                    'photo_carnet_b64.required'         => 'La photo du rapport d\'intervention est obligatoire.',
                    'photo_equipement_b64.required'     => 'La photo de l\'équipement est obligatoire.',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ve) {
                $errors = $ve->validator->errors()->all();
                $stepLog('2_validation_ko', ['errors' => $errors]);
                return redirect()->back()->withErrors($ve->validator)->withInput();
            }
            $stepLog('2_validation_ok', ['statut' => $request->statut]);

            if ($request->statut === 'resolu') {
                $rapport = trim((string)$request->rapport_technicien);
                if (mb_strlen($rapport) < 20) {
                    return $safeRedirectBack('error', 'Le rapport des travaux doit contenir au moins 20 caractères.');
                }
            } else {
                $details = trim((string)$request->details_statut_terrain);
                if (mb_strlen($details) < 5) {
                    return $safeRedirectBack('error', 'Veuillez préciser les détails du statut sélectionné (au moins 5 caractères).');
                }
            }

            $uploadPath = public_path('uploads/rapports');
            if (!file_exists($uploadPath)) {
                if (!@mkdir($uploadPath, 0775, true)) {
                    $alt = storage_path('app/public/uploads/rapports');
                    if (!file_exists($alt) && !@mkdir($alt, 0775, true)) {
                        $stepLog('3_mkdir_fail', ['path' => $uploadPath, 'alt' => $alt]);
                        return $safeRedirectBack('error', 'Impossible de créer le dossier uploads (droits du serveur).');
                    }
                    $uploadPath = $alt;
                }
            }
            if (!file_exists($uploadPath) || !is_writable($uploadPath)) {
                $stepLog('3_not_writable', ['path' => $uploadPath]);
                return $safeRedirectBack('error', 'Dossier uploads non accessible en écriture : ' . basename($uploadPath));
            }
            $stepLog('3_upload_path', ['path' => $uploadPath]);

            $photoCarnetPath = null;
            $carnetName = null;
            try {
                $b64Carnet = $request->input('photo_carnet_b64');
                if (empty($b64Carnet) || strlen($b64Carnet) < 50) {
                    throw new \Exception('Photo carnet vide ou invalide.');
                }
                if (strpos($b64Carnet, ',') !== false) {
                    $b64Carnet = explode(',', $b64Carnet, 2)[1];
                }
                $b64Carnet = str_replace(' ', '+', $b64Carnet);
                $b64Carnet = preg_replace('#[^A-Za-z0-9+/=]#', '', $b64Carnet);
                $carnetData = base64_decode($b64Carnet, true);
                if (!$carnetData || strlen($carnetData) < 50) {
                    throw new \Exception('Le décodage base64 de la photo carnet a échoué.');
                }
                $finfo = @finfo_open();
                if ($finfo && @finfo_buffer($finfo, $carnetData, FILEINFO_MIME_TYPE) !== 'image/jpeg') {
                    $img = @imagecreatefromstring($carnetData);
                    if (!$img) throw new \Exception('Photo carnet : données JPEG invalides.');
                    @imagedestroy($img);
                }
                $carnetName = 'carnet_' . $id . '_' . time() . '_' . rand(100, 999) . '.jpg';
                $full1 = $uploadPath . '/' . $carnetName;
                $r1 = @file_put_contents($full1, $carnetData);
                if (!$r1 || !file_exists($full1) || filesize($full1) < 50) {
                    @unlink($full1);
                    throw new \Exception('Écriture fichier photo carnet impossible.');
                }
                @chmod($full1, 0644);
                if (strpos($uploadPath, public_path()) === 0) {
                    $photoCarnetPath = str_replace('\\', '/', substr($uploadPath, strlen(public_path()))) . '/' . $carnetName;
                } else {
                    $photoCarnetPath = '/storage/uploads/rapports/' . $carnetName;
                }
            } catch (\Throwable $e) {
                $stepLog('4_photo_carnet_ko', ['err' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                return $safeRedirectBack('error', 'Erreur photo carnet : ' . $e->getMessage());
            }
            $stepLog('4_photo_carnet_ok', ['path' => $photoCarnetPath, 'kb' => round(strlen($carnetData ?? '') / 1024)]);

            $photoEquipementPath = null;
            $equipName = null;
            try {
                $b64Equip = $request->input('photo_equipement_b64');
                if (empty($b64Equip) || strlen($b64Equip) < 50) {
                    throw new \Exception('Photo équipement vide ou invalide.');
                }
                if (strpos($b64Equip, ',') !== false) {
                    $b64Equip = explode(',', $b64Equip, 2)[1];
                }
                $b64Equip = str_replace(' ', '+', $b64Equip);
                $b64Equip = preg_replace('#[^A-Za-z0-9+/=]#', '', $b64Equip);
                $equipData = base64_decode($b64Equip, true);
                if (!$equipData || strlen($equipData) < 50) {
                    throw new \Exception('Le décodage base64 de la photo équipement a échoué.');
                }
                $equipName = 'equipement_' . $id . '_' . time() . '_' . rand(100, 999) . '.jpg';
                $full2 = $uploadPath . '/' . $equipName;
                $r2 = @file_put_contents($full2, $equipData);
                if (!$r2 || !file_exists($full2) || filesize($full2) < 50) {
                    @unlink($full2);
                    throw new \Exception('Écriture fichier photo équipement impossible.');
                }
                @chmod($full2, 0644);
                if (strpos($uploadPath, public_path()) === 0) {
                    $photoEquipementPath = str_replace('\\', '/', substr($uploadPath, strlen(public_path()))) . '/' . $equipName;
                } else {
                    $photoEquipementPath = '/storage/uploads/rapports/' . $equipName;
                }
            } catch (\Throwable $e) {
                @unlink($uploadPath . '/' . ($carnetName ?? ''));
                $stepLog('5_photo_equip_ko', ['err' => $e->getMessage()]);
                return $safeRedirectBack('error', 'Erreur photo équipement : ' . $e->getMessage());
            }
            $stepLog('5_photo_equip_ok', ['path' => $photoEquipementPath, 'kb' => round(strlen($equipData ?? '') / 1024)]);

            $rapportContent = !empty($request->rapport_technicien)
                ? $request->rapport_technicien
                : $request->details_statut_terrain;

            try {
                $updateData = [
                    'statut'                     => $request->statut,
                    'rapport'                    => $rapportContent,
                    'photo_carnet_rapport'       => $photoCarnetPath,
                    'photo_equipement_apres'     => $photoEquipementPath,
                    'date_rapport_technicien'    => now(),
                    'rapport_soumis_par_user_id' => $user->id,
                    'statut_rapport_technicien'  => 'soumis',
                    'raison_rejet_rapport'       => null,
                    'date_rejet_rapport'         => null,
                    'rapport_rejete_par_user_id' => null,
                ];
                if ($request->statut === 'resolu') {
                    $updateData['date_realisation'] = now()->toDateString();
                }

                $hasDetailsCol = false;
                try {
                    $sqlDetCheck = "SELECT COUNT(*) AS nb FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE TABLE_SCHEMA = DATABASE()
                                      AND TABLE_NAME   = 'depannages'
                                      AND COLUMN_NAME  = 'details_statut_terrain'";
                    $chkDet = \Illuminate\Support\Facades\DB::selectOne($sqlDetCheck);
                    $hasDetailsCol = $chkDet && ($chkDet->nb ?? 0) > 0;
                } catch (\Throwable $eCol) {
                    $stepLog('6_col_check_silent_fail', ['err' => $eCol->getMessage()]);
                    $hasDetailsCol = false;
                }
                if ($hasDetailsCol) {
                    $updateData['details_statut_terrain'] = $request->details_statut_terrain;
                }

                $hasRiCol = false;
                try {
                    $sqlRiChk = "SELECT COUNT(*) AS nb FROM INFORMATION_SCHEMA.COLUMNS
                                 WHERE TABLE_SCHEMA = DATABASE()
                                   AND TABLE_NAME   = 'depannages'
                                   AND COLUMN_NAME  = 'ri_soutarah'";
                    $chkRi = \Illuminate\Support\Facades\DB::selectOne($sqlRiChk);
                    $hasRiCol = $chkRi && ($chkRi->nb ?? 0) > 0;
                } catch (\Throwable $eRiCol) {
                    $stepLog('6_col_ri_check_silent_fail', ['err' => $eRiCol->getMessage()]);
                    $hasRiCol = false;
                }
                if ($hasRiCol) {
                    $updateData['ri_soutarah'] = trim((string)$request->ri_soutarah);
                }

                $stepLog('6_update_prep', ['hasDetailsCol' => $hasDetailsCol, 'hasRiCol' => $hasRiCol, 'keys' => array_keys($updateData)]);

                \Illuminate\Support\Facades\DB::table('depannages')->where('id', $id)->update($updateData);
            } catch (\Throwable $e) {
                @unlink($uploadPath . '/' . ($carnetName ?? ''));
                @unlink($uploadPath . '/' . ($equipName ?? ''));
                $stepLog('7_bdd_ko', [
                    'err' => $e->getMessage(),
                    'sqlstate' => method_exists($e, 'getSqlState') ? $e->getSqlState() : null,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return $safeRedirectBack('error', 'Erreur enregistrement BDD : ' . $e->getMessage());
            }
            $stepLog('7_bdd_ok');

            try {
                $admin = User::where('type_utilisateur', 'admin')->first();
                if ($admin) {
                    NotificationController::create(
                        $admin->id,
                        $depannage->id,
                        'rapport_technicien_soumis',
                        "Rapport complet soumis pour intervention #{$id}",
                        "{$user->nom_complet} a soumis le rapport complet avec photos et compte-rendu"
                    );
                }

                $superviseurSoutarah = null;
                if ($depannage->equipement && $depannage->equipement->site && $depannage->equipement->site->base_id) {
                    try {
                        $assignment = \App\Models\Assignment::with('superviseur')
                            ->where('base_id', $depannage->equipement->site->base_id)
                            ->first();
                        if ($assignment && $assignment->superviseur) {
                            $superviseurSoutarah = $assignment->superviseur;
                        }
                    } catch (\Throwable $eAssign) {
                        $stepLog('8_assign_fail_silent', ['err' => $eAssign->getMessage()]);
                    }
                }

                if ($superviseurSoutarah) {
                    $statutLabels = [
                        'resolu'               => 'Résolu & Terminé',
                        'en_attente_piece'     => 'EN ATTENTE DE PIECE',
                        'partiellement_resolu' => 'Partiellement résolu (autre passage requis)',
                        'non_resolu'           => 'NON RESOLU / BLOQUE',
                    ];
                    $statutLabel = $statutLabels[$request->statut] ?? $request->statut;
                    $isBlocking  = in_array($request->statut, ['en_attente_piece', 'non_resolu']);
                    $notifTitle  = $isBlocking
                        ? "ACTION REQUISE - Intervention #{$id} : {$statutLabel}"
                        : "Rapport soumis - Intervention #{$id} : {$statutLabel}";
                    $notifBody   = $isBlocking
                        ? "{$user->nom_complet} signale un blocage. Détails : " . ($request->details_statut_terrain ?? '—')
                        : "{$user->nom_complet} a soumis le rapport. Statut : {$statutLabel}.";

                    NotificationController::create(
                        $superviseurSoutarah->id,
                        $depannage->id,
                        'rapport_technicien_soumis',
                        $notifTitle,
                        $notifBody
                    );
                }
            } catch (\Throwable $eNotif) {
                $stepLog('8_notif_silent_fail', ['err' => $eNotif->getMessage()]);
            }
            $stepLog('8_notif_ok');

            $successMessages = [
                'resolu'               => 'Rapport soumis avec succès ! Votre superviseur Soutarah va le transmettre au superviseur client pour validation.',
                'en_attente_piece'     => 'Rapport soumis. Votre superviseur a été alerté du besoin en pièce de rechange.',
                'partiellement_resolu' => 'Rapport soumis. Un autre passage sera planifié par votre superviseur.',
                'non_resolu'           => 'Rapport soumis. Votre superviseur a été notifié du blocage et prendra les mesures nécessaires.',
            ];
            $successMsg = $successMessages[$request->statut] ?? 'Rapport soumis avec succès.';
            $stepLog('9_success_redirect');

            try {
                return redirect()->route('depannages.show', $id)->with('success', $successMsg);
            } catch (\Throwable $eFinalRedirect) {
                $stepLog('9_redirect_fail', ['err' => $eFinalRedirect->getMessage()]);
                $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Rapport envoyé #' . (int)$id . '</title>'
                    . '<meta http-equiv="refresh" content="3;url=' . htmlspecialchars(route('depannages.show', $id), ENT_QUOTES) . '">'
                    . '<style>body{font-family:system-ui,Arial,sans-serif;max-width:640px;margin:40px auto;padding:0 16px;line-height:1.5}'
                    . '.ok{padding:18px;border-radius:12px;background:#d1fae5;border:2px solid #059669;color:#064e3b}'
                    . 'a{color:#1d4ed8;font-weight:700}</style></head><body>'
                    . '<div class="ok"><strong>✅ Rapport soumis — Intervention #' . (int)$id . '</strong><br><br>'
                    . htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8')
                    . '</div><br>Redirection automatique dans 3s… '
                    . '<a href="' . htmlspecialchars(route('depannages.show', $id), ENT_QUOTES) . '">ou cliquez ici</a>.'
                    . '</body></html>';
                header('Content-Type: text/html; charset=utf-8');
                die($html);
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (empty($msg)) $msg = get_class($e);
            $stepLog('FATAL', [
                'err'    => $msg,
                'class'  => get_class($e),
                'file'   => $e->getFile(),
                'line'   => $e->getLine(),
                'trace'  => $e->getTraceAsString(),
            ]);
            return $safeRedirectBack('error', 'Erreur système [' . get_class($e) . '] : ' . $msg . ' (ligne ' . $e->getLine() . ')');
        }
    }
}
