<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use App\Models\User;
use Illuminate\Http\Request;

class EquipeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 🆕 Auto-synchronisation des techniciens orphelins (ex: créés avec equipe_id mais pas encore dans equipe_user)
        $unlinkedUsers = User::whereNotNull('equipe_id')
            ->whereIn('type_utilisateur', ['technicien', 'chef technicien'])
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('equipe_user')
                    ->whereColumn('equipe_user.user_id', 'utilisateurs.id')
                    ->whereColumn('equipe_user.equipe_id', 'utilisateurs.equipe_id');
            })
            ->get();

        foreach ($unlinkedUsers as $unlinked) {
            $isChef = Equipe::where('id', $unlinked->equipe_id)
                ->where('chef_equipe', $unlinked->id)
                ->exists();
            $unlinked->equipes()->attach($unlinked->equipe_id, ['role' => $isChef ? 'chef' : 'membre']);
        }

        $equipes = Equipe::with(['chef', 'membres', 'chefs', 'membresSimples'])->get();
        $totalCount = Equipe::count();
        
        // Charger uniquement les TECHNICIENS pour le formulaire d'ajout
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])
            ->with(['equipes', 'equipesEnTantQueChef'])
            ->get();

        // Indiquer si l'utilisateur peut modifier (admin uniquement)
        $canModify = $user->type_utilisateur === 'admin';

        return view('equipes.index', compact('equipes', 'totalCount', 'techniciens', 'canModify'));
    }

    public function create()
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut créer des équipes.');
        }
        
        // Charger uniquement les TECHNICIENS (pas les superviseurs ni admins)
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])
            ->with(['equipes', 'equipesEnTantQueChef'])
            ->get();

        return view('equipes.create', compact('techniciens'));
    }

    public function store(Request $request)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut créer des équipes.');
        }
        
        $validated = $request->validate([
            'nom_equipe' => 'required|string|max:50',
            'chef_equipe' => 'nullable|integer',
            'membres' => 'nullable|array',
        ]);

        $equipe = Equipe::create([
            'nom_equipe' => $validated['nom_equipe'],
            'chef_equipe' => $validated['chef_equipe'] ?? null,
        ]);

        // Ajouter les membres simples (avec rôle 'membre')
        if ($request->has('membres') && is_array($request->membres)) {
            foreach ($request->membres as $membreId) {
                $equipe->membres()->attach($membreId, ['role' => 'membre']);
            }
        }
        
        // Ajouter automatiquement le chef comme membre avec le rôle 'chef'
        if ($validated['chef_equipe']) {
            // Vérifier si le chef n'est pas déjà dans les membres
            if (!$equipe->membres()->where('user_id', $validated['chef_equipe'])->exists()) {
                $equipe->membres()->attach($validated['chef_equipe'], ['role' => 'chef']);
            }
        }

        return redirect()->route('equipes.index')->with('success', 'Équipe créée avec succès !');
    }

    public function show($id)
    {
        $equipe = Equipe::with(['chef', 'membres'])->findOrFail($id);
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->get();
        
        // Indiquer si l'utilisateur peut modifier (admin uniquement)
        $canModify = auth()->user()->type_utilisateur === 'admin';
        
        return view('equipes.show', compact('equipe', 'techniciens', 'canModify'));
    }

    public function edit($id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut modifier les équipes.');
        }
        
        $equipe = Equipe::with(['chef', 'membres', 'chefs', 'membresSimples'])->findOrFail($id);
        
        // Charger uniquement les TECHNICIENS (pas les superviseurs ni admins)
        $techniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])
            ->with(['equipes', 'equipesEnTantQueChef'])
            ->get();

        return view('equipes.edit', compact('equipe', 'techniciens'));
    }

    public function update(Request $request, $id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut modifier les équipes.');
        }
        
        $equipe = Equipe::findOrFail($id);

        $validated = $request->validate([
            'nom_equipe' => 'required|string|max:50',
            'chef_equipe' => 'nullable|integer',
            'membres' => 'nullable|array',
        ]);

        $equipe->update([
            'nom_equipe' => $validated['nom_equipe'],
            'chef_equipe' => $validated['chef_equipe'] ?? null,
        ]);

        // Détacher tous les membres actuels
        $equipe->membres()->detach();

        // Rattacher les membres
        if ($request->has('membres') && is_array($request->membres)) {
            foreach ($request->membres as $membreId) {
                $equipe->membres()->attach($membreId, ['role' => 'membre']);
            }
        }
        
        // Ajouter automatiquement le chef comme membre avec le rôle 'chef'
        if ($validated['chef_equipe']) {
            // Vérifier si le chef n'est pas déjà dans les membres
            if (!$equipe->membres()->where('user_id', $validated['chef_equipe'])->exists()) {
                $equipe->membres()->attach($validated['chef_equipe'], ['role' => 'chef']);
            } else {
                // Mettre à jour son rôle en 'chef' s'il était déjà membre
                $equipe->membres()->updateExistingPivot($validated['chef_equipe'], ['role' => 'chef']);
            }
        }

        return redirect()->route('equipes.index')->with('success', 'Équipe mise à jour avec succès !');
    }

    public function destroy($id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut supprimer les équipes.');
        }
        
        $equipe = Equipe::findOrFail($id);
        
        // La table pivot se vide automatiquement grâce à onDelete('cascade') dans les anciennes versions
        // Mais on le fait manuellement pour être sûr
        $equipe->membres()->detach();
        
        $equipe->delete();

        return redirect()->route('equipes.index')->with('success', 'Équipe supprimée avec succès !');
    }

    public function addMember(Request $request, $id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut modifier les équipes.');
        }
        
        $request->validate([
            'user_id' => 'required|integer|exists:utilisateurs,id',
        ]);

        $equipe = Equipe::findOrFail($id);
        
        // Vérifier si déjà membre
        if ($equipe->membres()->where('user_id', $request->user_id)->exists()) {
            return redirect()->route('equipes.index')->with('info', 'Cette personne est déjà dans l\'équipe.');
        }
        
        // Toujours ajouter comme membre
        $equipe->membres()->attach($request->user_id, ['role' => 'membre']);

        // Mettre à jour equipe_id sur l'utilisateur s'il n'en a pas
        User::where('id', $request->user_id)->whereNull('equipe_id')->update(['equipe_id' => $id]);

        return redirect()->route('equipes.index')->with('success', 'Membre ajouté à l\'équipe avec succès !');
    }

    public function removeMember($equipe_id, $user_id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut modifier les équipes.');
        }
        
        $equipe = Equipe::findOrFail($equipe_id);
        
        // Si c'est le chef qu'on retire, retirer aussi le statut de chef
        if ($equipe->chef_equipe == $user_id) {
            $equipe->chef_equipe = null;
            $equipe->save();
        }
        
        // Retirer de la table pivot
        $equipe->membres()->detach($user_id);

        // Si l'utilisateur avait cette equipe_id, synchroniser vers sa prochaine équipe ou null
        $userMembre = User::with('equipes')->find($user_id);
        if ($userMembre && $userMembre->equipe_id == $equipe_id) {
            $next = $userMembre->equipes->first();
            $userMembre->equipe_id = $next ? $next->id : null;
            $userMembre->save();
        }

        return redirect()->route('equipes.index')->with('success', 'Membre retiré de l\'équipe.');
    }

    public function setChef($equipe_id, $user_id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut modifier les équipes.');
        }
        
        $equipe = Equipe::findOrFail($equipe_id);
        $user = User::findOrFail($user_id);

        if (!$user->isTechnicien()) {
            return redirect()->route('equipes.index')->with('error', 'Seuls les techniciens peuvent être chefs d\'équipe.');
        }

        $equipe->chef_equipe = $user_id;
        $equipe->save();

        if (!$equipe->membres()->where('user_id', $user_id)->exists()) {
            $equipe->membres()->attach($user_id, ['role' => 'chef']);
        } else {
            $equipe->membres()->updateExistingPivot($user_id, ['role' => 'chef']);
        }

        return redirect()->route('equipes.index')->with('success', $user->nom_complet . ' est désormais le chef de l\'équipe ' . $equipe->nom_equipe . ' !');
    }

    public function unsetChef($equipe_id)
    {
        // Bloquer si pas admin
        if (auth()->user()->type_utilisateur !== 'admin') {
            abort(403, 'Seul l\'administrateur peut modifier les équipes.');
        }
        
        $equipe = Equipe::findOrFail($equipe_id);

        $ancienChefId = $equipe->chef_equipe;
        if ($ancienChefId && $equipe->membres()->where('user_id', $ancienChefId)->exists()) {
            $equipe->membres()->updateExistingPivot($ancienChefId, ['role' => 'membre']);
        }

        $equipe->chef_equipe = null;
        $equipe->save();

        return redirect()->route('equipes.index')->with('success', 'Le chef d\'équipe a été retiré. Les membres restent dans l\'équipe en tant que techniciens.');
    }
}
