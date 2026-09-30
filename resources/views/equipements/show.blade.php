@extends('layouts.app')

@section('title', 'Fiche Équipement - ' . $equipement->equipement_nom)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('equipements.index') }}" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la liste
        </a>
        <h1>{{ $equipement->equipement_nom }} <span style="color: #38bdf8; font-size: 1.2rem;">({{ $equipement->equipement_code ?? 'EQ-'.$equipement->id }})</span></h1>
    </div>
    @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_soutarah']))
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('equipements.edit', $equipement->id) }}" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.75rem 1.25rem; border-radius: 0.5rem; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
    </div>
    @endif
</div>

<!-- Grille passeport équipement -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Spécifications techniques -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #38bdf8; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-microchip"></i> Spécifications Techniques
        </h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Marque</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $equipement->marque ?? 'N/A' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Puissance</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $equipement->puissance ?? 'Non spécifiée' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Emplacement</p>
                <p style="font-weight: 600; font-size: 1rem;"><span class="badge badge-info">{{ $equipement->emplacement ?? 'Non spécifié' }}</span></p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">Année d'Installation</p>
                <p style="font-weight: 600; font-size: 1rem;">{{ $equipement->annee_installation ?? 'Non renseignée' }}</p>
            </div>
            <div>
                <p style="color: #94a3b8; font-size: 0.85rem;">État Fonctionnel</p>
                <p style="font-weight: 600; font-size: 1rem;">
                    <span class="badge {{ $equipement->etat == 'En panne' ? 'badge-danger' : 'badge-success' }}">
                        {{ $equipement->etat ?? 'Bon état' }}
                    </span>
                </p>
            </div>
        </div>

        @if($equipement->observations)
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #334155;">
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.4rem;">Observations & Remarques</p>
            <p style="font-size: 0.95rem; line-height: 1.5;">{{ $equipement->observations }}</p>
        </div>
        @endif
    </div>

    <!-- Emplacement & Métadonnées -->
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #10b981; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-location-dot"></i> Localisation & Emplacement
        </h3>
        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.2rem;">Site d'implantation</p>
        <p style="font-weight: 600; font-size: 1rem; margin-bottom: 1rem;">
            <i class="fa-solid fa-building"></i> {{ $equipement->site ? $equipement->site->nom_site : 'Non assigné' }}
        </p>

        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.2rem;">Emplacement (Sous-site / Local / Bureau)</p>
        <p style="font-weight: 600; font-size: 1rem; margin-bottom: 1rem; color: #a855f7;">
            @if($equipement->zone)
                <i class="fa-solid fa-layer-group"></i> {{ $equipement->zone->nom_zone }}
                @if($equipement->zone->code_zone)
                    <code style="font-size: 0.85rem; background: rgba(168,85,247,0.15); color: #c084fc; padding: 0.1rem 0.4rem; border-radius: 0.25rem;">({{ $equipement->zone->code_zone }})</code>
                @endif
            @else
                <span style="color: #94a3b8; font-style: italic;">Non assigné</span>
            @endif
        </p>

        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.2rem;">Position de l'unité</p>
        <p style="font-weight: 600; font-size: 1rem; margin-bottom: 1rem;">
            @if($equipement->emplacement === 'interne')
                <span class="badge" style="background-color: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6;">
                    <i class="fa-solid fa-building"></i> Interne (Intérieur)
                </span>
            @elseif($equipement->emplacement === 'externe')
                <span class="badge" style="background-color: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b;">
                    <i class="fa-solid fa-tree"></i> Externe (Extérieur)
                </span>
            @else
                <span style="color: #94a3b8; font-style: italic;">Non spécifiée</span>
            @endif
        </p>

        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.2rem;">Numéro d'équipement sur site</p>
        <p style="font-weight: 600; font-size: 1rem;">N° {{ $equipement->num_sur_site ?? '1' }}</p>
    </div>
</div>

<!-- Onglet Traçabilité Fluides Froid (F-GAS) -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title" style="color: #06b6d4;"><i class="fa-solid fa-snowflake"></i> Historique des Fiches Froid & Fluides (F-GAS)</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date Saisie</th>
                    <th>Fluide / Fréon</th>
                    <th>État Filtres</th>
                    <th>Cuivre & Armaflex</th>
                    <th>Technicien</th>
                    <th>Observations</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fichesFroid as $f)
                <tr>
                    <td>{{ $f->date_saisie }}</td>
                    <td><strong style="color: #38bdf8;">{{ $f->freon ?? 'N/A' }}</strong></td>
                    <td>{{ $f->etat_filtres ?? '-' }}</td>
                    <td>{{ $f->cuivre ?? '-' }} / {{ $f->armaflex ?? '-' }}</td>
                    <td>{{ $f->technicien->nom_complet ?? 'N/A' }}</td>
                    <td>{{ $f->observations ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8;">Aucune fiche froid enregistrée pour cet équipement.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Onglet Historique Dépannages -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #f59e0b;"><i class="fa-solid fa-wrench"></i> Historique des Dépannages</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date Demande</th>
                    <th>Description Panne</th>
                    <th>Urgence</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($depannages as $d)
                <tr>
                    <td>{{ $d->date_demande }}</td>
                    <td>{{ Str::limit($d->description_panne, 60) }}</td>
                    <td><span class="badge badge-warning">{{ $d->etat_demande }}</span></td>
                    <td><span class="badge badge-success">{{ $d->statut }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8;">Aucun historique de panne pour cet équipement.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
