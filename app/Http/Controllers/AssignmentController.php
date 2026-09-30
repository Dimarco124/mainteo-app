<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\User;
use App\Models\BaseSite;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    /**
     * Liste des assignments
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $assignments = Assignment::with(['superviseurSoutarah', 'base.client', 'client'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $superviseursSoutarah = User::where('type_utilisateur', 'superviseur_soutarah')
            ->whereDoesntHave('assignment')
            ->orderBy('nom')
            ->get();

        // POUR LE MODAL DE CRÉATION : Bases/clients non assignés
        $basesDisponibles = BaseSite::with('client')
            ->whereDoesntHave('assignment')
            ->orderBy('nom_base')
            ->get();

        $clientsDisponibles = Client::whereDoesntHave('bases')
            ->whereDoesntHave('assignment')
            ->orderBy('nom')
            ->get();

        // POUR LE MODAL D'ÉDITION : TOUTES les bases et clients
        $bases = BaseSite::with('client')
            ->orderBy('nom_base')
            ->get();

        $clients = Client::whereDoesntHave('bases')
            ->orderBy('nom')
            ->get();

        return view('assignments.index', compact(
            'assignments', 
            'superviseursSoutarah', 
            'bases', 
            'clients',
            'basesDisponibles',
            'clientsDisponibles'
        ));
    }

    /**
     * Créer un assignment
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'superviseur_soutarah_id' => 'required|exists:utilisateurs,id|unique:assignments,superviseur_soutarah_id',
            'assignment_type' => 'required|in:base,client',
            'base_id' => 'required_if:assignment_type,base|nullable|exists:bases,id',
            'client_id' => 'required_if:assignment_type,client|nullable|exists:clients,id',
        ]);

        // Vérifier que le superviseur a bien le bon type
        $superviseur = User::findOrFail($validated['superviseur_soutarah_id']);
        if (!$superviseur->isSuperviseurSoutarah()) {
            return back()->with('error', 'L\'utilisateur sélectionné n\'est pas un Superviseur Soutarah.');
        }

        Assignment::create([
            'superviseur_soutarah_id' => $validated['superviseur_soutarah_id'],
            'base_id' => $validated['assignment_type'] == 'base' ? $validated['base_id'] : null,
            'client_id' => $validated['assignment_type'] == 'client' ? $validated['client_id'] : null,
        ]);

        return redirect()->route('assignments.index')
            ->with('success', 'Affectation créée avec succès.');
    }

    /**
     * Supprimer un assignment
     */
    public function destroy(Assignment $assignment)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Affectation supprimée avec succès.');
    }

    /**
     * Modifier un assignment
     */
    public function update(Request $request, Assignment $assignment)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'assignment_type' => 'required|in:base,client',
            'base_id' => 'required_if:assignment_type,base|nullable|exists:bases,id',
            'client_id' => 'required_if:assignment_type,client|nullable|exists:clients,id',
        ]);

        $assignment->update([
            'base_id' => $validated['assignment_type'] == 'base' ? $validated['base_id'] : null,
            'client_id' => $validated['assignment_type'] == 'client' ? $validated['client_id'] : null,
        ]);

        return redirect()->route('assignments.index')
            ->with('success', 'Affectation modifiée avec succès.');
    }
}
