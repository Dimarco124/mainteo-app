<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DemandeurController extends Controller
{
    /**
     * Liste des demandeurs (Superviseur Client uniquement)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Vérifier que c'est bien un superviseur client
        if (!$user->isSuperviseurClient()) {
            abort(403, 'Accès réservé aux superviseurs clients.');
        }

        // Récupérer les demandeurs de SA base uniquement
        $query = User::with(['sitesAssignes', 'baseSite'])
            ->where('type_utilisateur', 'demandeur')
            ->where('base_id', $user->base_id);

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $demandeurs = $query->orderBy('nom')->paginate(15);

        return view('demandeurs.index', compact('demandeurs'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403);
        }

        // Récupérer uniquement les sites de SA base ou de SON client
        $sites = $this->getSitesForSuperviseur($user);

        return view('demandeurs.create', compact('sites'));
    }

    /**
     * Enregistrer un nouveau demandeur
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403);
        }

        $validated = $request->validate([
            'nom'          => 'required|string|max:100',
            'prenom'       => 'nullable|string|max:100',
            'email'        => 'required|email|unique:utilisateurs,email',
            'mot_de_passe' => 'required|string|min:4',
            'telephone'    => 'required|string|max:50',
            'sites'        => 'required|array|min:1',
            'sites.*'      => 'exists:sites,id',
        ]);

        // Vérifier que tous les sites appartiennent au scope du superviseur
        $allowedSiteIds = $this->getSitesForSuperviseur($user)->pluck('id')->toArray();
        $invalidSites = array_diff($validated['sites'], $allowedSiteIds);

        if (count($invalidSites) > 0) {
            return back()->withErrors([
                'sites' => 'Vous ne pouvez assigner que des sites de votre entreprise ou base.'
            ])->withInput();
        }

        // Créer le demandeur
        $demandeur = User::create([
            'nom'              => $validated['nom'],
            'prenom'           => $validated['prenom'],
            'email'            => $validated['email'],
            'mot_de_passe'     => Hash::make($validated['mot_de_passe']),
            'type_utilisateur' => 'demandeur',
            'telephone'        => $validated['telephone'],
            'base_id'          => $user->base_id,
            'client_id'        => $user->client_id ?? ($user->baseSite ? $user->baseSite->client_id : null),
            'statut'           => 'actif',
            'created_by'       => $user->id,
        ]);

        // Attacher les sites sélectionnés
        $demandeur->sitesAssignes()->attach($validated['sites']);

        return redirect()->route('demandeurs.index')
            ->with('success', "Demandeur {$demandeur->nom_complet} créé avec succès et assigné à " . count($validated['sites']) . " site(s).");
    }

    private function getSitesForSuperviseur($user)
    {
        $query = Site::query();

        $baseId = $user->base_id;
        $clientId = $user->client_id;
        if (!$clientId && $baseId && $user->baseSite) {
            $clientId = $user->baseSite->client_id;
        }

        if ($baseId) {
            // Le superviseur client rattaché à une base voit EXCLUSIVEMENT les sites de SA base
            $query->where('base_id', $baseId);
        } elseif ($clientId) {
            // Le superviseur rattaché à une entreprise directe sans base voit les sites de son entreprise
            $query->where('client_id', $clientId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->orderBy('nom_site')->get();
    }

    /**
     * Afficher un demandeur
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403);
        }

        $demandeur = User::with(['sitesAssignes', 'baseSite', 'demandesCreees'])
            ->where('type_utilisateur', 'demandeur')
            ->where('base_id', $user->base_id)
            ->findOrFail($id);

        return view('demandeurs.show', compact('demandeur'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403);
        }

        $demandeur = User::with('sitesAssignes')
            ->where('type_utilisateur', 'demandeur')
            ->where('base_id', $user->base_id)
            ->findOrFail($id);

        // Sites de SA base ou de SON client
        $sites = $this->getSitesForSuperviseur($user);

        return view('demandeurs.edit', compact('demandeur', 'sites'));
    }

    /**
     * Mettre à jour un demandeur
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403);
        }

        $demandeur = User::where('type_utilisateur', 'demandeur')
            ->where('base_id', $user->base_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'nullable|string|max:100',
            'email'     => 'required|email|unique:utilisateurs,email,' . $id,
            'telephone' => 'required|string|max:50',
            'statut'    => 'required|in:actif,inactif',
            'sites'     => 'required|array|min:1',
            'sites.*'   => 'exists:sites,id',
        ]);

        // Vérifier que tous les sites appartiennent à SA base
        $invalidSites = Site::whereIn('id', $validated['sites'])
            ->where('base_id', '!=', $user->base_id)
            ->count();

        if ($invalidSites > 0) {
            return back()->withErrors([
                'sites' => 'Vous ne pouvez assigner que des sites de votre base.'
            ])->withInput();
        }

        // Optionnel : changer le mot de passe
        if ($request->filled('mot_de_passe')) {
            $request->validate(['mot_de_passe' => 'string|min:4']);
            $validated['mot_de_passe'] = Hash::make($request->mot_de_passe);
        }

        // Mettre à jour
        $demandeur->update($validated);

        // Synchroniser les sites (remplace les anciens par les nouveaux)
        $demandeur->sitesAssignes()->sync($validated['sites']);

        return redirect()->route('demandeurs.index')
            ->with('success', "Demandeur {$demandeur->nom_complet} mis à jour avec succès.");
    }

    /**
     * Supprimer un demandeur
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->isSuperviseurClient()) {
            abort(403);
        }

        $demandeur = User::where('type_utilisateur', 'demandeur')
            ->where('base_id', $user->base_id)
            ->findOrFail($id);

        // Vérifier s'il a des demandes en cours
        $demandesEnCours = $demandeur->demandesCreees()
            ->whereNotIn('statut', ['closed', 'rejected_by_client', 'rejected_by_soutarah'])
            ->count();

        if ($demandesEnCours > 0) {
            return back()->with('error', "Impossible de supprimer ce demandeur : {$demandesEnCours} demande(s) en cours.");
        }

        $name = $demandeur->nom_complet;
        $demandeur->delete();

        return redirect()->route('demandeurs.index')
            ->with('success', "Demandeur {$name} supprimé avec succès.");
    }
}
