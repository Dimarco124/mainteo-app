<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Depannage;
use App\Models\Site;
use App\Models\Equipement;
use App\Models\User;
use App\Models\Assignment;
use App\Models\InterventionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemandeController extends Controller
{
    /**
     * Page principale des demandes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && in_array($user->type_utilisateur, ['demandeur', 'superviseur_client'])) {
            return $this->indexDemandes($request, 'all');
        }
        return redirect()->route('demandes.standard');
    }

    /**
     * Liste des demandes STANDARDS uniquement (non VIP)
     */
    public function indexStandard(Request $request)
    {
        return $this->indexDemandes($request, 'standard');
    }

    /**
     * Liste des demandes VIP uniquement
     */
    public function indexVip(Request $request)
    {
        return $this->indexDemandes($request, 'vip');
    }

    /**
     * Méthode centralisée pour lister les demandes
     */
    private function indexDemandes(Request $request, $typeVip = 'all')
    {
        $user = Auth::user();
        $role = $user->type_utilisateur;

        // Les clients (demandeurs et superviseurs clients) ne doivent JAMAIS avoir de filtre VIP
        // Ils voient TOUTES leurs demandes confondues (Standards + VIP)
        if (in_array($role, ['demandeur', 'superviseur_client'])) {
            $typeVip = 'all';
        }

        // Afficher les DEMANDES réelles
        $query = Demande::with(['createdBy', 'client', 'base', 'site', 'equipement', 'validatedByClient', 'validatedBySoutarah', 'technicalOperation.equipe', 'technicalOperation.technicien']);

        // Filtrer selon le rôle
        if ($role === 'demandeur') {
            // Demandeur : ses demandes ou celles de ses sites assignés
            $siteIds = $user->sitesAssignes->pluck('id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            $query->where(function($q) use ($user, $siteIds) {
                $q->where('created_by_user_id', $user->id);
                if (!empty($siteIds)) {
                    $q->orWhereIn('site_id', $siteIds);
                }
            });
        } elseif ($role === 'superviseur_client') {
            // Superviseur Client : uniquement les demandes de SA base OU de son client direct
            if ($user->base_id) {
                $query->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $query->where('client_id', $user->client_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'superviseur_soutarah') {
            // Superviseur Soutarah : uniquement les demandes VALIDÉES par le client de SA base/client assigné(e)
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->where('base_id', $assignment->base_id)
                        ->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                } elseif ($assignment->client_id) {
                    $query->where('client_id', $assignment->client_id)
                        ->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($role === 'admin') {
            // Admin : uniquement les demandes VALIDÉES par le client (pas les demandes en attente de validation client)
            $query->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed', 'rejected_by_client', 'rejected_by_soutarah']);
        }

        // Filtres dynamiques
        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }
        if ($urgence = $request->input('niveau_urgence')) {
            $query->where('niveau_urgence', $urgence);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'like', "%{$search}%")
                    ->orWhere('numero_reference_externe', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('equipement', function ($eq) use ($search) {
                        $eq->where('equipement_nom', 'like', "%{$search}%");
                    });
            });
        }
        // Filtre par plage de dates
        if ($dateDebut = $request->input('date_debut')) {
            $query->whereDate('created_at', '>=', $dateDebut);
        }
        if ($dateFin = $request->input('date_fin')) {
            $query->whereDate('created_at', '<=', $dateFin);
        }
        // 🚨 Filtre VIP selon le type demandé
        if ($typeVip === 'vip') {
            $query->where('est_vip', true);
        } elseif ($typeVip === 'standard') {
            $query->where(function($q) {
                $q->where('est_vip', false)->orWhereNull('est_vip');
            });
        }
        
        // Filtre VIP manuel (paramètre URL)
        if ($request->input('vip')) {
            $query->where('est_vip', true);
        }

        $demandes = $query->orderBy('id', 'desc')->paginate(15);

        // Compteurs par statut - avec filtre VIP appliqué
        $baseQuery = Demande::query();
        
        // 🚨 Appliquer le filtre VIP aux stats selon le type demandé
        if ($typeVip === 'vip') {
            $baseQuery->where('est_vip', true);
        } elseif ($typeVip === 'standard') {
            $baseQuery->where(function($q) {
                $q->where('est_vip', false)->orWhereNull('est_vip');
            });
        }
        
        if ($role === 'demandeur') {
            $baseQuery->where('created_by_user_id', $user->id);
        } elseif ($role === 'superviseur_client') {
            if ($user->base_id) {
                $baseQuery->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $baseQuery->where('client_id', $user->client_id);
            }
        } elseif ($role === 'superviseur_soutarah') {
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $baseQuery->where('base_id', $assignment->base_id)
                        ->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                } elseif ($assignment->client_id) {
                    $baseQuery->where('client_id', $assignment->client_id)
                        ->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed']);
                }
            }
        } elseif ($role === 'admin') {
            $baseQuery->whereIn('statut', ['validated_by_client', 'needs_technical_operation', 'confirmed_by_demandeur', 'closed', 'rejected_by_client', 'rejected_by_soutarah']);
        }

        $totalCount = (clone $baseQuery)->count();
        $pendingClientValidation = 0; // Admin et Soutarah ne voient pas ces demandes

        // Pour Superviseur Client et Demandeur uniquement
        if ($role === 'superviseur_client' || $role === 'demandeur') {
            $pendingClientValidation = (clone $baseQuery)->where('statut', 'pending_client_validation')->count();
        }

        $validatedByClient = (clone $baseQuery)->where('statut', 'validated_by_client')->count();
        $needsTechnicalOperation = (clone $baseQuery)->where('statut', 'needs_technical_operation')->count();
        $closedCount = (clone $baseQuery)->where('statut', 'closed')->count();

        return view('demandes.index', compact('demandes', 'totalCount', 'pendingClientValidation', 'validatedByClient', 'needsTechnicalOperation', 'closedCount', 'typeVip'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();

        // Récupérer les sites accessibles
        if ($user->isDemandeur()) {
            // Nouveau système prioritaire : Charger d'abord les sites assignés (many-to-many)
            $sites = $user->sitesAssignes()->with('baseSite')->orderBy('nom_site')->get();
            
            // Si aucun site assigné via relation many-to-many, vérifier l'ancien système (site_id unique)
            if ($sites->isEmpty() && $user->site_id) {
                // Ancien système : site unique (fallback)
                $sites = Site::where('id', $user->site_id)->with('baseSite')->get();
            }
            
            // 🚨 Si le demandeur n'a TOUJOURS AUCUN site assigné après les deux vérifications
            if ($sites->isEmpty()) {
                // Pas de redirection, la vue affichera le message d'erreur
                $sites = collect();
            }
        } elseif ($user->isSuperviseurClient()) {
            // Les superviseurs clients utilisent base_id OU client_id (PAS le système many-to-many)
            if ($user->base_id) {
                // Structure : Client → Base → Sites
                $sites = Site::where('base_id', $user->base_id)->with('baseSite')->orderBy('nom_site')->get();
            } elseif ($user->client_id) {
                // Structure : Client → Sites (sans base)
                $sites = Site::where('client_id', $user->client_id)->with('baseSite')->orderBy('nom_site')->get();
            } else {
                $sites = collect();
            }
        } else {
            $sites = Site::with('baseSite')->orderBy('nom_site')->get();
        }

        // Récupérer le client de l'utilisateur connecté
        $userClient = null;
        if ($user->client_id) {
            $userClient = \App\Models\Client::find($user->client_id);
        } elseif ($user->base_id) {
            $base = \App\Models\BaseSite::find($user->base_id);
            if ($base) {
                $userClient = $base->client;
            }
        }

        return view('demandes.create', compact('sites', 'userClient'));
    }

    /**
     * Enregistrer une nouvelle demande
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // LOG DE DIAGNOSTIC - à supprimer après débogage
        \Illuminate\Support\Facades\Log::info('[DEMANDE STORE] Appel reçu', [
            'user_id'   => $user->id,
            'user_type' => $user->type_utilisateur,
            'base_id'   => $user->base_id,
            'client_id' => $user->client_id,
            'inputs'    => $request->except(['_token']),
        ]);

        // Déterminer le client de l'utilisateur pour validation conditionnelle
        $userClient = null;
        if ($user->client_id) {
            $userClient = \App\Models\Client::find($user->client_id);
        } elseif ($user->base_id) {
            $base = \App\Models\BaseSite::find($user->base_id);
            if ($base) {
                $userClient = $base->client;
            }
        }

        // Règles de validation conditionnelle selon le client
        $validationRules = [
            'type_intervention'    => 'required|in:Dépannage,Installation',
            'site_id'              => 'nullable|exists:sites,id',
            'zone_id'              => 'nullable|exists:zones_sites,id',
            'equipement_id'        => 'required|exists:equipements,id',
            'description'          => 'required|string',
            'niveau_urgence'       => 'required|in:faible,moyen,urgent,critique',
            'date_debut_souhaitee' => 'nullable|date',
        ];

        // 🚨 Validation conditionnelle du numéro de référence externe selon le client
        if ($userClient && strtoupper($userClient->nom) === 'SUCAF') {
            // SUCAF : Champ OBLIGATOIRE et unique
            $validationRules['numero_reference_externe'] = 'required|string|max:100|unique:demandes,numero_reference_externe';
        } else {
            // RETROCI ou autres : Champ OPTIONNEL mais unique si fourni
            $validationRules['numero_reference_externe'] = 'nullable|string|max:100|unique:demandes,numero_reference_externe';
        }

        $validated = $request->validate($validationRules);

        // Récupérer l'équipement avec sa zone
        $equipement = Equipement::with('site.baseSite', 'client', 'zone')->findOrFail($validated['equipement_id']);
        // 🚨 DÉFINITION VIP (par Superviseur Client ou Admin uniquement, jamais par le demandeur)
        $estVip = ($user->isSuperviseurClient() || $user->isAdmin()) && $request->boolean('est_vip');

        // Déterminer client_id, base_id, site_id selon la structure
        $clientId = null;
        $baseId = null;
        $siteId = $validated['site_id'] ?? null;

        if ($equipement->site) {
            // Cas normal : Client → Base → Site → Équipement
            $siteId = $equipement->site->id;
            $baseId = $equipement->site->base_id;

            if ($equipement->site->baseSite) {
                // Structure 1 : client → base → site → équipement
                $clientId = $equipement->site->baseSite->client_id;
            } else {
                // Structure 2 : client → site → équipement (pas de base)
                $clientId = $equipement->site->client_id;
            }
        } else {
            // Cas entreprise directe : Client → Équipement
            $clientId = $equipement->client_id;
            $baseId = null;
            $siteId = null;
        }

        // Vérifier les permissions
        if ($user->isDemandeur()) {
            if ($siteId) {
                // Vérifier que le site fait partie des sites assignés (many-to-many ou site_id direct)
                $siteIds = $user->sitesAssignes()->pluck('sites.id')->map(fn($id) => (int)$id)->toArray();
                if ($user->site_id) {
                    $siteIds[] = (int) $user->site_id;
                }
                if (!in_array((int)$siteId, $siteIds)) {
                    abort(403, 'Vous ne pouvez créer des demandes que pour vos sites assignés.');
                }
            } else {
                // Pour entreprise directe, vérifier que le demandeur appartient au même client
                if ($user->client_id != $clientId) {
                    abort(403, 'Vous ne pouvez créer des demandes que pour votre entreprise.');
                }
            }
        }

        if ($user->isSuperviseurClient()) {
            $hasAccess = false;
            if ($user->base_id && $user->base_id == $baseId) {
                $hasAccess = true;
            } elseif ($user->client_id && $user->client_id == $clientId) {
                $hasAccess = true;
            }
            // Si ni base_id ni client_id n'est défini sur le superviseur, on autorise quand même
            // (cas d'un superviseur non encore configuré)
            if (!$user->base_id && !$user->client_id) {
                $hasAccess = true;
            }
            \Illuminate\Support\Facades\Log::info('[DEMANDE STORE] Vérif superviseur', [
                'hasAccess' => $hasAccess,
                'user_base_id'  => $user->base_id,
                'user_client_id'=> $user->client_id,
                'equip_base_id' => $baseId,
                'equip_client_id'=> $clientId,
            ]);

            if (!$hasAccess) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Accès refusé : cet équipement n\'appartient pas à votre périmètre. (base: '.$baseId.' / client: '.$clientId.')');
            }
        }

        // Créer la demande
        // Demandeur -> toujours 'pending_client_validation' (le superviseur client définira le VIP à la validation)
        // Superviseur client ou Admin -> directement 'validated_by_client'
        $statutInitial = $user->isDemandeur() ? 'pending_client_validation' : 'validated_by_client';
        
        $demande = Demande::create([
            'numero_reference_externe' => $validated['numero_reference_externe'],
            'created_by_user_id' => $user->id,
            'created_by_role' => $user->isDemandeur() ? 'demandeur' : ($user->isAdmin() ? 'admin' : 'superviseur_client'),
            'client_id' => $clientId,
            'base_id' => $baseId,
            'site_id' => $siteId,
            'zone_id' => $request->input('zone_id') ?: $equipement->zone_id,
            'equipement_id' => $validated['equipement_id'],
            'type_intervention' => $validated['type_intervention'],
            'description' => $validated['description'],
            'niveau_urgence' => $estVip ? 'critique' : $validated['niveau_urgence'],
            'date_debut_souhaitee' => $validated['date_debut_souhaitee'] ?? null,
            'photo_panne' => null,
            'statut' => $statutInitial,
            'est_vip' => $estVip,
        ]);

        // Si créée par demandeur, notifier les superviseurs clients pour validation
        if ($user->isDemandeur()) {
            $superviseurs = User::where('type_utilisateur', 'superviseur_client')
                ->where(function ($q) use ($baseId, $clientId) {
                    if ($baseId) {
                        $q->where('base_id', $baseId);
                    }
                    if ($clientId) {
                        $q->orWhere('client_id', $clientId);
                    }
                })
                ->get();

            foreach ($superviseurs as $superviseur) {
                InterventionNotification::create([
                    'user_id' => $superviseur->id,
                    'intervention_id' => null,
                    'demande_id' => $demande->id,
                    'type' => 'nouvelle_demande',
                    'message' => "Nouvelle demande #{$demande->numero_demande} créée par {$user->nom_complet}",
                    'statut' => 'non_lu',
                ]);
            }
        } else {
            // Créée directement par Superviseur Client ou Admin : notifier Soutarah (avec statut VIP éventuel)
            $this->notifierValidationClient($demande, $estVip);
        }

        if (in_array($user->type_utilisateur, ['demandeur', 'superviseur_client'])) {
            return redirect()->route('demandes.index')
                ->with('success', 'Votre demande d\'intervention a bien été enregistrée.');
        }

        return redirect()->route('demandes.standard')
            ->with('success', 'Demande créée avec succès.');
    }

    /**
     * Afficher une demande
     */
    public function show(Demande $demande)
    {
        $this->authorizeAccess($demande);

        $demande->load(['createdBy', 'client', 'base', 'site', 'equipement', 'technicalOperation', 'validatedByClient', 'validatedBySoutarah', 'validatedFinaleBy', 'appelEffectuePar']);

        return view('demandes.show', compact('demande'));
    }

    /**
     * Validation par Superviseur Client
     */
    public function validateByClient(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Seuls les superviseurs client peuvent effectuer cette action.');
        }

        if (!$this->checkSuperviseurClientAccess($user, $demande)) {
            \Illuminate\Support\Facades\Log::warning('[DEMANDE VALIDATE] Accès refusé', [
                'user_id' => $user->id,
                'user_base' => $user->base_id,
                'user_client' => $user->client_id,
                'demande_id' => $demande->id,
                'demande_base' => $demande->base_id,
                'demande_client' => $demande->client_id,
            ]);
            return back()->with('error', 'Vous ne pouvez valider que les demandes de votre périmètre.');
        }

        if ($demande->statut != 'pending_client_validation') {
            return back()->with('error', 'Cette demande ne peut plus être validée.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string',
            'est_vip' => 'nullable|boolean',
        ]);

        $estVip = $request->boolean('est_vip');

        DB::transaction(function () use ($demande, $user, $validated, $estVip) {
            $updateData = [
                'statut' => 'validated_by_client',
                'validated_by_client_user_id' => $user->id,
                'date_validation_client' => now(),
                'message_validation_client' => $validated['message'] ?? null,
                'est_vip' => $estVip,
            ];

            if ($estVip) {
                $updateData['niveau_urgence'] = 'critique';
            }

            $demande->update($updateData);

            // Notifier le demandeur que sa demande a été validée
            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => null,
                'demande_id' => $demande->id,
                'type' => 'demande_validee_superviseur',
                'message' => "Votre demande #{$demande->numero_demande} a été validée par {$user->nom_complet}",
                'statut' => 'non_lu',
            ]);

            // Notifier Admin et Superviseur Soutarah (avec flag VIP si coché)
            $this->notifierValidationClient($demande, $estVip);
        });

        return back()->with('success', $estVip ? 'Demande validée et qualifiée en VIP avec succès.' : 'Demande validée avec succès.');
    }

    /**
     * Rejet par Superviseur Client
     */
    public function rejectByClient(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Seuls les superviseurs client peuvent effectuer cette action.');
        }

        if (!$this->checkSuperviseurClientAccess($user, $demande)) {
            return back()->with('error', 'Vous ne pouvez rejeter que les demandes de votre périmètre.');
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $demande->update([
            'statut' => 'rejected_by_client',
            'validated_by_client_user_id' => $user->id,
            'date_validation_client' => now(),
            'message_validation_client' => $validated['message'],
        ]);

        // Notifier le demandeur
        InterventionNotification::create([
            'user_id' => $demande->created_by_user_id,
            'intervention_id' => null,
            'demande_id' => $demande->id,
            'type' => 'demande_rejetee',
            'message' => "Votre demande #{$demande->numero_demande} a été rejetée : {$validated['message']}",
            'statut' => 'non_lu',
        ]);

        return back()->with('success', 'Demande rejetée.');
    }

    /**
     * Validation par Admin/Superviseur Soutarah
     */
    public function validateBySoutarah(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403);
        }

        if ($demande->statut != 'validated_by_client') {
            return back()->with('error', 'Cette demande doit d\'abord être validée par le client.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'statut' => 'needs_technical_operation',
                'validated_by_soutarah_user_id' => $user->id,
                'date_validation_soutarah' => now(),
                'message_validation_soutarah' => $validated['message'] ?? null,
            ]);

            // ═══════════════════════════════════════════════════════════════════
            // CRÉER AUTOMATIQUEMENT L'INTERVENTION (DEPANNAGE) 
            // ═══════════════════════════════════════════════════════════════════
            $depannage = \App\Models\Depannage::create([
                'demande_id' => $demande->id,
                'type_intervention' => $demande->type_intervention,
                'equipement_id' => $demande->equipement_id,
                'equipement_reference' => $demande->equipement_reference ?? ($demande->equipement ? $demande->equipement->equipement_code . ' - ' . $demande->equipement->equipement_nom : 'N/A'),
                'description_panne' => $demande->description,
                'urgence' => ucfirst($demande->niveau_urgence), // Convertir 'urgent' en 'Urgent'
                'photo_panne' => null,
                'fichier_joint' => null,
                'client_id' => $demande->client_id,
                'client_nom' => $demande->client ? $demande->client->nom : null,
                'demandeur_id' => $demande->created_by_user_id,
                'demandeur_nom' => $demande->demandeur_nom,
                'date_demande' => $demande->created_at->toDateString(),
                'statut' => 'en attente',
                'etat_demande' => 'Validée',
                'created_by_role' => $user->type_utilisateur, // Utiliser le rôle de l'utilisateur qui valide
                'technicien_id' => null,
                'equipe_id' => null,
                'date_prevue' => null,
            ]);

            // Mettre à jour la demande avec l'ID de l'opération créée
            $demande->update([
                'technical_operation_id' => $depannage->id,
            ]);

            // Notifier le superviseur client de la validation
            if ($demande->validatedByClient) {
                InterventionNotification::create([
                    'user_id' => $demande->validated_by_client_user_id,
                    'intervention_id' => $depannage->id,
                    'demande_id' => $demande->id,
                    'type' => 'demande_validee_soutarah',
                    'message' => "La demande #{$demande->numero_demande} a été validée par {$user->nom_complet}. Opération #{$depannage->id} créée.",
                    'statut' => 'non_lu',
                ]);
            }

            // Notifier également le demandeur
            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => $depannage->id,
                'demande_id' => $demande->id,
                'type' => 'demande_validee_soutarah',
                'message' => "Votre demande #{$demande->numero_demande} a été validée par Soutarah. Opération #{$depannage->id} créée et en attente d'affectation.",
                'statut' => 'non_lu',
            ]);
        });

        return redirect()->route('operations.index')
            ->with('success', 'Demande validée et opération créée automatiquement. Vous pouvez maintenant affecter une équipe.');
    }

    /**
     * Rejet par Admin/Superviseur Soutarah
     */
    public function rejectBySoutarah(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'statut' => 'rejected_by_soutarah',
                'validated_by_soutarah_user_id' => $user->id,
                'date_validation_soutarah' => now(),
                'message_validation_soutarah' => $validated['message'],
            ]);

            // Notifier le superviseur client
            if ($demande->validatedByClient) {
                InterventionNotification::create([
                    'user_id' => $demande->validated_by_client_user_id,
                    'intervention_id' => null,
                    'demande_id' => $demande->id,
                    'type' => 'demande_rejetee',
                    'message' => "La demande #{$demande->numero_demande} a été rejetée par Soutarah : {$validated['message']}",
                    'statut' => 'non_lu',
                ]);
            }

            // Notifier également le demandeur
            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => null,
                'demande_id' => $demande->id,
                'type' => 'demande_rejetee',
                'message' => "Votre demande #{$demande->numero_demande} a été rejetée par Soutarah : {$validated['message']}",
                'statut' => 'non_lu',
            ]);
        });

        return back()->with('success', 'Demande rejetée.');
    }



    /**
     * Validation d'une demande d'Installation par Soutarah/Admin
     */
    public function validateInstallation(Request $request, Demande $demande)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403);
        }

        if ($demande->type_intervention !== 'Installation') {
            return back()->with('error', 'Cette action ne concerne que les demandes d\'installation.');
        }

        DB::transaction(function() use ($demande, $user) {
            // Créer l'opération technique d'installation
            $depannage = \App\Models\Depannage::create([
                'demande_id'            => $demande->id,
                'type_intervention'     => 'Installation',
                'equipement_id'         => $demande->equipement_id,
                'equipement_reference'  => $demande->equipement ? $demande->equipement->equipement_code . ' - ' . $demande->equipement->equipement_nom : 'N/A',
                'description_panne'     => $demande->description,
                'urgence'               => ucfirst($demande->niveau_urgence),
                'date_debut_prevue'     => $demande->date_debut_souhaitee,
                'date_fin_prevue'       => $demande->date_debut_souhaitee,
                'client_id'             => $demande->client_id,
                'client_nom'            => $demande->client ? $demande->client->nom : null,
                'demandeur_id'          => $demande->created_by_user_id,
                'date_demande'          => $demande->created_at->toDateString(),
                'statut'                => 'en attente',
                'etat_demande'          => 'Validée',
                'created_by_role'       => $user->type_utilisateur,
            ]);

            $demande->update([
                'statut' => 'needs_technical_operation',
                'validated_by_soutarah_user_id' => $user->id,
                'date_validation_soutarah' => now(),
                'technical_operation_id' => $depannage->id,
            ]);

            if ($demande->equipement) {
                $demande->equipement->update(['etat' => 'bon']);
            }

            // Notifier le client créateur
            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'demande_id' => $demande->id,
                'intervention_id' => $depannage->id,
                'type' => 'demande_installation_validee',
                'message' => "Votre demande d'installation #{$demande->numero_demande} a été validée par Soutarah ! Opération #{$depannage->id} prête pour affectation.",
                'statut' => 'non_lu',
            ]);
        });

        return back()->with('success', 'Demande d\'installation validée avec succès. L\'équipement est activé.');
    }

    /**
     * Rejet d'une demande d'Installation par Soutarah/Admin
     */
    public function rejectInstallation(Request $request, Demande $demande)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        DB::transaction(function() use ($demande, $user, $validated) {
            $demande->update([
                'statut' => 'rejected_by_soutarah',
                'validated_by_soutarah_user_id' => $user->id,
                'date_validation_soutarah' => now(),
                'message_validation_soutarah' => $validated['message'],
            ]);

            if ($demande->equipement) {
                $demande->equipement->update(['etat' => 'hors service']);
            }

            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'demande_id' => $demande->id,
                'type' => 'demande_installation_rejetee',
                'message' => "Votre demande d'installation #{$demande->numero_demande} a été rejetée : {$validated['message']}",
                'statut' => 'non_lu',
            ]);
        });

        return back()->with('warning', 'Demande d\'installation rejetée.');
    }

    /**
     * Notifier Admin et Superviseur Soutarah après validation client
     */
    private function notifierValidationClient(Demande $demande, $estVip = false)
    {
        // 🚨 Message différent pour les demandes VIP
        $messageType = $estVip ? 'demande_vip_urgente' : 'demande_validee_client';
        $prefixeMessage = $estVip ? '🚨 URGENT VIP' : 'Nouvelle demande';
        
        // 1. Notifier tous les admins
        $admins = User::where('type_utilisateur', 'admin')->get();
        foreach ($admins as $admin) {
            InterventionNotification::create([
                'user_id' => $admin->id,
                'intervention_id' => null,
                'demande_id' => $demande->id,
                'type' => $messageType,
                'message' => "{$prefixeMessage} #{$demande->numero_demande}" . ($estVip ? " - Demande qualifiée VIP par le client (Traitement prioritaire)" : " validée par le client et en attente de votre validation"),
                'statut' => 'non_lu',
            ]);
        }

        // 2. Notifier le(s) superviseur(s) Soutarah concerné(s)
        // Chercher par base_id OU par client_id
        $assignments = Assignment::where(function ($query) use ($demande) {
            $query->where('base_id', $demande->base_id)
                ->orWhere('client_id', $demande->client_id);
        })->get();

        foreach ($assignments as $assignment) {
            // Vérifier que la notification n'a pas déjà été créée (éviter les doublons)
            $existeDeja = InterventionNotification::where('user_id', $assignment->superviseur_soutarah_id)
                ->where('demande_id', $demande->id)
                ->where('type', $messageType)
                ->exists();

            if (!$existeDeja) {
                InterventionNotification::create([
                    'user_id' => $assignment->superviseur_soutarah_id,
                    'intervention_id' => null,
                    'demande_id' => $demande->id,
                    'type' => $messageType,
                    'message' => "{$prefixeMessage} #{$demande->numero_demande}" . ($estVip ? " - Demande qualifiée VIP par le client dans votre périmètre - TRAITER IMMÉDIATEMENT" : " validée pour votre périmètre et en attente de votre validation"),
                    'statut' => 'non_lu',
                ]);
            }
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * PHASE 1 : ENREGISTREMENT APPEL TELEPHONIQUE AU DEMANDEUR
     * Après validation Soutarah, on appelle le demandeur pour convenir
     * d'une date d'intervention et on enregistre le compte-rendu.
     * ═══════════════════════════════════════════════════════════════════
     */
    public function enregistrerAppel(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403, 'Seuls les administrateurs et superviseurs Soutarah peuvent enregistrer un appel.');
        }

        if (!in_array($demande->statut, ['validated_by_client', 'needs_technical_operation'])) {
            return back()->with('error', 'Cette demande ne peut pas faire l\'objet d\'un appel pour le moment.');
        }

        $validated = $request->validate([
            'appel_notes' => 'required|string|max:2000',
            'date_confirmee_avec_demandeur' => 'nullable|date',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $anciennesNotes = $demande->appel_notes;
            $historique = '';
            if ($anciennesNotes) {
                $historique = "\n\n--- [Appel précédent - " . ($demande->appel_date ? $demande->appel_date->format('d/m/Y H:i') : 'Sans date') . " par " . ($demande->appelEffectuePar ? $demande->appelEffectuePar->nom_complet : 'Inconnu') . "] ---\n" . $anciennesNotes;
            }

            $demande->update([
                'appel_notes' => $validated['appel_notes'] . $historique,
                'appel_effectue_par_user_id' => $user->id,
                'appel_date' => now(),
                'date_confirmee_avec_demandeur' => $validated['date_confirmee_avec_demandeur'] ?? null,
            ]);

            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => null,
                'demande_id' => $demande->id,
                'type' => 'appel_enregistre',
                'message' => "Appel enregistré pour votre demande #{$demande->numero_demande}" . (isset($validated['date_confirmee_avec_demandeur']) ? " - Date prévue : " . date('d/m/Y', strtotime($validated['date_confirmee_avec_demandeur'])) : ''),
                'statut' => 'non_lu',
            ]);

            $admins = User::where('type_utilisateur', 'admin')->get();
            foreach ($admins as $admin) {
                if ($admin->id != $user->id) {
                    InterventionNotification::create([
                        'user_id' => $admin->id,
                        'intervention_id' => null,
                        'demande_id' => $demande->id,
                        'type' => 'appel_enregistre',
                        'message' => "Appel enregistré sur demande #{$demande->numero_demande} par {$user->nom_complet}",
                        'statut' => 'non_lu',
                    ]);
                }
            }
        });

        return back()->with('success', 'Appel enregistré avec succès.' . (isset($validated['date_confirmee_avec_demandeur']) ? ' Date confirmée : ' . date('d/m/Y', strtotime($validated['date_confirmee_avec_demandeur'])) : ''));
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * PHASE 5 : CONFIRMATION PAR LE DEMANDEUR
     * Le demandeur confirme que le travail a été bien réalisé
     * ═══════════════════════════════════════════════════════════════════
     */
    public function confirmByDemandeur(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        // Vérifier que c'est bien le demandeur qui a créé la demande
        if (!$user->isDemandeur() || $demande->created_by_user_id != $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à confirmer cette demande.');
        }

        // Vérifier que l'opération technique a été complétée et qu'un rapport a été soumis
        if (!$demande->technicalOperation || !$demande->technicalOperation->rapport) {
            return back()->with('error', 'Le rapport technique doit être soumis avant confirmation.');
        }

        $validated = $request->validate([
            'commentaire' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'statut' => 'confirmed_by_demandeur',
                'date_confirmation_demandeur' => now(),
                'commentaire_demandeur' => $validated['commentaire'] ?? 'Travaux confirmés',
            ]);

            // Notifier le superviseur client pour validation finale
            $superviseurs = User::where('base_id', $demande->base_id)
                ->where('type_utilisateur', 'superviseur_client')
                ->get();

            foreach ($superviseurs as $superviseur) {
                InterventionNotification::create([
                    'user_id' => $superviseur->id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'demande_confirmee_demandeur',
                    'message' => "Demande #{$demande->numero_demande} confirmée par le demandeur. Validation finale requise.",
                    'statut' => 'non_lu',
                ]);
            }
        });

        return back()->with('success', 'Travaux confirmés avec succès. En attente de validation finale du superviseur.');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * PHASE 5 : VALIDATION FINALE PAR SUPERVISEUR CLIENT
     * Clôture définitive de la demande
     * ═══════════════════════════════════════════════════════════════════
     */
    public function validateFinale(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403, 'Vous n\'êtes pas autorisé à valider cette demande.');
        }

        // Vérifier que la demande appartient à la base OU au client du superviseur
        $hasAccess = false;
        if ($user->base_id && $user->base_id == $demande->base_id) {
            $hasAccess = true;
        } elseif ($user->client_id && $user->client_id == $demande->client_id) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'Vous ne pouvez valider que les demandes de votre périmètre.');
        }

        if ($demande->statut != 'confirmed_by_demandeur') {
            return back()->with('error', 'La demande doit d\'abord être confirmée par le demandeur.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'statut' => 'closed',
                'date_validation_finale' => now(),
                'validated_finale_by_user_id' => $user->id,
                'message_validation_finale' => $validated['message'] ?? 'Demande clôturée avec succès',
            ]);

            // Mettre à jour l'opération technique aussi
            if ($demande->technicalOperation) {
                $demande->technicalOperation->update([
                    'statut_operation' => 'closed',
                ]);
            }

            // Notifier l'admin et le demandeur
            $admins = User::where('type_utilisateur', 'admin')->get();
            foreach ($admins as $admin) {
                InterventionNotification::create([
                    'user_id' => $admin->id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'demande_cloturee',
                    'message' => "Demande #{$demande->numero_demande} clôturée par {$user->nom_complet}",
                    'statut' => 'non_lu',
                ]);
            }

            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => $demande->technical_operation_id,
                'demande_id' => $demande->id,
                'type' => 'demande_cloturee',
                'message' => "Votre demande #{$demande->numero_demande} a été clôturée avec succès",
                'statut' => 'non_lu',
            ]);
        });

        return back()->with('success', 'Demande clôturée définitivement. Le workflow est terminé.');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * PHASE 5 : REJET FINAL (travaux non conformes)
     * Le superviseur client peut rejeter si les travaux ne sont pas satisfaisants
     * ═══════════════════════════════════════════════════════════════════
     */
    public function rejectFinale(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403, 'Vous n\'êtes pas autorisé à rejeter cette demande.');
        }

        // Vérifier que la demande appartient à la base OU au client du superviseur
        $hasAccess = false;
        if ($user->base_id && $user->base_id == $demande->base_id) {
            $hasAccess = true;
        } elseif ($user->client_id && $user->client_id == $demande->client_id) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'Vous ne pouvez rejeter que les demandes de votre périmètre.');
        }

        if ($demande->statut != 'confirmed_by_demandeur') {
            return back()->with('error', 'La demande doit être confirmée par le demandeur avant rejet.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            // Remettre le statut à "needs_technical_operation" pour une nouvelle intervention
            $demande->update([
                'statut' => 'needs_technical_operation',
                'date_validation_finale' => now(),
                'validated_finale_by_user_id' => $user->id,
                'message_validation_finale' => "REJET : {$validated['message']}",
            ]);

            // Mettre à jour l'opération technique
            if ($demande->technicalOperation) {
                $demande->technicalOperation->update([
                    'statut_operation' => 'rework_needed',
                    'statut' => 'en attente',
                ]);
            }

            // Notifier l'admin et le technicien
            $admins = User::where('type_utilisateur', 'admin')->get();
            foreach ($admins as $admin) {
                InterventionNotification::create([
                    'user_id' => $admin->id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'travaux_non_conformes',
                    'message' => "Travaux non conformes pour demande #{$demande->numero_demande} : {$validated['message']}",
                    'statut' => 'non_lu',
                ]);
            }

            if ($demande->technicalOperation && $demande->technicalOperation->technicien_id) {
                InterventionNotification::create([
                    'user_id' => $demande->technicalOperation->technicien_id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'travaux_non_conformes',
                    'message' => "Travaux à refaire pour l'intervention #{$demande->technical_operation_id} : {$validated['message']}",
                    'statut' => 'non_lu',
                ]);
            }
        });

        return back()->with('warning', 'Travaux rejetés. Une nouvelle intervention sera nécessaire.');
    }

    /**
     * Vérifier l'accès à une demande
     */
    private function authorizeAccess(Demande $demande)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDemandeur() && $demande->created_by_user_id != $user->id) {
            abort(403);
        }

        if ($user->isSuperviseurClient()) {
            if (!$this->checkSuperviseurClientAccess($user, $demande)) {
                abort(403, 'Vous ne pouvez accéder qu\'aux demandes de votre périmètre.');
            }
        }

        if ($user->isSuperviseurSoutarah()) {
            $assignment = Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if (
                !$assignment ||
                ($assignment->base_id && $assignment->base_id != $demande->base_id) ||
                ($assignment->client_id && $assignment->client_id != $demande->client_id)
            ) {
                abort(403);
            }
        }
    }

    /**
     * Empêcher toute modification sur une demande déjà clôturée
     */
    private function preventIfClosed(Demande $demande)
    {
        if ($demande->statut === 'closed') {
            abort(403, 'Cette demande est clôturée et ne peut plus être modifiée.');
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * WORKFLOW DE CLÔTURE - Demandeur confirme le passage des techniciens
     * ═══════════════════════════════════════════════════════════════════
     */
    public function confirmerPassageTechniciens(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        // Vérifier que l'utilisateur est bien le demandeur de cette demande
        if ($user->id != $demande->created_by_user_id) {
            abort(403, 'Vous n\'êtes pas autorisé à confirmer cette intervention.');
        }

        $validated = $request->validate([
            'techniciens_sont_passes' => 'required|boolean',
            'resultat_intervention' => 'required|in:satisfaisant,non_satisfaisant,partiellement_satisfaisant',
            'commentaire_resultat' => 'nullable|string',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'techniciens_sont_passes' => $validated['techniciens_sont_passes'],
                'resultat_intervention' => $validated['resultat_intervention'],
                'commentaire_resultat' => $validated['commentaire_resultat'],
                'date_confirmation_passage' => now(),
            ]);

            // Notifier le superviseur client (par base_id ou client_id)
            $superviseurs = User::where('type_utilisateur', 'superviseur_client')
                ->where(function ($q) use ($demande) {
                    if ($demande->base_id) {
                        $q->where('base_id', $demande->base_id);
                    }
                    if ($demande->client_id) {
                        $q->orWhere('client_id', $demande->client_id);
                    }
                })
                ->get();

            foreach ($superviseurs as $superviseurClient) {
                InterventionNotification::create([
                    'user_id' => $superviseurClient->id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'demandeur_a_confirme',
                    'message' => "Le demandeur {$user->nom_complet} a confirmé l'intervention. Résultat: {$validated['resultat_intervention']}",
                    'statut' => 'non_lu',
                ]);
            }
        });

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Votre confirmation a été enregistrée. Merci!');
    }

    /**
     * Superviseur Client valide la clôture (après comparaison des 2 rapports)
     */
    public function validerCloture(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Seuls les superviseurs client peuvent valider la clôture.');
        }

        if (!$this->checkSuperviseurClientAccess($user, $demande)) {
            return back()->with('error', 'Vous ne pouvez valider que les demandes de votre périmètre.');
        }

        $validated = $request->validate([
            'message_cloture' => 'nullable|string',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'statut_validation_finale_client' => 'conforme',
                'date_cloture_finale' => now(),
                'cloture_par_user_id' => $user->id,
                'statut' => 'closed',
            ]);

            // Mettre à jour l'intervention technique aussi
            if ($demande->technical_operation_id) {
                $depannage = Depannage::find($demande->technical_operation_id);
                if ($depannage) {
                    $depannage->update([
                        'statut' => 'résolu',
                        'statut_validation_finale_client' => 'conforme',
                        'date_validation_finale_client' => now(),
                        'validated_by_client_user_id' => $user->id,
                        'commentaire_validation_client' => $validated['message_cloture'] ?? null,
                    ]);
                }
            }

            // Notifier le demandeur
            InterventionNotification::create([
                'user_id' => $demande->created_by_user_id,
                'intervention_id' => $demande->technical_operation_id,
                'demande_id' => $demande->id,
                'type' => 'intervention_cloturee',
                'message' => "Votre demande #{$demande->numero_demande} a été clôturée avec succès. L'intervention est terminée.",
                'statut' => 'non_lu',
            ]);

            // Notifier admin et superviseur Soutarah
            $admin = User::where('type_utilisateur', 'admin')->first();
            if ($admin) {
                InterventionNotification::create([
                    'user_id' => $admin->id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'intervention_cloturee',
                    'message' => "Demande #{$demande->numero_demande} clôturée par {$user->nom_complet}. Intervention conforme.",
                    'statut' => 'non_lu',
                ]);
            }

            if ($demande->validated_by_soutarah_user_id) {
                InterventionNotification::create([
                    'user_id' => $demande->validated_by_soutarah_user_id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'intervention_cloturee',
                    'message' => "Demande #{$demande->numero_demande} clôturée avec succès. Intervention conforme.",
                    'statut' => 'non_lu',
                ]);
            }
        });

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Intervention clôturée avec succès! Le dossier est maintenant terminé.');
    }

    /**
     * Superviseur Client signale une non-conformité
     */
    public function signalerNonConformite(Request $request, Demande $demande)
    {
        $this->preventIfClosed($demande);
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            return back()->with('error', 'Seuls les superviseurs client peuvent signaler une non-conformité.');
        }

        if (!$this->checkSuperviseurClientAccess($user, $demande)) {
            return back()->with('error', 'Vous ne pouvez signaler que les demandes de votre périmètre.');
        }

        $validated = $request->validate([
            'message_non_conformite' => 'required|string',
        ]);

        DB::transaction(function () use ($demande, $user, $validated) {
            $demande->update([
                'statut_validation_finale_client' => 'non_conforme',
                'message_non_conformite' => $validated['message_non_conformite'],
                'date_cloture_finale' => now(),
                'cloture_par_user_id' => $user->id,
            ]);

            // Mettre à jour l'intervention technique aussi
            if ($demande->technical_operation_id) {
                $depannage = Depannage::find($demande->technical_operation_id);
                if ($depannage) {
                    $depannage->update([
                        'statut_validation_finale_client' => 'non_conforme',
                        'commentaire_validation_client' => $validated['message_non_conformite'],
                        'date_validation_finale_client' => now(),
                        'validated_by_client_user_id' => $user->id,
                    ]);
                }
            }

            // La demande reste "en cours" pour permettre une réintervention

            // Notifier admin et superviseur Soutarah
            $admin = User::where('type_utilisateur', 'admin')->first();
            if ($admin) {
                InterventionNotification::create([
                    'user_id' => $admin->id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'non_conformite_signalee',
                    'message' => "Non-conformité signalée pour demande #{$demande->numero_demande}: {$validated['message_non_conformite']}",
                    'statut' => 'non_lu',
                ]);
            }

            if ($demande->validated_by_soutarah_user_id) {
                InterventionNotification::create([
                    'user_id' => $demande->validated_by_soutarah_user_id,
                    'intervention_id' => $demande->technical_operation_id,
                    'demande_id' => $demande->id,
                    'type' => 'non_conformite_signalee',
                    'message' => "Non-conformité signalée par {$user->nom_complet}: {$validated['message_non_conformite']}",
                    'statut' => 'non_lu',
                ]);
            }
        });

        return redirect()->route('demandes.show', $demande)
            ->with('warning', 'Non-conformité signalée. Les responsables ont été notifiés.');
    }

    /**
     * Vérifier l'accès d'un superviseur client selon son périmètre (base_id ou client_id)
     */
    private function checkSuperviseurClientAccess($user, $demande): bool
    {
        if (!$user->isSuperviseurClient()) {
            return false;
        }

        // Si le superviseur n'a ni base_id ni client_id configurés, autoriser l'accès
        if (!$user->base_id && !$user->client_id) {
            return true;
        }

        // 1. Accès via base_id direct
        if ($user->base_id && $demande->base_id && $user->base_id == $demande->base_id) {
            return true;
        }

        // 2. Accès via client_id direct sur l'utilisateur
        if ($user->client_id && $demande->client_id && $user->client_id == $demande->client_id) {
            return true;
        }

        // 3. Accès via le client_id de la base de l'utilisateur
        $userClientFromBase = $user->baseSite ? $user->baseSite->client_id : null;
        if ($userClientFromBase && $demande->client_id && $userClientFromBase == $demande->client_id) {
            return true;
        }

        return false;
    }
}
