<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Client;
use App\Models\Equipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['client', 'baseSite', 'equipe', 'equipes', 'equipesEnTantQueChef']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('type_utilisateur', 'like', "%{$search}%");
            });
        }

        $categorie = $request->input('categorie', 'all');
        if ($categorie === 'soutarah') {
            $query->whereIn('type_utilisateur', ['admin', 'superviseur_soutarah', 'technicien', 'chef technicien']);
        } elseif ($categorie === 'client') {
            $query->whereIn('type_utilisateur', ['superviseur_client', 'demandeur']);
        }

        if ($role = $request->input('role')) {
            $query->where('type_utilisateur', $role);
        }

        $users      = $query->orderBy('id', 'desc')->paginate(15);
        $totalCount = User::count();

        // Statistiques
        $totalSoutarah = User::whereIn('type_utilisateur', ['admin', 'superviseur_soutarah', 'technicien', 'chef technicien'])->count();
        $totalClient = User::whereIn('type_utilisateur', ['superviseur_client', 'demandeur'])->count();

        // Statistiques : Total des bases
        $totalBasesCount = BaseSite::count();

        // Clients sans superviseur Soutarah
        $clientsSansAssign = Client::whereDoesntHave('bases')
            ->whereNotIn('id', function ($q) {
                $q->select('client_id')
                    ->from('assignments')
                    ->whereNotNull('client_id');
            })->count();

        return view('utilisateurs.index', compact(
            'users',
            'totalCount',
            'totalSoutarah',
            'totalClient',
            'totalBasesCount',
            'clientsSansAssign',
            'categorie'
        ));
    }

    public function create()
    {
        // Liste des clients et bases pour création superviseur
        $clients = Client::orderBy('nom')->get();
        
        // Toutes les bases disponibles (plusieurs superviseurs clients autorisés par base)
        $bases = BaseSite::with('client')
            ->orderBy('nom_base')
            ->get();
            
        $sites   = \App\Models\Site::with('baseSite.client')->orderBy('nom_site')->get();
        $equipes = Equipe::orderBy('nom_equipe')->get();

        $selectedClientId = request('client_id');
        $selectedBaseId   = request('base_id');

        return view('utilisateurs.create', compact(
            'clients',
            'bases',
            'sites',
            'equipes',
            'selectedClientId',
            'selectedBaseId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'nullable|string|max:100',
            'email'            => 'required|email|unique:utilisateurs,email',
            'type_utilisateur' => 'required|string|in:superviseur_client,superviseur_soutarah,demandeur,technicien',
            'mot_de_passe'     => 'required|string|min:4',
            'telephone'        => 'nullable|string|max:50',
            'specialite'       => 'nullable|string|max:100',
            'client_id'        => 'nullable|integer',
            'base_id'          => 'nullable|integer',
            'site_id'          => 'nullable|integer',
            'site_ids'         => 'nullable|array',  // 🆕 NOUVEAU : pour multi-sites demandeurs
            'site_ids.*'       => 'exists:sites,id', // 🆕 NOUVEAU : validation de chaque site_id
            'equipe_id'        => 'nullable|integer',
        ]);

        // Validation conditionnelle : Superviseur Client DOIT avoir SOIT base_id SOIT client_id
        if ($validated['type_utilisateur'] === 'superviseur_client') {
            if (empty($validated['base_id']) && empty($validated['client_id'])) {
                return back()->withErrors([
                    'base_id' => 'Un superviseur client doit être rattaché à une base (grande entreprise) OU directement à une entreprise (petite entreprise sans bases).'
                ])->withInput();
            }
            
            // Si base_id fourni, récupérer automatiquement le client_id de la base
            if (!empty($validated['base_id'])) {
                $base = BaseSite::find($validated['base_id']);
                if ($base) {
                    $validated['client_id'] = $base->client_id;
                }
            }
            // Sinon, on garde juste le client_id (petite entreprise sans bases)
        }

        // 🆕 Validation conditionnelle : Demandeur DOIT avoir au moins UN site assigné
        if ($validated['type_utilisateur'] === 'demandeur') {
            if (empty($validated['site_ids']) || count($validated['site_ids']) === 0) {
                return back()->withErrors(['site_ids' => 'Un demandeur doit avoir au moins un site assigné.'])->withInput();
            }
            
            // Récupérer base_id et client_id depuis le PREMIER site assigné
            $firstSite = \App\Models\Site::with('baseSite')->find($validated['site_ids'][0]);
            if ($firstSite && $firstSite->baseSite) {
                $validated['base_id'] = $firstSite->baseSite->id;
                $validated['client_id'] = $firstSite->baseSite->client_id;
            }
            
            // ⚠️ Ne pas définir site_id (ancien système), on utilisera demandeur_site
            unset($validated['site_id']);
        }

        $validated['mot_de_passe'] = Hash::make($validated['mot_de_passe']);
        $validated['statut']       = 'actif';

        // Extraire site_ids avant de créer l'utilisateur
        $siteIds = $validated['site_ids'] ?? [];
        unset($validated['site_ids']); // Ne pas inclure dans les attributs du User

        $user = User::create($validated);

        // 🆕 SYNCHRONISER les sites assignés pour les demandeurs
        if ($user->type_utilisateur === 'demandeur' && !empty($siteIds)) {
            $user->sitesAssignes()->sync($siteIds);
        }

        // 🆕 SYNCHRONISER l'équipe dans la table pivot equipe_user pour les techniciens
        if ($user->isTechnicien() && !empty($validated['equipe_id'])) {
            $user->equipes()->sync([$validated['equipe_id'] => ['role' => 'membre']]);
        }

        $message = 'Compte créé avec succès pour ' . $user->nom_complet . ' !';
        
        // Message supplémentaire pour superviseur Soutarah
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $message .= ' N\'oubliez pas d\'assigner ce superviseur à une base ou entreprise dans le menu Affectations.';
        }
        
        // Message supplémentaire pour demandeur
        if ($user->type_utilisateur === 'demandeur') {
            $message .= ' (' . count($siteIds) . ' site(s) assigné(s))';
        }

        return redirect()->route('utilisateurs.index')
            ->with('success', $message);
    }

    public function show($id)
    {
        $user = User::with(['client', 'baseSite', 'equipe', 'depannages'])->findOrFail($id);
        return view('utilisateurs.show', compact('user'));
    }

    public function edit($id)
    {
        $authUser = Auth::user();
        $user    = User::with(['assignment', 'sitesAssignes'])->findOrFail($id); // 🆕 Ajouter sitesAssignes
        $clients = Client::orderBy('nom')->get();
        
        // Toutes les bases disponibles (plusieurs superviseurs clients autorisés par base)
        $bases = BaseSite::with('client')
            ->orderBy('nom_base')
            ->get();
            
        $sites   = $this->getSitesForUser($authUser);
        $equipes = Equipe::orderBy('nom_equipe')->get();

        return view('utilisateurs.edit', compact('user', 'clients', 'bases', 'sites', 'equipes'));
    }

    private function getSitesForUser($user)
    {
        $query = \App\Models\Site::query();

        if ($user && $user->isSuperviseurClient()) {
            $baseId = $user->base_id;
            $clientId = $user->client_id;
            if (!$clientId && $baseId && $user->baseSite) {
                $clientId = $user->baseSite->client_id;
            }

            $query->where(function ($q) use ($baseId, $clientId) {
                if ($baseId) {
                    $q->orWhere('base_id', $baseId);
                }
                if ($clientId) {
                    $q->orWhere('client_id', $clientId);
                    $q->orWhereHas('baseSite', function ($bq) use ($clientId) {
                        $bq->where('client_id', $clientId);
                    });
                }
                if (!$baseId && !$clientId) {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        return $query->with('baseSite.client')->orderBy('nom_site')->get();
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Ne permettre 'admin' dans la validation QUE si l'utilisateur est déjà admin
        $allowedRoles = ['superviseur_client', 'superviseur_soutarah', 'demandeur', 'technicien'];
        
        if ($user->type_utilisateur === 'admin') {
            $allowedRoles[] = 'admin'; // Permettre de garder admin si déjà admin
        }
        
        if ($user->type_utilisateur === 'chef technicien') {
            $allowedRoles[] = 'chef technicien'; // Legacy
        }

        $validated = $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'nullable|string|max:100',
            'email'            => 'required|email|unique:utilisateurs,email,' . $id,
            'type_utilisateur' => 'required|string|in:' . implode(',', $allowedRoles),
            'telephone'        => 'nullable|string|max:50',
            'specialite'       => 'nullable|string|max:100',
            'client_id'        => 'nullable|integer',
            'base_id'          => 'nullable|integer',
            'site_id'          => 'nullable|integer',
            'site_ids'         => 'nullable|array',  // 🆕 NOUVEAU
            'site_ids.*'       => 'exists:sites,id', // 🆕 NOUVEAU
            'equipe_id'        => 'nullable|integer',
            'statut'           => 'nullable|string|in:actif,inactif',
        ]);

        // Bloquer toute tentative de changer un non-admin vers admin
        if ($validated['type_utilisateur'] === 'admin' && $user->type_utilisateur !== 'admin') {
            return back()->withErrors([
                'type_utilisateur' => 'Vous ne pouvez pas promouvoir un compte vers Admin. Les comptes admin sont créés manuellement en base de données.'
            ])->withInput();
        }

        // Validation conditionnelle : Superviseur Client DOIT avoir SOIT base_id SOIT client_id
        if ($validated['type_utilisateur'] === 'superviseur_client') {
            if (empty($validated['base_id']) && empty($validated['client_id'])) {
                return back()->withErrors([
                    'base_id' => 'Un superviseur client doit être rattaché à une base (grande entreprise) OU directement à une entreprise (petite entreprise sans bases).'
                ])->withInput();
            }
            
            // Si base_id fourni, récupérer automatiquement le client_id de la base
            if (!empty($validated['base_id'])) {
                $base = BaseSite::find($validated['base_id']);
                if ($base) {
                    $validated['client_id'] = $base->client_id;
                }
            }
            // Sinon, on garde juste le client_id (petite entreprise sans bases)
        }

        // 🆕 Validation conditionnelle : Demandeur DOIT avoir au moins UN site assigné
        if ($validated['type_utilisateur'] === 'demandeur') {
            if (empty($validated['site_ids']) || count($validated['site_ids']) === 0) {
                return back()->withErrors(['site_ids' => 'Un demandeur doit avoir au moins un site assigné.'])->withInput();
            }
            
            // Récupérer base_id et client_id depuis le PREMIER site assigné
            $firstSite = \App\Models\Site::with('baseSite')->find($validated['site_ids'][0]);
            if ($firstSite && $firstSite->baseSite) {
                $validated['base_id'] = $firstSite->baseSite->id;
                $validated['client_id'] = $firstSite->baseSite->client_id;
            }
            
            // Ne pas définir site_id (ancien système)
            unset($validated['site_id']);
        }

        // Si changement de rôle vers superviseur_soutarah, nettoyer base_id/site_id
        if ($validated['type_utilisateur'] === 'superviseur_soutarah') {
            $validated['base_id'] = null;
            $validated['site_id'] = null;
            // L'assignation se fera via la table assignments
        }

        // Si changement vers admin ou technicien, nettoyer base_id/site_id
        if (in_array($validated['type_utilisateur'], ['admin', 'technicien'])) {
            $validated['base_id'] = null;
            $validated['site_id'] = null;
        }

        // Si non-technicien, nettoyer equipe_id
        if (!in_array($validated['type_utilisateur'], ['technicien', 'chef technicien'])) {
            $validated['equipe_id'] = null;
        }

        if ($request->filled('mot_de_passe')) {
            $request->validate(['mot_de_passe' => 'string|min:4']);
            $validated['mot_de_passe'] = Hash::make($request->mot_de_passe);
        }

        // 🆕 Extraire site_ids avant de mettre à jour
        $siteIds = $validated['site_ids'] ?? [];
        unset($validated['site_ids']); // Ne pas inclure dans les attributs du User

        $user->update($validated);

        // 🆕 SYNCHRONISER les sites assignés pour les demandeurs
        if ($user->type_utilisateur === 'demandeur' && !empty($siteIds)) {
            $user->sitesAssignes()->sync($siteIds);
        }

        // 🆕 SYNCHRONISER l'équipe dans la table pivot equipe_user pour les techniciens
        if ($user->isTechnicien()) {
            if (!empty($validated['equipe_id'])) {
                $isChef = \App\Models\Equipe::where('id', $validated['equipe_id'])
                    ->where('chef_equipe', $user->id)
                    ->exists();
                $role = $isChef ? 'chef' : 'membre';
                $user->equipes()->sync([$validated['equipe_id'] => ['role' => $role]]);
            } else {
                $user->equipes()->detach();
            }
        } else {
            $user->equipes()->detach();
        }

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Compte ' . $user->nom_complet . ' mis à jour avec succès !' . ($user->type_utilisateur === 'demandeur' ? ' (' . count($siteIds) . ' site(s) assigné(s))' : ''));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $name = $user->nom_complet;
        $user->delete();

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Compte d\'accès de ' . $name . ' supprimé.');
    }
}
