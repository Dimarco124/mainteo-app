@extends('layouts.app')

@section('title', 'Assigner des Sites')

@section('content')
<style>
    .site-checkbox {
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .site-checkbox:hover {
        background-color: #f7fafc;
        border-color: #10b981;
    }
    .site-checkbox input:checked ~ .site-label {
        font-weight: 700;
        color: #10b981;
    }
    .site-checkbox input:checked {
        accent-color: #10b981;
    }
</style>

<div class="header">
    <div>
        <a href="{{ route('users.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1>Assigner des Sites</h1>
        <p>Sélectionnez les sites accessibles pour <strong>{{ $demandeur->nom_complet }}</strong></p>
    </div>
</div>

@if(session('success'))
<div style="background-color: #d1fae5; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.75rem; border: 1px solid #a7f3d0;">
    <p style="margin: 0; color: #047857; font-weight: 700;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </p>
</div>
@endif

<div class="card">
    <form action="{{ route('demandeurs.sites.update', $demandeur->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div style="margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: #1e293b;">
                <i class="fa-solid fa-map-marker-alt"></i> Sites disponibles
            </h3>
            
            @if($allSites->isEmpty())
                <div style="text-align: center; padding: 2rem; color: #94a3b8;">
                    <i class="fa-solid fa-inbox fa-2x" style="display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
                    Aucun site disponible
                </div>
            @else
                <div style="display: grid; gap: 0.5rem;">
                    @foreach($allSites as $site)
                    <label class="site-checkbox">
                        <input 
                            type="checkbox" 
                            name="site_ids[]" 
                            value="{{ $site->id }}"
                            {{ in_array($site->id, $assignedSiteIds) ? 'checked' : '' }}
                            style="width: 18px; height: 18px; margin-right: 0.75rem;">
                        <span class="site-label" style="font-size: 0.95rem;">
                            <strong>{{ $site->nom_site }}</strong>
                            @if($site->baseSite)
                                <span style="color: #64748b; font-size: 0.85rem;"> ({{ $site->baseSite->nom_base }})</span>
                            @endif
                        </span>
                    </label>
                    @endforeach
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background-color: #eff6ff; border-radius: 0.5rem; border: 1px solid #dbeafe;">
                    <strong style="color: #1e40af;">
                        <i class="fa-solid fa-info-circle"></i> Sites sélectionnés : 
                        <span id="selected-count">{{ count($assignedSiteIds) }}</span>
                    </strong>
                </div>
            @endif
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn-primary" style="background-color: #10b981;">
                <i class="fa-solid fa-check"></i> Enregistrer l'assignation
            </button>
            <a href="{{ route('users.index') }}" class="btn-primary" style="background-color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-times"></i> Annuler
            </a>
        </div>
    </form>
</div>

<script>
// Compter les sites sélectionnés
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="site_ids[]"]');
    const counter = document.getElementById('selected-count');
    
    function updateCount() {
        const count = Array.from(checkboxes).filter(cb => cb.checked).length;
        counter.textContent = count;
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });
});
</script>
@endsection
