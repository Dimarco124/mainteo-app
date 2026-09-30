@extends('layouts.app')

@section('title', 'Modifier Emplacement')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Modifier l'Emplacement</h1>
        <p>{{ $zone->nom_zone }}</p>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    <form action="{{ route('zones.update', $zone->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Site Parent <span style="color: #dc2626;">*</span>
            </label>
            <select name="site_id" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                <option value="">-- Sélectionner un site --</option>
                @foreach($sites as $site)
                <option value="{{ $site->id }}" {{ old('site_id', $zone->site_id) == $site->id ? 'selected' : '' }}>
                    {{ $site->nom_site }}
                    @if($site->baseSite)
                        ({{ $site->baseSite->nom_base }})
                    @elseif($site->client)
                        ({{ $site->client->nom }})
                    @endif
                </option>
                @endforeach
            </select>
            @error('site_id')
            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Nom de l'Emplacement <span style="color: #dc2626;">*</span>
            </label>
            <input type="text" name="nom_zone" value="{{ old('nom_zone', $zone->nom_zone) }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
            <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.35rem; display: block;">Si vous modifiez le nom, un nouveau code sera généré automatiquement.</small>
            @error('nom_zone')
            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>


        <div style="margin-bottom: 1.5rem; background-color: #f8fafc; padding: 1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #64748b; font-size: 0.75rem;">
                Code Actuel (Généré automatiquement)
            </label>
            <div style="font-size: 0.95rem; font-weight: 700; color: #0f172a; font-family: 'Courier New', monospace;">
                {{ $zone->code_zone }}
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Observations / Détails (optionnel)
            </label>
            <textarea name="observations" rows="3" style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">{{ old('observations', $zone->observations) }}</textarea>
            @error('observations')
            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn-primary" style="background-color: #8b5cf6;">
                <i class="fa-solid fa-check"></i> Mettre à Jour
            </button>
            <a href="{{ route('clients.combined', ['view' => 'emplacements']) }}" style="padding: 0.75rem 1.5rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700; font-size: 0.85rem;">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
