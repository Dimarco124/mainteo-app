<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        if ($user->statut && $user->statut !== 'actif') {
            return back()->withErrors(['email' => 'Votre compte est inactif. Veuillez contacter un administrateur.'])->withInput();
        }

        // Vérification rétrocompatible des mots de passe (Bcrypt ou texte clair legacy)
        $passwordValid = false;
        if (str_starts_with($user->mot_de_passe, '$2y$') || str_starts_with($user->mot_de_passe, '$2a$')) {
            $passwordValid = Hash::check($request->mot_de_passe, $user->mot_de_passe);
        } else {
            // Mot de passe legacy en texte clair
            $passwordValid = ($request->mot_de_passe === $user->mot_de_passe);
            
            // Re-hasher en bcrypt pour la sécurité future si la connexion réussit
            if ($passwordValid) {
                $user->mot_de_passe = Hash::make($request->mot_de_passe);
                $user->save();
            }
        }

        if ($passwordValid) {
            Auth::login($user);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors(['email' => 'Mot de passe incorrect.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectBasedOnRole(User $user)
    {
        // Tous les utilisateurs (y compris techniciens) utilisent l'interface desktop responsive
        return redirect()->route('dashboard');
    }
}
