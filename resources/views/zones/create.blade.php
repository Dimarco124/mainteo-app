@extends('layouts.app')

@section('title', 'Nouveau Emplacement')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Créer un Nouveau Emplacement</h1>
        <p>Ajouter un sous-site, bureau, local ou villa au sein d'un site.</p>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <form action="{{ route('zones.store') }}" method="POST">
        @csrf

        @if(auth()->user()->isAdmin())
        {{-- Mode Admin : Cascade CLIENT -> BASE -> SITE --}}
        <div style="background-color: #f8fafc; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
            <h4 style="font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-map-location-dot" style="color: #8b5cf6;"></i> Sélection du Site Parent
            </h4>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 700;">
                    1. Entreprise Client * <span style="color: #be123c;">(Sélectionner d'abord)</span>
                </label>
                <select id="clientSelect" required style="width: 100%; padding: 0.75rem; background: #fff; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                    <option value="">-- Sélectionner une entreprise --</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->nom }} ({{ $c->code ?? 'CLI-'.$c->id }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 700;">
                    2. Base <span style="color: #64748b;">(Obligatoire si l'entreprise a des bases)</span>
                </label>
                <select id="baseSelect" disabled style="width: 100%; padding: 0.75rem; background: #fff; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                    <option value="">-- Choisir d'abord l'entreprise ci-dessus --</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #334155; margin-bottom: 0.4rem; font-weight: 700;">
                    3. Site Parent * <span style="color: #be123c;">(Emplacement rattaché à ce site)</span>
                </label>
                <select name="site_id" id="siteSelect" required disabled style="width: 100%; padding: 0.75rem; background: #fff; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                    <option value="">-- Choisir d'abord la base ou l'entreprise --</option>
                </select>
                @error('site_id')
                <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>
        @elseif(auth()->user()->type_utilisateur === 'superviseur_soutarah')
            @php
                $soutarahAssignment = \App\Models\Assignment::where('superviseur_soutarah_id', auth()->user()->id)->first();
            @endphp
            @if($soutarahAssignment && $soutarahAssignment->base_id)
                @php
                    $soutarahBase = \App\Models\BaseSite::find($soutarahAssignment->base_id);
                    $soutarahClient = $soutarahBase ? \App\Models\Client::find($soutarahBase->client_id) : null;
                @endphp
                <div style="background-color: #f0fdf4; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Votre Périmètre Assigné (Base & Entreprise)
                        </label>
                        <div style="background: #fff; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
                            <div style="font-weight: 700; color: #065f46; margin-bottom: 0.25rem;">
                                <i class="fa-solid fa-building"></i> {{ $soutarahClient ? $soutarahClient->nom : 'N/A' }}
                            </div>
                            <div style="font-size: 0.9rem; color: #047857;">
                                <i class="fa-solid fa-map-marker-alt"></i> Base : {{ $soutarahBase ? $soutarahBase->nom_base : 'N/A' }}
                                @if($soutarahBase)
                                    <code style="margin-left: 0.5rem;">({{ $soutarahBase->code_base }})</code>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #065f46; margin-bottom: 0.4rem; font-weight: 700;">
                            Site Parent * <span style="color: #be123c;">(Choisir un site de votre base)</span>
                        </label>
                        <select name="site_id" required style="width: 100%; padding: 0.75rem; background: #fff; border-radius: 0.5rem; border: 1px solid #a7f3d0; font-size: 0.85rem;">
                            <option value="">-- Sélectionner un site --</option>
                            @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ (old('site_id') == $site->id || $sites->count() === 1) ? 'selected' : '' }}>
                                {{ $site->nom_site }} ({{ $site->code_site }})
                            </option>
                            @endforeach
                        </select>
                        @error('site_id')
                        <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            @else
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                        Site Parent *
                    </label>
                    <select name="site_id" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                        <option value="">-- Sélectionner un site --</option>
                        @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ (old('site_id') == $site->id || $sites->count() === 1) ? 'selected' : '' }}>
                            {{ $site->nom_site }} ({{ $site->code_site }})
                        </option>
                        @endforeach
                    </select>
                    @error('site_id')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            @endif
        @else
            {{-- Mode Non-Admin (Superviseur Client / Demandeur) --}}
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Site Parent *
                </label>
                @if($sites->count() > 0)
                <select name="site_id" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                    <option value="">-- Sélectionner un site --</option>
                    @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ (old('site_id') == $site->id || $sites->count() === 1) ? 'selected' : '' }}>
                        {{ $site->nom_site }}
                        @if($site->baseSite)
                            ({{ $site->baseSite->nom_base }})
                        @elseif($site->client)
                            ({{ $site->client->nom }})
                        @endif
                    </option>
                    @endforeach
                </select>
                @else
                <div style="background-color: #fef2f2; color: #dc2626; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.85rem;">
                    <i class="fa-solid fa-exclamation-circle"></i> Aucun site enregistré dans votre périmètre.
                </div>
                @endif
                @error('site_id')
                <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Nom de l'Emplacement <span style="color: #dc2626;">*</span>
            </label>
            <input type="text" name="nom_zone" value="{{ old('nom_zone') }}" required placeholder="Ex: Bureau 101, Villa 4, Atelier Nord, Salle de réunion..." style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
            <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.35rem; display: block;">Nom spécifique du bureau, local, appartement ou villa. <strong>Le code sera généré automatiquement.</strong></small>
            @error('nom_zone')
            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>


        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Observations / Détails (optionnel)
            </label>
            <textarea name="observations" rows="3" placeholder="Informations complémentaires sur cet emplacement..." style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.6rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">{{ old('observations') }}</textarea>
            @error('observations')
            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn-primary" style="background-color: #8b5cf6;">
                <i class="fa-solid fa-check"></i> Enregistrer l'Emplacement
            </button>
            <a href="{{ auth()->user()->isAdmin() ? route('clients.combined', ['view' => 'emplacements']) : route('zones.index') }}" style="padding: 0.75rem 1.5rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 700; font-size: 0.85rem;">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('clientSelect');
    const baseSelect = document.getElementById('baseSelect');
    const siteSelect = document.getElementById('siteSelect');

    if (clientSelect && baseSelect && siteSelect) {
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            baseSelect.innerHTML = '<option value="">-- Chargement... --</option>';
            baseSelect.disabled = true;
            siteSelect.innerHTML = '<option value="">-- Choisir d\'abord la base ci-dessus --</option>';
            siteSelect.disabled = true;

            if (!clientId) {
                baseSelect.innerHTML = '<option value="">-- Choisir d\'abord l\'entreprise ci-dessus --</option>';
                return;
            }

            fetch(`/api/clients/${clientId}/bases`)
                .then(r => r.json())
                .then(bases => {
                    if (bases.length > 0) {
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
                    } else {
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
                            });
                    }
                });
        });

        baseSelect.addEventListener('change', function() {
            const baseId = this.value;
            siteSelect.innerHTML = '<option value="">-- Chargement des sites... --</option>';
            siteSelect.disabled = true;

            if (!baseId) {
                siteSelect.innerHTML = '<option value="">-- Choisir d\'abord la base ci-dessus --</option>';
                siteSelect.disabled = true;
                return;
            }

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
                });
        });
    }
});
</script>
@endsection
