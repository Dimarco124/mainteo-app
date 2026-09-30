@extends('mobile.technicien.layout')

@section('title', 'Comptes-Rendus - MAINTEO Mobile')
@section('page-title', 'Comptes-Rendus')

@section('mobile-content')
<div style="padding: 1.5rem;">
    @if($rapports->count() > 0)
        @foreach($rapports as $rapport)
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); border-left: 4px solid var(--main-emerald);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">#{{ $rapport->id }}</div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark);">
                        {{ $rapport->equipement->equipement_nom ?? 'Équipement' }}
                    </h3>
                </div>
                <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: #ecfdf5; color: #059669;">
                    <i class="fa-solid fa-circle-check"></i>
                    Résolu
                </span>
            </div>
            
            <div style="font-size: 0.9rem; color: var(--text-normal); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-map-marker-alt" style="color: var(--main-emerald);"></i>
                <span>{{ $rapport->equipement->site->nom_site ?? 'Site' }}</span>
            </div>
            
            <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <i class="fa-solid fa-calendar"></i>
                    {{ is_string($rapport->date_demande) ? $rapport->date_demande : $rapport->date_demande->format('d/m/Y') }}
                </div>
                @if($rapport->rapport_technicien)
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <i class="fa-solid fa-file-lines"></i>
                    Rapport disponible
                </div>
                @endif
            </div>
            
            @if($rapport->rapport_technicien)
            <details style="margin-bottom: 0.75rem;">
                <summary style="font-size: 0.9rem; font-weight: 600; color: var(--main-emerald); cursor: pointer; margin-bottom: 0.5rem;">
                    Voir le rapport
                </summary>
                <div style="padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; font-size: 0.85rem; color: var(--text-normal); line-height: 1.6;">
                    {{ Str::limit($rapport->rapport_technicien, 200) }}
                </div>
            </details>
            @endif
            
            <a href="{{ route('depannages.show', $rapport->id) }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; background: var(--bg-body); color: var(--text-normal); border: 1px solid var(--border-color); border-radius: 0.75rem; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: all 0.2s ease;">
                <i class="fa-solid fa-eye"></i>
                Voir détails
            </a>
        </div>
        @endforeach
        
        <!-- Pagination -->
        @if($rapports->hasPages())
        <div style="margin-top: 1.5rem;">
            {{ $rapports->links() }}
        </div>
        @endif
    @else
    <div style="text-align: center; padding: 3rem 2rem; color: var(--text-muted);">
        <div style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">📄</div>
        <p style="font-size: 1rem; margin-bottom: 1.5rem;">Aucun compte-rendu disponible</p>
        <p style="font-size: 0.9rem;">Les rapports d'interventions terminées apparaîtront ici</p>
    </div>
    @endif
</div>

<div style="height: 2rem;"></div>
@endsection
