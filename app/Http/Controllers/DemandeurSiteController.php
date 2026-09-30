<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeurSiteController extends Controller
{
    /**
     * Afficher le formulaire d'assignation pour un demandeur
     */
    public function edit($demandeurId)
    {
        $user = Auth::user();
        
        // Seul l'admin peut assigner des sites
        if (!$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
        
        $demandeur = User::where('type_utilisateur', 'demandeur')
            ->with('sitesAssignes')
            ->findOrFail($demandeurId);
        
        // Récupérer tous les sites disponibles
        $allSites = Site::with('baseSite')->orderBy('nom_site')->get();
        
        // IDs des sites déjà assignés
        $assignedSiteIds = $demandeur->sitesAssignes->pluck('id')->toArray();
        
        return view('demandeurs.assign-sites', compact('demandeur', 'allSites', 'assignedSiteIds'));
    }
    
    /**
     * Mettre à jour les sites assignés à un demandeur
     */
    public function update(Request $request, $demandeurId)
    {
        $user = Auth::user();
        
        // Seul l'admin peut assigner des sites
        if (!$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
        
        $demandeur = User::where('type_utilisateur', 'demandeur')->findOrFail($demandeurId);
        
        $validated = $request->validate([
            'site_ids' => 'nullable|array',
            'site_ids.*' => 'exists:sites,id',
        ]);
        
        // Synchroniser les sites assignés
        $siteIds = $validated['site_ids'] ?? [];
        $demandeur->sitesAssignes()->sync($siteIds);
        
        return redirect()->route('users.index')
            ->with('success', "Sites assignés à {$demandeur->nom_complet} avec succès ! ({$demandeur->sitesAssignes()->count()} site(s))");
    }
    
    /**
     * API : Obtenir les sites assignés à un demandeur
     */
    public function getSites($demandeurId)
    {
        $demandeur = User::where('type_utilisateur', 'demandeur')->findOrFail($demandeurId);
        $sites = $demandeur->sitesAssignes()->with('baseSite')->get();
        
        return response()->json($sites);
    }
}
