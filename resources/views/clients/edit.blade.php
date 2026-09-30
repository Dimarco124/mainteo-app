@extends('layouts.app')

@section('title', 'Modifier Client - ' . $client->nom)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('clients.show', $client->id) }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
        <h1>Modifier le Client : {{ $client->nom }}</h1>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <form action="{{ route('clients.update', $client->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Code Client *</label>
                <input type="text" name="code" value="{{ $client->code }}" required style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Nom / Raison Sociale *</label>
                <input type="text" name="nom" value="{{ $client->nom }}" required style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Téléphone</label>
                <input type="text" name="telephone" value="{{ $client->telephone }}" style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Adresse E-mail</label>
                <input type="email" name="email" value="{{ $client->email ?? $client->mail }}" style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Ville</label>
                <input type="text" name="ville" value="{{ $client->ville }}" style="width: 100%; padding: 0.75rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Pays</label>
                <input type="text" name="pays" value="{{ $client->pays }}" style="width: 100%; padding: 0.75rem;">
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Adresse Siège</label>
            <textarea name="adresse" rows="3" style="width: 100%; padding: 0.75rem;">{{ $client->adresse }}</textarea>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-save"></i> Mettre à jour la Fiche Client
        </button>
    </form>
</div>
@endsection
