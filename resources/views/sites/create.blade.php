@extends('layouts.app')

@section('title', 'Nouveau Site')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Créer un Nouveau Site</h1>
        <p>Ajouter un site d'intervention.</p>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    <form action="{{ route('sites.store') }}" method="POST">
        @csrf

        @if(isset($base))
            {{-- Superviseur Client avec base : structure fixe --}}
            <input type="hidden" name="base_id" value="{{ $base->id }}">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Structure
                </label>
                <div style="padding: 0.75rem; background-color: #eff6ff; border: 2px solid #bfdbfe; border-radius: 0.75rem; font-weight: 600; color: #1e40af;">
                    <i class="fa-solid fa-sitemap"></i> Client → Base → Site
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Base Rattachée <span style="color: #dc2626;">*</span>
                </label>
                <div style="padding: 0.75rem; background-color: #eff6ff; border: 2px solid #bfdbfe; border-radius: 0.75rem; font-weight: 600; color: #1e40af;">
                    <i class="fa-solid fa-building"></i> {{ $base->nom_base }}
                    @if($base->client)
                    <span style="color: #64748b; font-weight: 400;">({{ $base->client->nom }})</span>
                    @endif
                </div>
            </div>

        @elseif(isset($client))
            {{-- Superviseur Client sans base : structure directe --}}
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Structure
                </label>
                <div style="padding: 0.75rem; background-color: #fef3c7; border: 2px solid #fde68a; border-radius: 0.75rem; font-weight: 600; color: #92400e;">
                    <i class="fa-solid fa-sitemap"></i> Client → Site (sans base)
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Entreprise <span style="color: #dc2626;">*</span>
                </label>
                <div style="padding: 0.75rem; background-color: #fef3c7; border: 2px solid #fde68a; border-radius: 0.75rem; font-weight: 600; color: #92400e;">
                    <i class="fa-solid fa-building"></i> {{ $client->nom }}
                </div>
            </div>

        @else
            {{-- Admin : choix de structure --}}
            <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: #f0fdf4; border: 2px solid #86efac; border-radius: 0.75rem;">
                <label style="display: block; margin-bottom: 0.75rem; font-weight: 700; color: #065f46; font-size: 0.9rem;">
                    <i class="fa-solid fa-sitemap"></i> Choisir la structure hiérarchique
                </label>
                
                <div style="display: flex; gap: 1rem;">
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="structure_type" value="base" checked onchange="toggleStructure()" style="margin-right: 0.5rem;">
                        <strong>Avec Base</strong><br>
                        <span style="font-size: 0.75rem; color: #64748b;">Client → Base → Site</span>
                    </label>
                    
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="structure_type" value="client" onchange="toggleStructure()" style="margin-right: 0.5rem;">
                        <strong>Client Direct</strong><br>
                        <span style="font-size: 0.75rem; color: #64748b;">Client → Site</span>
                    </label>
                </div>
            </div>

            @error('structure')
                <div style="color: #dc2626; font-size: 0.85rem; margin-bottom: 1rem; padding: 0.75rem; background-color: #fee2e2; border-radius: 0.5rem;">
                    <i class="fa-solid fa-exclamation-circle"></i> {{ $message }}
                </div>
            @enderror

            <div id="base_selector" style="margin-bottom: 1.5rem;">
                <label for="base_id" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Base Rattachée <span style="color: #dc2626;">*</span>
                </label>
                <select name="base_id" id="base_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">Sélectionner une base...</option>
                    @foreach($bases as $baseItem)
                        <option value="{{ $baseItem->id }}" {{ old('base_id') == $baseItem->id ? 'selected' : '' }}>
                            {{ $baseItem->nom_base }} 
                            @if($baseItem->client)
                            ({{ $baseItem->client->nom }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('base_id')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div id="client_selector" style="margin-bottom: 1.5rem; display: none;">
                <label for="client_id" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Client Direct <span style="color: #dc2626;">*</span>
                </label>
                <select name="client_id" id="client_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">Sélectionner un client...</option>
                    @foreach($clients as $clientItem)
                        <option value="{{ $clientItem->id }}" {{ old('client_id') == $clientItem->id ? 'selected' : '' }}>
                            {{ $clientItem->nom }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label for="code_site" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Code Site <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="code_site" id="code_site" 
                       value="{{ old('code_site', $nextCode) }}" 
                       required style="width: 100%; padding: 0.75rem;">
                @error('code_site')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="nom_site" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Nom du Site <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="nom_site" id="nom_site" 
                       value="{{ old('nom_site') }}" 
                       placeholder="Ex: Entrepôt A, Bâtiment Principal"
                       required style="width: 100%; padding: 0.75rem;">
                @error('nom_site')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="adresse" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Adresse / Localisation
            </label>
            <input type="text" name="adresse" id="adresse" 
                   value="{{ old('adresse') }}" 
                   placeholder="Ex: Zone industrielle, Bâtiment C"
                   style="width: 100%; padding: 0.75rem;">
            @error('adresse')
                <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label for="ville" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Ville
                </label>
                <input type="text" name="ville" id="ville" 
                       value="{{ old('ville') }}" 
                       placeholder="Ex: Dakar"
                       style="width: 100%; padding: 0.75rem;">
                @error('ville')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="telephone" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                    Téléphone
                </label>
                <input type="text" name="telephone" id="telephone" 
                       value="{{ old('telephone') }}" 
                       placeholder="Ex: +221 33 123 45 67"
                       style="width: 100%; padding: 0.75rem;">
                @error('telephone')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="observations" style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                Observations
            </label>
            <textarea name="observations" id="observations" rows="2" 
                      placeholder="Notes particulières sur ce site..."
                      style="width: 100%; padding: 0.75rem;">{{ old('observations') }}</textarea>
            @error('observations')
                <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="{{ auth()->user()->isSuperviseurClient() ? route('sites.index') : route('clients.combined', ['view' => 'sites']) }}" 
               style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.65rem 1.1rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Enregistrer le Site
            </button>
        </div>
    </form>
</div>

<script>
function toggleStructure() {
    const structureType = document.querySelector('input[name="structure_type"]:checked').value;
    const baseSelector = document.getElementById('base_selector');
    const clientSelector = document.getElementById('client_selector');
    const baseInput = document.getElementById('base_id');
    const clientInput = document.getElementById('client_id');
    
    if (structureType === 'base') {
        baseSelector.style.display = 'block';
        clientSelector.style.display = 'none';
        baseInput.required = true;
        clientInput.required = false;
        clientInput.value = '';
    } else {
        baseSelector.style.display = 'none';
        clientSelector.style.display = 'block';
        baseInput.required = false;
        clientInput.required = true;
        baseInput.value = '';
    }
}

// Si old('structure_type') existe (après erreur validation), restore l'état
@if(old('structure_type') === 'client')
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('input[name="structure_type"][value="client"]').checked = true;
    toggleStructure();
});
@endif
</script>
@endsection
