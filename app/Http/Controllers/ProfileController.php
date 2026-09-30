<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:utilisateurs,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Changer le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Vérifier l'ancien mot de passe
        if (!Hash::check($validated['current_password'], $user->mot_de_passe)) {
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        // Mettre à jour le mot de passe
        $user->update([
            'mot_de_passe' => Hash::make($validated['new_password'])
        ]);

        return redirect()->route('profile.show')->with('success', 'Mot de passe changé avec succès !');
    }

    /**
     * Afficher la page des paramètres
     */
    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * Mettre à jour le thème (mode sombre)
     */
    public function updateTheme(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'dark_mode' => 'required|boolean',
        ]);

        $user->update(['dark_mode' => $validated['dark_mode']]);

        return response()->json([
            'success' => true,
            'message' => 'Thème mis à jour avec succès',
            'dark_mode' => $user->dark_mode
        ]);
    }
}
