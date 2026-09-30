@extends('layouts.app')

@section('title', 'Espace Client')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Espace Client — Bienvenue, {{ Auth::user()->nom_complet }}</h1>
        <p>Suivez en temps réel l'avancement de vos demandes et l'état de vos équipements.</p>
    </div>
    <a href="{{ route('depannages.create') }}" class="my-4" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 10px rgba(190, 18, 60, 0.2); font-size: 0.8rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> Signaler une Panne
    </a>
</div>

<!-- KPIs Client -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #fffbeb; color: #b45309;"><i class="fa-solid fa-hourglass-half"></i></div>
        <div>
            <div class="stat-val">{{ $mes_depannages->where('statut', 'en attente')->count() }}</div>
            <div class="stat-label">Demandes en Attente</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #f0f9ff; color: #0369a1;"><i class="fa-solid fa-spinner"></i></div>
        <div>
            <div class="stat-val">{{ $mes_depannages->where('statut', 'en cours')->count() }}</div>
            <div class="stat-label">Interventions en Cours</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="stat-val">{{ $mes_depannages->where('statut', 'résolu')->count() }}</div>
            <div class="stat-label">Interventions Résolues</div>
        </div>
    </div>
</div>

<!-- Mes Demandes de Dépannage -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-list-check"></i> Mes Demandes de Dépannage ({{ $mes_depannages->count() }})</h3>
        <a href="{{ route('depannages.create') }}" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
            <i class="fa-solid fa-plus-circle"></i> Nouvelle demande
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th># Ticket</th>
                    <th>Équipement</th>
                    <th>Panne / Symptôme</th>
                    <th>Urgence</th>
                    <th>Date Souhaitée</th>
                    <th>Technicien Attribué</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mes_depannages as $d)
                <tr>
                    <td><code>#{{ $d->id }}</code></td>
                    <td><strong style="color: #059669;">{{ $d->equipement_reference ?? 'Équipement non spécifié' }}</strong></td>
                    <td>
                        {{ Str::limit($d->description_panne, 45) }}
                        @if($d->photo_panne || $d->fichier_joint)
                            <span title="Fichier / Photo jointe" style="color: #059669; margin-left: 0.3rem;"><i class="fa-solid fa-paperclip"></i></span>
                        @endif
                    </td>
                    <td>
                        @if(($d->urgence ?? '') === 'Urgent')
                            <span class="badge badge-danger"><i class="fa-solid fa-fire"></i> Urgent</span>
                        @elseif(($d->urgence ?? '') === 'Faible')
                            <span class="badge badge-info">Faible</span>
                        @else
                            <span class="badge badge-warning">Normal</span>
                        @endif
                    </td>
                    <td>{{ $d->technicien->nom_complet ?? 'En attente d\'affectation' }}</td>
                    <td>
                        @if($d->statut === 'résolu')
                            <span class="badge badge-success">Résolu</span>
                        @elseif($d->statut === 'en cours')
                            <span class="badge badge-info">En cours</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('depannages.show', $d->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Suivre Ticket
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2.5rem;">
                        <i class="fa-solid fa-clipboard-check" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                        Vous n'avez actuellement aucune demande de dépannage enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
