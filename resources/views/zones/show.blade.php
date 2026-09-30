@extends('layouts.app')

@section('title', 'Détails Emplacement - ' . $zone->nom_zone)

@section('content')
<div class="header">
    <div>
        <a href="{{ route('zones.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour à la liste
        </a>
        <h1>{{ $zone->nom_zone }} <span style="color: #059669; font-size: 1rem;">({{ $zone->code_zone }})</span></h1>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        @if(in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_client']))
        <a href="{{ route('zones.edit', $zone->id) }}" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-pen"></i> Modifier
        </a>
        @endif
        <a href="{{ route('equipements.create') }}?zone_id={{ $zone->id }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Ajouter un Équipement
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <div>
        <!-- Équipements de l'emplacement -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-toolbox"></i> Équipements dans cet Emplacement ({{ $zone->equipements->count() }})</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom Équipement</th>
                            <th>Type</th>
                            <th>Marque</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zone->equipements as $eq)
                        <tr>
                            <td><strong style="color: #059669;">{{ $eq->equipement_code }}</strong></td>
                            <td>{{ $eq->equipement_nom }}</td>
                            <td><span class="badge badge-info">{{ $eq->type ?? '-' }}</span></td>
                            <td>{{ $eq->marque ?? '-' }}</td>
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
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 1.5rem;">
                                <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                <p>Aucun équipement dans cet emplacement.</p>
                                <a href="{{ route('equipements.create') }}?zone_id={{ $zone->id }}" class="btn-primary" style="margin-top: 0.75rem; display: inline-flex;">
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
        <!-- Informations de l'emplacement -->
        <div class="card">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #059669; margin-bottom: 1rem;"><i class="fa-solid fa-address-card"></i> Informations</h3>
            
            <p style="color: #64748b; font-size: 0.85rem;">Code Emplacement</p>
            <p style="font-weight: 800; color: #0f172a; margin-bottom: 0.75rem;">{{ $zone->code_zone }}</p>

            <p style="color: #64748b; font-size: 0.85rem;">Nom</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">{{ $zone->nom_zone }}</p>

            @if($zone->bureau)
            <p style="color: #64748b; font-size: 0.85rem;">Bureau</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">{{ $zone->bureau }}</p>
            @endif

            @if($zone->etage)
            <p style="color: #64748b; font-size: 0.85rem;">Étage</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">{{ $zone->etage }}</p>
            @endif

            <p style="color: #64748b; font-size: 0.85rem;">Site</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">
                @if($zone->site)
                    <a href="{{ route('sites.show', $zone->site->id) }}" style="color: #059669; text-decoration: none;">
                        {{ $zone->site->nom_site }}
                    </a>
                @else
                    <span style="color: #94a3b8;">-</span>
                @endif
            </p>

            @if($zone->site && $zone->site->baseSite)
            <p style="color: #64748b; font-size: 0.85rem;">Base</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">
                <a href="{{ route('bases-sites.show', $zone->site->baseSite->id) }}" style="color: #059669; text-decoration: none;">
                    {{ $zone->site->baseSite->nom_base }}
                </a>
            </p>
            @endif

            @if($zone->site && $zone->site->client)
            <p style="color: #64748b; font-size: 0.85rem;">Client</p>
            <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">
                <a href="{{ route('clients.show', $zone->site->client->id) }}" style="color: #059669; text-decoration: none;">
                    {{ $zone->site->client->nom }}
                </a>
            </p>
            @endif

            <p style="color: #64748b; font-size: 0.85rem;">Nombre d'équipements</p>
            <p style="font-weight: 700; color: #0f172a;">
                <span class="badge badge-success">{{ $zone->equipements->count() }} équipement(s)</span>
            </p>

            @if($zone->observations)
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">
            <p style="color: #64748b; font-size: 0.85rem;">Observations</p>
            <p style="color: #0f172a; font-size: 0.9rem; line-height: 1.5;">{{ $zone->observations }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
