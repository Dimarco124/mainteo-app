@extends('layouts.app')

@section('title', 'Nouveau Technicien')

@section('content')
    <div class="header">
        <div>
            <a href="{{ route('techniciens.index') }}"
                style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Annuler
            </a>
            <h1>Ajouter un Technicien au Personnel</h1>
        </div>
    </div>

    <div class="card" style="max-width: 750px;">
        <form action="{{ route('techniciens.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Nom *</label>
                    <input type="text" name="nom" required placeholder="ex: KOUASSI"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Prénom</label>
                    <input type="text" name="prenom" placeholder="ex: Marc"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Adresse E-mail
                        *</label>
                    <input type="email" name="email" required placeholder="marc.kouassi@mainteo.net"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Mot de passe
                        *</label>
                    <input type="password" name="mot_de_passe" required placeholder="••••••••"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Téléphone</label>
                    <input type="text" name="telephone" placeholder="ex: +225 0700000000"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Spécialité</label>
                    <input type="text" name="specialite" placeholder="ex: Frigoriste, Électricien CVC"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Type
                        d'Utilisateur</label>
                    <input type="text" value="Technicien" disabled
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                    <input type="hidden" name="type_utilisateur" value="technicien">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem;">Équipe
                        attribuée</label>
                    <select name="equipe_id"
                        style="width: 100%; padding: 0.75rem; background-color: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; outline: none;">
                        <option value="">Sélectionner une équipe</option>
                        @foreach($equipes as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->nom_equipe }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit"
                style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; padding: 0.85rem 1.5rem; border-radius: 0.5rem; font-weight: 700; font-size: 1rem; cursor: pointer;">
                <i class="fa-solid fa-check"></i> Enregistrer le Technicien
            </button>
        </form>
    </div>
@endsection