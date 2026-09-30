@extends('layouts.app')

@section('title', 'Fiche Base / Site - ' . $base->nom_base)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('bases-sites.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Revenir à la liste
        </a>
        <h1>{{ $base->nom_base }} <span style="color: #059669; font-size: 1rem;">({{ $base->code_base }})</span></h1>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('bases-sites.edit', $base->id) }}" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
        <a href="{{ route('equipements.create') }}?base_site_id={{ $base->id }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter un Équipement
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <div>
        <!-- Équipements de la base -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-toolbox"></i> Équipements de cette Base ({{ $base->equipements->count() }})</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom Équipement</th>
                            <th>Type</th>
                            <th>Marque</th>
                            <th>Emplacement</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($base->equipements as $eq)
                        <tr>
                            <td><strong style="color: #059669;">{{ $eq->equipement_code }}</strong></td>
                            <td>{{ $eq->equipement_nom }}</td>
                            <td><span class="badge badge-info">{{ $eq->type ?? '-' }}</span></td>
                            <td>{{ $eq->marque ?? '-' }}</td>
                            <td>
                                @if($eq->emplacement === 'interne')
                                    <span class="badge" style="background: #dbeafe; color: #1e40af;">Interne</span>
                                @elseif($eq->emplacement === 'externe')
                                    <span class="badge" style="background: #fef3c7; color: #92400e;">Externe</span>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($eq->etat === 'fonctionnel')
                                    <span class="badge badge-success">Fonctionnel</span>
                                @elseif($eq->etat === 'en panne')
                                    <span class="badge badge-danger">En panne</span>
                                @elseif($eq->etat === 'en maintenance')
                                    <span class="badge badge-warning">En maintenance</span>
                                @else
                                    <span style="color: #94a3b8;">{{ $eq->etat ?? '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('equipements.show', $eq->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 700;">
                                    <i class="fa-solid fa-eye"></i> Détails
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 1.5rem;">
                                <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                <p>Aucun équipement enregistré sur cette base.</p>
                                <a href="{{ route('equipements.create') }}?base_id={{ $base->id }}" class="btn-primary" style="margin-top: 0.75rem; display: inline-flex;">
                                    <i class="fa-solid fa-plus"></i> Ajouter un Équipement
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <!-- Informations de la base -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #059669; margin-bottom: 1rem;"><i class="fa-solid fa-address-card"></i> Informations</h3>
            
            <p style="color: #64748b; font-size: 0.85rem;">Code Base</p>
            <p style="font-weight: 800; color: #0f172a; margin-bottom: 0.75rem;">{{ $base->code_base }}</p>

            <p style="color: #64748b; font-size: 0.85rem;">Client Propriétaire</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">
                <a href="{{ route('clients.show', $base->client->id) }}" style="color: #059669; text-decoration: none;">
                    {{ $base->client->nom ?? 'N/A' }}
                </a>
            </p>

            <p style="color: #64748b; font-size: 0.85rem;">Nombre de sites</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">
                <span class="badge badge-info">{{ $base->sites->count() }} site(s)</span>
            </p>

            <p style="color: #64748b; font-size: 0.85rem;">Nombre d'équipements</p>
            <p style="font-weight: 700; color: #0f172a;">
                <span class="badge badge-success">{{ $base->equipements->count() }} équipement(s)</span>
            </p>
        </div>

        <!-- Sites de cette base -->
        <div class="card">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #059669; margin-bottom: 1rem;">
                <i class="fa-solid fa-location-dot"></i> Sites ({{ $base->sites->count() }})
            </h3>
            
            @forelse($base->sites as $site)
                <div style="padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; margin-bottom: 0.75rem; border-left: 3px solid #059669;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">{{ $site->nom_site }}</p>
                            <p style="font-size: 0.8rem; color: #64748b;">Code: {{ $site->code_site }}</p>
                            <p style="font-size: 0.8rem; color: #64748b;">{{ $site->equipements->count() }} équipement(s)</p>
                        </div>
                        <a href="{{ route('sites.show', $site->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </div>
                </div>
            @empty
                <p style="color: #94a3b8; text-align: center; padding: 1rem;">
                    <i class="fa-solid fa-location-slash" style="font-size: 1.5rem; margin-bottom: 0.5rem; opacity: 0.3; display: block;"></i>
                    Aucun site dans cette base
                </p>
            @endforelse

            <a href="{{ route('sites.create') }}?base_id={{ $base->id }}" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 0.75rem;">
                <i class="fa-solid fa-plus"></i> Ajouter un Site
            </a>
        </div>
    </div>
</div>
@endsection
