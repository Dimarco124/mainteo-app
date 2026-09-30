@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Notifications ({{ $nonLuesCount }} non lues)</h1>
        <p>Demandes de validation et alertes sur les interventions</p>
    </div>
    
    @if($nonLuesCount > 0)
    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #059669, #10b981);">
            <i class="fa-solid fa-check-double"></i> Tout marquer comme lu
        </button>
    </form>
    @endif
</div>

<div class="card">
    @forelse($notifications as $notif)
    <div style="padding: 1.25rem; border-bottom: 1px solid #e2e8f0; {{ $notif->statut === 'non_lu' ? 'background-color: #f0fdf4;' : '' }} display: flex; gap: 1rem; align-items: start;">
        <div style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; 
            @if($notif->type === 'demande_validation_superviseur' || $notif->type === 'demande_validation_admin')
                background-color: #fef3c7; color: #b45309;
            @elseif($notif->type === 'intervention_approuvee')
                background-color: #d1fae5; color: #047857;
            @else
                background-color: #fee2e2; color: #dc2626;
            @endif
        ">
            @if($notif->type === 'demande_validation_superviseur' || $notif->type === 'demande_validation_admin')
                <i class="fa-solid fa-bell"></i>
            @elseif($notif->type === 'intervention_approuvee')
                <i class="fa-solid fa-circle-check"></i>
            @else
                <i class="fa-solid fa-circle-xmark"></i>
            @endif
        </div>
        
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">
                    @if($notif->statut === 'non_lu')
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #059669; margin-right: 0.5rem;"></span>
                    @endif
                    {{ ucfirst(str_replace('_', ' ', $notif->type)) }}
                </h3>
                <span style="font-size: 0.75rem; color: #94a3b8; white-space: nowrap;">
                    {{ $notif->created_at->diffForHumans() }}
                </span>
            </div>
            
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 0.75rem; line-height: 1.6;">
                {{ $notif->message }}
            </p>
            
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                @if($notif->demande_id)
                <a href="{{ route('demandes.show', $notif->demande_id) }}" class="btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    <i class="fa-solid fa-eye"></i> Voir la demande
                </a>
                @elseif($notif->intervention_id)
                <a href="{{ route('depannages.show', $notif->intervention_id) }}" class="btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    <i class="fa-solid fa-eye"></i> Voir l'intervention
                </a>
                @elseif($notif->maintenance_id)
                <a href="{{ route('maintenances.show', $notif->maintenance_id) }}" class="btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Voir la maintenance
                </a>
                @endif
                
                @if($notif->statut === 'non_lu')
                <form action="{{ route('notifications.markAsRead', $notif->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #059669; font-size: 0.85rem; cursor: pointer; text-decoration: underline;">
                        <i class="fa-solid fa-check"></i> Marquer comme lu
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div style="text-align: center; padding: 3rem; color: #94a3b8;">
        <i class="fa-solid fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
        <p style="font-size: 1.1rem; font-weight: 600;">Aucune notification pour le moment</p>
        <p style="font-size: 0.9rem;">Vous serez notifié des demandes de validation d'interventions</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div style="margin-top: 1.5rem;">
    {{ $notifications->links('vendor.pagination.custom') }}
</div>
@endif
@endsection
