<?php

namespace App\Http\Controllers;

use App\Models\CompteRenduJournalier;
use App\Models\Maintenance;
use App\Models\InterventionNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompteRenduJournalierController extends Controller
{
    /**
     * Formulaire de création d'un Compte Rendu Journalier
     */
    public function create($maintenanceId)
    {
        $maintenance = Maintenance::with(['client', 'site', 'equipe.membres', 'equipe.chef', 'equipes.membres', 'equipes.chef', 'technicien'])->findOrFail($maintenanceId);
        $user = Auth::user();

        // Seul le chef d'équipe peut saisir un compte rendu
        if (!$user->isChefTechnicien()) {
            return redirect()->route('maintenances.show', $maintenance->id)
                ->with('error', 'Seul le chef d\'équipe peut saisir un compte rendu journalier.');
        }

        // Trouver l'équipe dont l'utilisateur est le chef parmi les équipes affectées
        $userEquipe = null;
        if ($maintenance->equipe && $maintenance->equipe->chef_equipe == $user->id) {
            $userEquipe = $maintenance->equipe;
        } elseif ($maintenance->equipes && $maintenance->equipes->count() > 0) {
            $userEquipe = $maintenance->equipes->first(fn($eq) => $eq->chef_equipe == $user->id);
        }

        if (!$userEquipe) {
            return redirect()->route('maintenances.show', $maintenance->id)
                ->with('error', 'Vous devez être le chef d\'une des équipes affectées à cette maintenance.');
        }

        // Pré-remplir la liste des intervenants
        $intervenantsNames = [];
        if ($userEquipe->membres && $userEquipe->membres->count() > 0) {
            foreach ($userEquipe->membres as $m) {
                $intervenantsNames[] = $m->nom_complet ?? $m->nom;
            }
        } elseif ($maintenance->technicien) {
            $intervenantsNames[] = $maintenance->technicien->nom_complet ?? $maintenance->technicien->nom;
        }

        $nomsIntervenantsDefault = implode(', ', $intervenantsNames);

        // Nom du responsable d'équipe
        $responsableDefault = $user->nom_complet;

        return view('comptes_rendus.create', compact('maintenance', 'userEquipe', 'nomsIntervenantsDefault', 'responsableDefault'));
    }

    /**
     * Enregistrer le Compte Rendu Journalier
     */
    public function store(Request $request, $maintenanceId)
    {
        $maintenance = Maintenance::with(['equipe', 'equipes'])->findOrFail($maintenanceId);
        $user = Auth::user();

        // Seul le chef d'équipe peut enregistrer un compte rendu
        if (!$user->isChefTechnicien()) {
            return redirect()->route('maintenances.show', $maintenance->id)
                ->with('error', 'Seul le chef d\'équipe peut saisir un compte rendu journalier.');
        }

        // Trouver l'équipe dont l'utilisateur est le chef parmi les équipes affectées
        $userEquipe = null;
        if ($maintenance->equipe && $maintenance->equipe->chef_equipe == $user->id) {
            $userEquipe = $maintenance->equipe;
        } elseif ($maintenance->equipes && $maintenance->equipes->count() > 0) {
            $userEquipe = $maintenance->equipes->first(fn($eq) => $eq->chef_equipe == $user->id);
        }

        if (!$userEquipe) {
            return redirect()->route('maintenances.show', $maintenance->id)
                ->with('error', 'Vous devez être le chef d\'une des équipes affectées à cette maintenance.');
        }

        $validated = $request->validate([
            'date_rapport' => 'required|date',
            'noms_intervenants' => 'nullable|string|max:255',
            'activites_realisees' => 'required|string',
            'nombre_equipements_traites' => 'required|integer|min:0',
            'anomalies_constatees' => 'nullable|string',
            'difficultes_rencontrees' => 'nullable|string',
            'materiel_utilise' => 'nullable|string',
            'travaux_non_termines' => 'nullable|string',
            'heure_fin' => 'nullable|string|max:50',
            'nom_responsable' => 'nullable|string|max:255',
        ]);

        $rapport = CompteRenduJournalier::create([
            'maintenance_id' => $maintenance->id,
            'site_id' => $maintenance->site_id,
            'equipe_id' => $userEquipe->id,
            'responsable_id' => $user->id,
            'date_rapport' => $validated['date_rapport'],
            'noms_intervenants' => $validated['noms_intervenants'] ?? null,
            'activites_realisees' => $validated['activites_realisees'],
            'nombre_equipements_traites' => $validated['nombre_equipements_traites'],
            'anomalies_constatees' => $validated['anomalies_constatees'] ?? null,
            'difficultes_rencontrees' => $validated['difficultes_rencontrees'] ?? null,
            'materiel_utilise' => $validated['materiel_utilise'] ?? null,
            'travaux_non_termines' => $validated['travaux_non_termines'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'nom_responsable' => $validated['nom_responsable'] ?? $user->nom_complet,
            'created_by_user_id' => $user->id,
        ]);

        // Recalculer le pourcentage d'avancement de la maintenance
        $nouveauPourcentage = $maintenance->recalculerAvancement();

        // Notifier le créateur de la maintenance et les superviseurs
        $superviseurs = User::whereIn('type_utilisateur', ['admin', 'superviseur_soutarah'])->get();
        foreach ($superviseurs as $sup) {
            InterventionNotification::create([
                'user_id' => $sup->id,
                'maintenance_id' => $maintenance->id,
                'type' => 'compte_rendu_soumis',
                'message' => "Compte rendu journalier soumis pour " . $maintenance->numero_maintenance . " (" . $validated['nombre_equipements_traites'] . " éq. traités). Avancement: " . $nouveauPourcentage . "%",
                'statut' => 'non_lu',
            ]);
        }

        return redirect()->route('maintenances.show', $maintenance->id)
            ->with('success', 'Compte rendu journalier enregistré avec succès ! Avancement mis à jour : ' . $nouveauPourcentage . '%');
    }

    /**
     * Consulter un compte rendu journalier
     */
    public function show($id)
    {
        $rapport = CompteRenduJournalier::with(['maintenance.client', 'maintenance.site', 'site', 'equipe', 'responsable', 'createdBy'])->findOrFail($id);
        return view('comptes_rendus.show', compact('rapport'));
    }

    /**
     * Supprimer un compte rendu journalier
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperviseurSoutarah()) {
            abort(403, 'Action non autorisée');
        }

        $rapport = CompteRenduJournalier::findOrFail($id);
        $maintenance = $rapport->maintenance;
        $rapport->delete();

        if ($maintenance) {
            $maintenance->recalculerAvancement();
        }

        return redirect()->route('maintenances.show', $maintenance->id)
            ->with('success', 'Compte rendu journalier supprimé. Avancement réinterrogé.');
    }
}
