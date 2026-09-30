@extends('layouts.app')

@section('title', 'Détails du Demandeur')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('demandeurs.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la liste
        </a>
        <h1>{{ $demandeur->nom_complet }}</h1>
    </div>
    <a href="{{ route('demandeurs.edit', $demandeur) }}" class="btn-primary" style="background-color: #f59e0b;">
        <i class="fa-solid fa-edit"></i> Modifier
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Informations principales -->
    <div class="card">
        <h3 style="margin-bottom: 1rem; color: #1e40af; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.5rem;">
            <i class="fa-solid fa-user"></i> Informations Personnelles
        </h3>

        <div style="display: grid; gap: 1rem;">
            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Nom Complet</label>
                <div style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ $demandeur->nom_complet }}
                </div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Email</label>
                <div style="font-size: 1rem; color: #1d4ed8; margin-top: 0.25rem;">
                    <a href="mailto:{{ $demandeur->email }}" style="text-decoration: none; color: inherit;">
                        <i class="fa-solid fa-envelope"></i> {{ $demandeur->email }}
                    </a>
                </div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Téléphone</label>
                <div style="font-size: 1rem; color: #047857; margin-top: 0.25rem; font-weight: 600;">
                    <i class="fa-solid fa-phone"></i> {{ $demandeur->telephone }}
                </div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Base Assignée</label>
                <div style="font-size: 1rem; color: #0f172a; margin-top: 0.25rem;">
                    @if($demandeur->baseSite)
                        <span class="badge" style="background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.9rem;">
                            <i class="fa-solid fa-building"></i> {{ $demandeur->baseSite->nom_base }}
                        </span>
                    @else
                        <span style="color: #cbd5e1;">Non assigné</span>
                    @endif
                </div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Statut</label>
                <div style="margin-top: 0.25rem;">
                    @if($demandeur->statut === 'actif')
                    <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                        <i class="fa-solid fa-check-circle"></i> Actif
                    </span>
                    @else
                    <span class="badge" style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca;">
                        <i class="fa-solid fa-times-circle"></i> Inactif
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="card">
        <h3 style="margin-bottom: 1rem; color: #1e40af; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.5rem;">
            <i class="fa-solid fa-chart-bar"></i> Statistiques
        </h3>

        <div style="display: grid; gap: 1rem;">
            <div style="background-color: #eff6ff; padding: 1rem; border-radius: 0.5rem;">
                <div style="font-size: 0.75rem; color: #1e40af; text-transform: uppercase; font-weight: 700;">Sites Assignés</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: #1d4ed8; margin-top: 0.25rem;">
                    {{ $demandeur->sitesAssignes->count() }}
                </div>
            </div>

            <div style="background-color: #f0fdf4; padding: 1rem; border-radius: 0.5rem;">
                <div style="font-size: 0.75rem; color: #065f46; text-transform: uppercase; font-weight: 700;">Demandes Créées</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: #047857; margin-top: 0.25rem;">
                    {{ $demandeur->demandesCreees->count() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sites assignés -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1rem; color: #1e40af; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.5rem;">
        <i class="fa-solid fa-location-dot"></i> Sites Assignés ({{ $demandeur->sitesAssignes->count() }})
    </h3>

    @if($demandeur->sitesAssignes->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        @foreach($demandeur->sitesAssignes as $site)
        <div style="padding: 1rem; background-color: #eff6ff; border: 2px solid #bfdbfe; border-radius: 0.75rem;">
            <div style="font-weight: 700; color: #1e40af; margin-bottom: 0.25rem;">
                <i class="fa-solid fa-building"></i> {{ $site->nom_site }}
            </div>
            <div style="font-size: 0.8rem; color: #64748b;">
                {{ $site->adresse ?? 'Pas d\'adresse' }}
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 2rem; background-color: #fef3c7; border-radius: 0.5rem;">
        <i class="fa-solid fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;"></i>
        <p style="color: #b45309; font-weight: 600;">Aucun site assigné</p>
        <p style="color: #92400e; font-size: 0.85rem;">Modifiez ce demandeur pour lui assigner des sites.</p>
    </div>
    @endif
</div>

<!-- Demandes récentes -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1rem; color: #1e40af; border-bottom: 2px solid #bfdbfe; padding-bottom: 0.5rem;">
        <i class="fa-solid fa-clipboard-list"></i> Demandes Récentes
    </h3>

    @if($demandeur->demandesCreees->count() > 0)
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Site</th>
                    <th>Description</th>
                    <th>Urgence</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandeur->demandesCreees->take(10) as $demande)
                <tr>
                    <td><code>{{ $demande->numero_demande }}</code></td>
                    <td>{{ $demande->site->nom_site ?? 'N/A' }}</td>
                    <td>{{ Str::limit($demande->description, 50) }}</td>
                    <td>
                        @php
                            $urgenceStyles = [
                                'critique' => ['bg' => '#fee2e2', 'color' => '#be123c', 'border' => '#fecaca'],
                                'urgent' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3'],
                                'moyen' => ['bg' => '#f0f9ff', 'color' => '#0369a1', 'border' => '#bae6fd'],
                                'faible' => ['bg' => '#f1f5f9', 'color' => '#64748b', 'border' => '#cbd5e1']
                            ];
                            $urgStyle = $urgenceStyles[$demande->niveau_urgence] ?? $urgenceStyles['moyen'];
                        @endphp
                        <span class="badge" style="background-color: {{ $urgStyle['bg'] }}; color: {{ $urgStyle['color'] }}; border: 1px solid {{ $urgStyle['border'] }};">
                            {{ ucfirst($demande->niveau_urgence) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="font-size: 0.75rem;">{{ $demande->statut }}</span>
                    </td>
                    <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align: center; padding: 2rem; color: #94a3b8;">
        <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.3;"></i>
        <p>Aucune demande créée pour le moment</p>
    </div>
    @endif
</div>
@endsection
