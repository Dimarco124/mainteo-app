@extends('layouts.app')

@section('title', 'Demande d\'Installation d\'Équipement')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('equipements.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1>Demande d'Installation d'Équipement</h1>
        <p style="color: #64748b; font-size: 0.85rem;">Remplissez les informations de base pour demander l'installation d'un nouvel équipement</p>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    @if ($errors->any())
        <div style="background-color: #fee; border: 1px solid #fcc; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <h4 style="color: #c00; margin-bottom: 0.5rem;">⚠️ Erreurs de validation :</h4>
            <ul style="color: #c00; margin: 0; padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('equipements.storeSimple') }}" method="POST">
        @csrf

        {{-- Votre Base (Auto-rempli) --}}
        <div style="background-color: #f0fdf4; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                Votre Base
            </label>
            @php
                $userBase = \App\Models\BaseSite::find(auth()->user()->base_id);
                $userClient = \App\Models\Client::find(auth()->user()->client_id);
            @endphp
            <div style="background: #fff; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
                <div style="font-weight: 700; color: #065f46; margin-bottom: 0.25rem;">
                    <i class="fa-solid fa-building"></i> {{ $userClient ? $userClient->nom : 'N/A' }}
                </div>
                <div style="font-size: 0.9rem; color: #047857;">
                    <i class="fa-solid fa-map-marker-alt"></i> {{ $userBase ? $userBase->nom_base : 'N/A' }} 
                    @if($userBase)
                        <code style="margin-left: 0.5rem;">({{ $userBase->code_base }})</code>
                    @endif
                </div>
            </div>
            
            <input type="hidden" name="client_id" value="{{ auth()->user()->client_id }}">
            <input type="hidden" name="base_id" value="{{ auth()->user()->base_id }}">
        </div>

        {{-- Site d'Installation --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Site d'Installation * <span style="color: #be123c;">(Obligatoire)</span>
            </label>
            <select name="site_id" required style="width: 100%; padding: 0.75rem;">
                <option value="">-- Sélectionner un site --</option>
                @php
                    $userSites = \App\Models\Site::where('base_id', auth()->user()->base_id)->orderBy('nom_site')->get();
                @endphp
                @foreach($userSites as $site)
                <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                    {{ $site->nom_site }} ({{ $site->code_site }})
                </option>
                @endforeach
            </select>
            @error('site_id')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Nom / Désignation --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Nom / Désignation *
            </label>
            <input type="text" name="equipement_nom" value="{{ old('equipement_nom') }}" required placeholder="ex: Climatiseur Bureau Principal" style="width: 100%; padding: 0.75rem;">
            @error('equipement_nom')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Type d'Équipement --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Type d'Équipement *
            </label>
            <select name="type" required style="width: 100%; padding: 0.75rem;">
                <option value="Climatiseur Split" {{ old('type') == 'Climatiseur Split' ? 'selected' : '' }}>Climatiseur Split (S)</option>
                <option value="Groupe Froid" {{ old('type') == 'Groupe Froid' ? 'selected' : '' }}>Groupe Froid (GF)</option>
                <option value="CVC" {{ old('type') == 'CVC' ? 'selected' : '' }}>CVC / Ventilo-Convecteur (CVC)</option>
                <option value="Chambre Froide" {{ old('type') == 'Chambre Froide' ? 'selected' : '' }}>Chambre Froide (CF)</option>
                <option value="Armoire Réfrigérée" {{ old('type') == 'Armoire Réfrigérée' ? 'selected' : '' }}>Armoire Réfrigérée (AR)</option>
                <option value="Autre" {{ old('type') == 'Autre' ? 'selected' : '' }}>Autre (EQP)</option>
            </select>
            @error('type')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Emplacement --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Emplacement *
            </label>
            <select name="emplacement" required style="width: 100%; padding: 0.75rem;">
                <option value="externe" {{ old('emplacement', 'externe') == 'externe' ? 'selected' : '' }}>Externe / Extérieur</option>
                <option value="interne" {{ old('emplacement') == 'interne' ? 'selected' : '' }}>Interne / Intérieur</option>
            </select>
            @error('emplacement')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Description / Observations --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Description / Informations complémentaires
            </label>
            <textarea name="description" rows="3" placeholder="Décrivez les besoins ou informations importantes..." style="width: 100%; padding: 0.75rem;">{{ old('description') }}</textarea>
        </div>

        {{-- Date d'Installation Souhaitée --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 700;">
                Date d'Installation Souhaitée *
            </label>
            <input type="date" name="date_installation_souhaitee" value="{{ old('date_installation_souhaitee', date('Y-m-d')) }}" required style="width: 100%; padding: 0.75rem;">
            @error('date_installation_souhaitee')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.85rem; color: #1e40af; margin: 0; display: flex; align-items: flex-start; gap: 0.5rem;">
                <i class="fa-solid fa-info-circle" style="margin-top: 0.15rem;"></i>
                <span>Votre demande sera transmise à l'équipe Soutarah qui complétera les informations techniques (marque, puissance, etc.) et planifiera l'installation.</span>
            </p>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
            <i class="fa-solid fa-paper-plane"></i> Soumettre la Demande d'Installation
        </button>
    </form>
</div>
@endsection
