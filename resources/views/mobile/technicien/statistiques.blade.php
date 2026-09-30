@extends('mobile.technicien.layout')

@section('title', 'Statistiques - MAINTEO Mobile')
@section('page-title', 'Statistiques')

@section('mobile-content')
<div style="padding: 1.5rem;">
    <!-- Stats générales -->
    <div style="background: #ffffff; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-chart-pie" style="color: var(--main-emerald);"></i>
            Vue d'ensemble
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div style="text-align: center; padding: 1rem; background: #ecfdf5; border-radius: 0.75rem;">
                <div style="font-size: 2rem; font-weight: 800; color: var(--main-emerald);">{{ $stats['total'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Interventions</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: #eff6ff; border-radius: 0.75rem;">
                <div style="font-size: 2rem; font-weight: 800; color: #0369a1;">{{ $stats['mois_actuel'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Ce Mois</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 0.75rem;">
                <div style="font-size: 2rem; font-weight: 800; color: #059669;">{{ $stats['resolues'] }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Résolues</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: #fef3c7; border-radius: 0.75rem;">
                <div style="font-size: 2rem; font-weight: 800; color: #b45309;">
                    {{ $stats['total'] > 0 ? round(($stats['resolues'] / $stats['total']) * 100) : 0 }}%
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Taux de Résolution</div>
            </div>
        </div>
    </div>
    
    <!-- Performance -->
    <div style="background: #ffffff; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-trophy" style="color: #f59e0b;"></i>
            Performance
        </h2>
        
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Interventions résolues</span>
                <span style="font-size: 0.9rem; font-weight: 700; color: var(--main-emerald);">{{ $stats['resolues'] }} / {{ $stats['total'] }}</span>
            </div>
            <div style="width: 100%; height: 12px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                <div style="width: {{ $stats['total'] > 0 ? ($stats['resolues'] / $stats['total']) * 100 : 0 }}%; height: 100%; background: linear-gradient(90deg, var(--main-emerald), var(--main-emerald-light)); border-radius: 999px; transition: width 0.5s ease;"></div>
            </div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--main-emerald); font-size: 1.1rem;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Interventions ce mois</span>
                </div>
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--text-dark);">{{ $stats['mois_actuel'] }}</span>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #b45309; font-size: 1.1rem;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-normal);">Moyenne par mois</span>
                </div>
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--text-dark);">
                    {{ round($stats['total'] / max(1, now()->month)) }}
                </span>
            </div>
        </div>
    </div>
    
    <!-- Message encouragement -->
    <div style="background: linear-gradient(135deg, var(--main-emerald), var(--main-emerald-light)); border-radius: 1rem; padding: 1.5rem; color: #ffffff; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎯</div>
        <div style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">Excellent travail !</div>
        <div style="font-size: 0.9rem; opacity: 0.9;">Continuez sur cette lancée</div>
    </div>
</div>

<div style="height: 2rem;"></div>
@endsection
