@extends('layouts.app')

@section('title', 'Ajouter un Équipement')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('equipements.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
        <h1>Enregistrer un Nouvel Équipement</h1>
        @if(auth()->user()->type_utilisateur === 'superviseur_client')
        <p style="color: #64748b; font-size: 0.85rem;">Ajoutez un nouvel équipement à votre base</p>
        @else
        <p style="color: #64748b; font-size: 0.85rem;">Choisissez d'abord le client, puis la base où se trouve l'équipement</p>
        @endif
    </div>
</div>

<div class="card" style="max-width: 900px;">
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
    
    <form action="{{ route('equipements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- 1. LOCALISATION --}}
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-map-location-dot"></i> Localisation
        </h3>

        @if(auth()->user()->type_utilisateur === 'superviseur_client')
            {{-- Superviseur Client : Afficher uniquement sa base en lecture seule --}}
            <div style="background-color: #f0fdf4; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                <div style="margin-bottom: 0.75rem;">
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
                </div>
                
                <input type="hidden" name="client_id" value="{{ auth()->user()->client_id }}">
                <input type="hidden" name="base_id" value="{{ auth()->user()->base_id }}">
                
                {{-- Sélection du Site --}}
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                        Site d'Installation * <span style="color: #be123c;">(Obligatoire)</span>
                    </label>
                    <select name="site_id" id="siteSelect" required style="width: 100%; padding: 0.75rem; background: #fff;">
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
                
                <div style="margin-top: 1rem;">
                    <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                        Zone / Emplacement <span style="color: #64748b;">(Bureau, Villa, Local...)</span>
                    </label>
                    <select name="zone_id" id="zoneSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                        <option value="">-- Choisir d'abord le site ci-dessus --</option>
                    </select>
                </div>
                
                <p style="font-size: 0.8rem; color: #047857; margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-info-circle"></i>
                    L'équipement sera ajouté au site sélectionné
                </p>
            </div>
        @elseif(auth()->user()->type_utilisateur === 'superviseur_soutarah')
            {{-- Superviseur Soutarah : Périmètre strictement filtré selon l'Assignment --}}
            @php
                $soutarahAssignment = \App\Models\Assignment::where('superviseur_soutarah_id', auth()->user()->id)->first();
            @endphp
            @if($soutarahAssignment && $soutarahAssignment->base_id)
                {{-- Assigné à une Base spécifique : Client et Base fixes, choix du Site --}}
                @php
                    $soutarahBase = \App\Models\BaseSite::find($soutarahAssignment->base_id);
                    $soutarahClient = $soutarahBase ? \App\Models\Client::find($soutarahBase->client_id) : null;
                @endphp
                <div style="background-color: #f0fdf4; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Périmètre Assigné Soutarah (Entreprise & Base)
                        </label>
                        <div style="background: #fff; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
                            <div style="font-weight: 700; color: #065f46; margin-bottom: 0.25rem;">
                                <i class="fa-solid fa-building"></i> {{ $soutarahClient ? $soutarahClient->nom : 'N/A' }}
                            </div>
                            <div style="font-size: 0.9rem; color: #047857;">
                                <i class="fa-solid fa-map-marker-alt"></i> {{ $soutarahBase ? $soutarahBase->nom_base : 'N/A' }} 
                                @if($soutarahBase)
                                    <code style="margin-left: 0.5rem;">({{ $soutarahBase->code_base }})</code>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="client_id" value="{{ $soutarahClient ? $soutarahClient->id : '' }}">
                    <input type="hidden" name="base_id" value="{{ $soutarahAssignment->base_id }}">
                    
                    {{-- Sélection du Site --}}
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Site d'Installation * <span style="color: #be123c;">(Obligatoire)</span>
                        </label>
                        <select name="site_id" id="siteSelect" required style="width: 100%; padding: 0.75rem; background: #fff;">
                            <option value="">-- Sélectionner un site --</option>
                            @php
                                $userSites = \App\Models\Site::where('base_id', $soutarahAssignment->base_id)->orderBy('nom_site')->get();
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
                    
                    <div style="margin-top: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Zone / Emplacement <span style="color: #64748b;">(Bureau, Villa, Local...)</span>
                        </label>
                        <select name="zone_id" id="zoneSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                            <option value="">-- Choisir d'abord le site ci-dessus --</option>
                        </select>
                    </div>
                </div>
            @elseif($soutarahAssignment && $soutarahAssignment->client_id)
                {{-- Assigné à une Entreprise entière : Client fixe, choix Base & Site --}}
                @php
                    $soutarahClient = \App\Models\Client::find($soutarahAssignment->client_id);
                    $soutarahBases = \App\Models\BaseSite::where('client_id', $soutarahAssignment->client_id)->orderBy('nom_base')->get();
                @endphp
                <div style="background-color: #f0fdf4; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Entreprise Assignée
                        </label>
                        <div style="background: #fff; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #a7f3d0; font-weight: 700; color: #065f46;">
                            <i class="fa-solid fa-building"></i> {{ $soutarahClient ? $soutarahClient->nom : 'N/A' }}
                        </div>
                        <input type="hidden" name="client_id" value="{{ $soutarahAssignment->client_id }}">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Base *
                        </label>
                        <select name="base_id" id="baseSelect" required style="width: 100%; padding: 0.75rem; background: #fff;">
                            <option value="">-- Sélectionner une base --</option>
                            @foreach($soutarahBases as $b)
                            <option value="{{ $b->id }}" {{ old('base_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->nom_base }} ({{ $b->code_base }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Site d'Installation
                        </label>
                        <select name="site_id" id="siteSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                            <option value="">-- Choisir d'abord la base ci-dessus --</option>
                        </select>
                    </div>

                    <div style="margin-top: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Zone / Emplacement <span style="color: #64748b;">(Bureau, Villa, Local...)</span>
                        </label>
                        <select name="zone_id" id="zoneSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                            <option value="">-- Choisir d'abord le site ci-dessus --</option>
                        </select>
                    </div>
                </div>
            @endif
        @else
            {{-- Admin : Sélection complète CLIENT → BASE → SITE --}}
            <div style="background-color: #f0fdf4; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                        1. Entreprise Client * <span style="color: #be123c;">(Sélectionner d'abord)</span>
                    </label>
                    <select name="client_id" id="clientSelect" required style="width: 100%; padding: 0.75rem; background: #fff;">
                        <option value="">-- Sélectionner une entreprise --</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nom }} <span style="color: #64748b;">({{ $c->code ?? 'CLI-'.$c->id }})</span>
                        </option>
                        @endforeach
                    </select>
                    @error('client_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                        2. Base <span style="color: #64748b;">(Optionnel si entreprise unique)</span>
                    </label>
                    <select name="base_id" id="baseSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                        <option value="">-- Choisir d'abord l'entreprise ci-dessus --</option>
                    </select>
                    @error('base_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                        3. Site d'Installation <span style="color: #64748b;">(Optionnel si pas de site)</span>
                    </label>
                    <select name="site_id" id="siteSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                        <option value="">-- Choisir d'abord la base ci-dessus --</option>
                    </select>
                    @error('site_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-top: 1rem;">
                    <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                        4. Zone / Emplacement <span style="color: #64748b;">(Bureau, Villa, Local...)</span>
                    </label>
                    <select name="zone_id" id="zoneSelect" style="width: 100%; padding: 0.75rem; background: #fff;">
                        <option value="">-- Choisir d'abord le site ci-dessus --</option>
                    </select>
                    @error('zone_id')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <p style="font-size: 0.75rem; color: #047857; margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-info-circle"></i>
                    Base, Site et Zone sont optionnels si l'entreprise est unique (ex: un immeuble)
                </p>
            </div>
        @endif

        {{-- CARTE DYNAMIQUE DU CODE ÉQUIPEMENT --}}
        <div style="background: linear-gradient(135deg, #065f46, #047857); color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 1rem; margin-bottom: 1.5rem; box-shadow: 0 10px 25px -5px rgba(6, 95, 70, 0.3); display: flex; align-items: center; justify-content: space-between; gap: 1rem; border: 2px solid #34d399;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem; color: #a7f3d0;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Code Équipement (Généré automatiquement)
                </div>
                <div id="codePreviewText" style="font-family: monospace; font-size: 1.5rem; font-weight: 900; letter-spacing: 0.05em; color: #ffffff;">
                    Calcul en cours...
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px); padding: 0.5rem 0.85rem; border-radius: 0.6rem; font-size: 0.8rem; font-weight: 600; text-align: right; border: 1px solid rgba(255,255,255,0.2);">
                <i class="fa-solid fa-sync" style="font-size: 0.75rem; margin-right: 0.3rem;"></i> Calcul Automatique
            </div>
        </div>
        <input type="hidden" name="equipement_code" id="equipement_code" value="{{ old('equipement_code') }}">

        {{-- 2. IDENTIFICATION --}}
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
            <i class="fa-solid fa-id-card"></i> Identification de l'Équipement
        </h3>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Nom / Désignation *</label>
                <input type="text" name="equipement_nom" value="{{ old('equipement_nom') }}" required placeholder="ex: Groupe Froid Daikin 2.5CV" style="width: 100%; padding: 0.75rem;">
                @error('equipement_nom')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @error('equipement_code')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
                @enderror
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">N° d'Équipement sur Site <span style="font-size: 0.75rem; color: #059669;">(ex: 01, 32)</span></label>
                <input type="text" name="num_sur_site" id="num_sur_site" value="{{ old('num_sur_site', '32') }}" required placeholder="ex: 32" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Type d'Équipement *</label>
                <select name="type" id="typeSelect" required style="width: 100%; padding: 0.75rem;">
                    <option value="Climatiseur Split" {{ old('type', 'Climatiseur Split') == 'Climatiseur Split' ? 'selected' : '' }}>Climatiseur Split (S)</option>
                    <option value="Groupe Froid" {{ old('type') == 'Groupe Froid' ? 'selected' : '' }}>Groupe Froid (GF)</option>
                    <option value="CVC" {{ old('type') == 'CVC' ? 'selected' : '' }}>CVC / Ventilo-Convecteur (CVC)</option>
                    <option value="Chambre Froide" {{ old('type') == 'Chambre Froide' ? 'selected' : '' }}>Chambre Froide (CF)</option>
                    <option value="Armoire Réfrigérée" {{ old('type') == 'Armoire Réfrigérée' ? 'selected' : '' }}>Armoire Réfrigérée (AR)</option>
                    <option value="Autre" {{ old('type') == 'Autre' ? 'selected' : '' }}>Autre (EQP)</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Emplacement *</label>
                <select name="emplacement" id="emplacementSelect" required style="width: 100%; padding: 0.75rem;">
                    <option value="externe" {{ old('emplacement', 'externe') == 'externe' ? 'selected' : '' }}>Externe / Extérieur (E)</option>
                    <option value="interne" {{ old('emplacement') == 'interne' ? 'selected' : '' }}>Interne / Intérieur (I)</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">État</label>
                <select name="etat" style="width: 100%; padding: 0.75rem;">
                    <option value="bon" {{ old('etat', 'bon') == 'bon' ? 'selected' : '' }}>Bon état</option>
                    <option value="moyen" {{ old('etat') == 'moyen' ? 'selected' : '' }}>État moyen</option>
                    <option value="mauvais" {{ old('etat') == 'mauvais' ? 'selected' : '' }}>Mauvais état</option>
                    <option value="hors service" {{ old('etat') == 'hors service' ? 'selected' : '' }}>Hors service</option>
                </select>
            </div>
        </div>

        {{-- 3. CARACTÉRISTIQUES TECHNIQUES --}}
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
            <i class="fa-solid fa-cogs"></i> Caractéristiques Techniques
        </h3>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Marque</label>
                <input type="text" name="marque" value="{{ old('marque') }}" placeholder="ex: Daikin, Carrier, Trane" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Puissance</label>
                <input type="text" name="puissance" value="{{ old('puissance') }}" placeholder="ex: 7.5 kW ou 9000 BTU" style="width: 100%; padding: 0.75rem;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Réfrigérant</label>
                <input type="text" name="refrigerant" value="{{ old('refrigerant') }}" placeholder="ex: R410A, R134a, R407C" style="width: 100%; padding: 0.75rem;">
            </div>
        </div>

        {{-- 4. INSTALLATION --}}
        <h3 style="font-size: 1rem; font-weight: 800; color: #059669; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
            <i class="fa-solid fa-calendar-check"></i> Date d'Installation Souhaitée
        </h3>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Date d'Installation Souhaitée <span style="color: #be123c;">*</span></label>
            <input type="date" name="date_installation_souhaitee" id="date_installation_souhaitee" value="{{ old('date_installation_souhaitee', date('Y-m-d')) }}" required style="width: 100%; padding: 0.75rem; border: 2px solid #a7f3d0; border-radius: 0.5rem;">
            @error('date_installation_souhaitee')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- 5. OBSERVATIONS --}}
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Observations / Remarques</label>
            <textarea name="observations" rows="3" placeholder="Informations complémentaires sur l'état, l'emplacement exact..." style="width: 100%; padding: 0.75rem;">{{ old('observations') }}</textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
            <i class="fa-solid fa-check"></i> Enregistrer l'Équipement
        </button>
    </form>
</div>

{{-- JavaScript pour charger les bases selon le client, puis les sites selon la base --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('clientSelect');
    const baseSelect = document.getElementById('baseSelect');
    const siteSelect = document.getElementById('siteSelect');
    const zoneSelect = document.getElementById('zoneSelect');
    
    // Charger les bases et les sites quand on sélectionne un client
    if (clientSelect && baseSelect && siteSelect) {
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            
            // Réinitialiser les selects dépendants
            baseSelect.innerHTML = '<option value="">-- Chargement... --</option>';
            baseSelect.disabled = true;
            siteSelect.innerHTML = '<option value="">-- Choisir d\'abord la base ci-dessus --</option>';
            siteSelect.disabled = true;
            if (zoneSelect) {
                zoneSelect.innerHTML = '<option value="">-- Choisir d\'abord le site --</option>';
            }
            
            if (!clientId) {
                baseSelect.innerHTML = '<option value="">-- Choisir d\'abord l\'entreprise ci-dessus --</option>';
                siteSelect.innerHTML = '<option value="">-- Choisir d\'abord l\'entreprise ci-dessus --</option>';
                generateEquipmentCode();
                return;
            }
            
            // 1. Charger les bases de l'entreprise
            fetch(`/api/clients/${clientId}/bases`)
                .then(r => r.json())
                .then(bases => {
                    if (bases.length > 0) {
                        // L'entreprise A des bases : l'utilisateur doit obligatoirement choisir la base avant le site !
                        baseSelect.innerHTML = '<option value="">-- Sélectionner une base --</option>';
                        bases.forEach(base => {
                            const option = document.createElement('option');
                            option.value = base.id;
                            option.textContent = `${base.nom_base} (${base.code_base})`;
                            baseSelect.appendChild(option);
                        });
                        baseSelect.disabled = false;

                        siteSelect.innerHTML = '<option value="">-- Choisir d\'abord la base ci-dessus --</option>';
                        siteSelect.disabled = true;

                        const oldBaseId = '{{ old('base_id') }}';
                        if (oldBaseId && baseSelect.querySelector(`option[value="${oldBaseId}"]`)) {
                            baseSelect.value = oldBaseId;
                            baseSelect.dispatchEvent(new Event('change'));
                        }
                    } else {
                        // L'entreprise N'A PAS de bases (ex: RETROCI) : on débloque immédiatement les sites directs
                        baseSelect.innerHTML = '<option value="">-- Aucune base pour cette entreprise --</option>';
                        baseSelect.disabled = true;
                        
                        siteSelect.innerHTML = '<option value="">-- Chargement des sites... --</option>';
                        fetch(`/api/clients/${clientId}/sites`)
                            .then(r => r.json())
                            .then(sites => {
                                siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
                                if (sites.length === 0) {
                                    siteSelect.innerHTML = '<option value="">-- Aucun site pour cette entreprise --</option>';
                                    siteSelect.disabled = true;
                                } else {
                                    sites.forEach(site => {
                                        const option = document.createElement('option');
                                        option.value = site.id;
                                        option.textContent = `${site.nom_site} (${site.code_site || 'SITE'})`;
                                        siteSelect.appendChild(option);
                                    });
                                    siteSelect.disabled = false;
                                }

                                const oldSiteId = '{{ old('site_id') }}';
                                if (oldSiteId && siteSelect.querySelector(`option[value="${oldSiteId}"]`)) {
                                    siteSelect.value = oldSiteId;
                                    loadZones(oldSiteId);
                                }
                                generateEquipmentCode();
                            });
                    }
                    generateEquipmentCode();
                })
                .catch(err => {
                    console.error('Erreur chargement bases:', err);
                    baseSelect.innerHTML = '<option value="">-- Erreur de chargement --</option>';
                });
        });
    }
    
    // Filtrer/charger les sites quand on choisit une base spécifique
    if (baseSelect && siteSelect) {
        baseSelect.addEventListener('change', function() {
            const baseId = this.value;
            
            siteSelect.innerHTML = '<option value="">-- Chargement des sites... --</option>';
            siteSelect.disabled = true;
            if (zoneSelect) {
                zoneSelect.innerHTML = '<option value="">-- Choisir d\'abord le site --</option>';
            }
            
            if (!baseId) {
                siteSelect.innerHTML = '<option value="">-- Choisir d\'abord la base ci-dessus --</option>';
                siteSelect.disabled = true;
                generateEquipmentCode();
                return;
            }
            
            // Charger les sites rattachés à cette base spécifique
            fetch(`/api/bases/${baseId}/sites`)
                .then(r => r.json())
                .then(sites => {
                    siteSelect.innerHTML = '<option value="">-- Sélectionner un site --</option>';
                    if (sites.length === 0) {
                        siteSelect.innerHTML = '<option value="">-- Aucun site pour cette base --</option>';
                        siteSelect.disabled = true;
                    } else {
                        sites.forEach(site => {
                            const option = document.createElement('option');
                            option.value = site.id;
                            option.textContent = `${site.nom_site} (${site.code_site || 'SITE'})`;
                            siteSelect.appendChild(option);
                        });
                        siteSelect.disabled = false;
                    }
                    generateEquipmentCode();
                })
                .catch(err => {
                    console.error('Erreur chargement sites de la base:', err);
                    siteSelect.innerHTML = '<option value="">-- Erreur de chargement --</option>';
                });
        });
    }

    if (siteSelect) {
        siteSelect.addEventListener('change', function() {
            loadZones(this.value);
        });
        if (siteSelect.value) {
            loadZones(siteSelect.value);
        }
    }

    async function loadZones(siteId) {
        const zoneSelect = document.getElementById('zoneSelect');
        if (!zoneSelect) return;

        zoneSelect.innerHTML = '<option value="">Chargement...</option>';
        if (!siteId) {
            zoneSelect.innerHTML = '<option value="">-- Choisir d\'abord le site --</option>';
            if (typeof refreshEquipementCount === 'function') refreshEquipementCount();
            return;
        }

        try {
            const res = await fetch(`/api/sites/${siteId}/zones`);
            const zones = await res.json();
            zoneSelect.innerHTML = '<option value="">-- Sélectionner une zone / emplacement (optionnel) --</option>';
            if (zones.length === 0) {
                zoneSelect.innerHTML = '<option value="">Aucune zone spécifique sur ce site</option>';
            } else {
                zones.forEach(z => {
                    const opt = document.createElement('option');
                    opt.value = z.id;
                    opt.textContent = z.nom_zone + (z.code_zone ? ` (${z.code_zone})` : '');
                    zoneSelect.appendChild(opt);
                });
            }
            const oldZoneId = '{{ old('zone_id') }}';
            if (oldZoneId) zoneSelect.value = oldZoneId;
        } catch (e) {
            console.error('Erreur chargement zones:', e);
            zoneSelect.innerHTML = '<option value="">-- Erreur de chargement --</option>';
        }
        if (typeof refreshEquipementCount === 'function') refreshEquipementCount();
    }
    
    // Si un client est déjà sélectionné au chargement (old input)
    if (clientSelect && clientSelect.value) {
        clientSelect.dispatchEvent(new Event('change'));
    }

    // ── Générateur dynamique instantané du Code Équipement ─────────────────────────
    function generateEquipmentCode() {
        try {
            const codeInput = document.getElementById('equipement_code');
            const previewText = document.getElementById('codePreviewText');

            // 1. Code Base (ex: F1)
            let baseCode = 'F1';
            const baseSelectEl = document.getElementById('baseSelect');
            if (baseSelectEl && baseSelectEl.selectedIndex >= 0 && baseSelectEl.options[baseSelectEl.selectedIndex]) {
                const text = baseSelectEl.options[baseSelectEl.selectedIndex].text;
                const match = text.match(/\(([^)]+)\)/);
                if (match && match[1]) baseCode = match[1];
            } else {
                const baseCodeBadge = document.querySelector('code');
                if (baseCodeBadge && baseCodeBadge.textContent) {
                    const match = baseCodeBadge.textContent.match(/\(([^)]+)\)/);
                    if (match && match[1]) baseCode = match[1];
                    else baseCode = baseCodeBadge.textContent.replace(/[^A-Za-z0-9]/g, '');
                }
            }

            // 2. Code Site (ex: LON)
            let siteCode = 'LON';
            const siteSelectEl = document.getElementById('siteSelect');
            if (siteSelectEl && siteSelectEl.selectedIndex >= 0 && siteSelectEl.options[siteSelectEl.selectedIndex]) {
                const text = siteSelectEl.options[siteSelectEl.selectedIndex].text;
                const match = text.match(/\(([^)]+)\)/);
                if (match && match[1]) {
                    siteCode = match[1];
                } else if (text && text.trim() && !text.includes('--')) {
                    siteCode = text.trim().substring(0, 3).toUpperCase();
                }
            }

            // 3. Type (S, GF, CVC, CF, AR) + Numéro sur site (ex: S32)
            const typeSelect = document.querySelector('[name="type"]');
            const typeVal = typeSelect ? typeSelect.value : '';
            let typeCode = 'S';
            if (typeVal.includes('Groupe')) typeCode = 'GF';
            else if (typeVal.includes('CVC')) typeCode = 'CVC';
            else if (typeVal.includes('Chambre')) typeCode = 'CF';
            else if (typeVal.includes('Armoire')) typeCode = 'AR';
            else if (typeVal.includes('Split')) typeCode = 'S';

            const numOnSiteInput = document.querySelector('[name="num_sur_site"]');
            const numOnSiteVal = (numOnSiteInput && numOnSiteInput.value) ? numOnSiteInput.value : '32';
            const numOnSite = numOnSiteVal.toString().replace(/[^0-9]/g, '') || '32';
            const typeNum = typeCode + numOnSite;

            // 4. Emplacement (E ou I) + Puissance (ex: E1.5)
            const emplSelect = document.querySelector('[name="emplacement"]');
            const emplVal = emplSelect ? emplSelect.value : 'externe';
            const emplChar = (emplVal === 'interne') ? 'I' : 'E';

            const puisInput = document.querySelector('[name="puissance"]');
            const puisValRaw = puisInput ? puisInput.value : '1.5';
            const puisValNum = puisValRaw.replace(/[^0-9.]/g, '') || '1.5';
            const emplPuis = emplChar + puisValNum;

            // 5. Marque + Réfrigérant (ex: NAS56 si réfrigérant = R56)
            const marqueInput = document.querySelector('[name="marque"]');
            const marqueRaw = marqueInput ? marqueInput.value : 'NAS';
            const marqueCode = (marqueRaw.replace(/[^A-Za-z0-9]/g, '') || 'NAS').substring(0, 3).toUpperCase();

            const refrigerantInput = document.querySelector('[name="refrigerant"]');
            const refrigerantRaw = refrigerantInput ? refrigerantInput.value : 'R56';
            // Extraire les chiffres après le "R" dans le réfrigérant (ex: R410A -> 410, R56 -> 56, R407C -> 407)
            const refrigerantMatch = refrigerantRaw.match(/R?(\d+)/i);
            const refrigerantNum = refrigerantMatch ? refrigerantMatch[1] : '56';
            const serieCode = marqueCode + refrigerantNum;

            // 6. Mois + Année (ex: 0826)
            const dateInstInput = document.getElementById('date_installation_souhaitee');
            let mmaa = '0826';
            if (dateInstInput && dateInstInput.value) {
                const parts = dateInstInput.value.split('-');
                if (parts.length === 3) {
                    const mm = parts[1];
                    const aa = parts[0].slice(-2);
                    mmaa = mm + aa;
                }
            }

            const finalCode = `${baseCode}${siteCode} ${typeNum}${emplPuis}${serieCode} ${mmaa}`;

            if (codeInput) codeInput.value = finalCode;
            if (previewText) previewText.textContent = finalCode;

        } catch (e) {
            console.error('Erreur génération code:', e);
        }
    }

    // Écouter instantanément TOUTES les saisies sur le formulaire
    document.querySelectorAll('form input, form select, form textarea').forEach(el => {
        el.addEventListener('input', generateEquipmentCode);
        el.addEventListener('change', generateEquipmentCode);
        el.addEventListener('keyup', generateEquipmentCode);
    });

    // Générer le code immédiatement
    generateEquipmentCode();
});

// Exécuter immédiatement hors DOMContentLoaded au cas où
setTimeout(function() {
    if (typeof generateEquipmentCode === 'function') {
        generateEquipmentCode();
    }
}, 300);
</script>
@endsection
