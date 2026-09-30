<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TechnicienController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('type_utilisateur', ['technicien', 'chef technicien']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialite', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $techniciens = $query->with('equipe')->orderBy('nom', 'asc')->paginate(15);
        $totalTechniciens = User::whereIn('type_utilisateur', ['technicien', 'chef technicien'])->count();

        return view('techniciens.index', compact('techniciens', 'totalTechniciens'));
    }

    public function create()
    {
        $equipes = Equipe::all();
        return view('techniciens.create', compact('equipes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'email' => 'required|email|unique:utilisateurs,email',
            'mot_de_passe' => 'required|string|min:6',
            'telephone' => 'nullable|string|max:50',
            'specialite' => 'nullable|string|max:100',
            'equipe_id' => 'nullable|integer',
        ]);

        $user = new User($validated);
        $user->type_utilisateur = 'technicien';
        $user->mot_de_passe = Hash::make($validated['mot_de_passe']);
        $user->statut = 'actif';
        $user->save();

        return redirect()->route('techniciens.index')->with('success', 'Technicien ajouté avec succès !');
    }
}
