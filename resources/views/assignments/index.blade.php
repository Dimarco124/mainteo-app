@extends('layouts.app')

@section('title', 'Gestion des Affectations')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Affectations Superviseurs Soutarah ({{ $assignments->total() }})</h1>
        <p>Assigner un superviseur Soutarah à une base OU à une petite entreprise</p>
    </div>
    <button onclick="openCreateModal()" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; padding: 0.65rem 1.1rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); border: none; cursor: pointer;">
        <i class="fa-solid fa-plus"></i> Nouvelle Affectation
    </button>
</div>

<div class="card" style="padding: 1.25rem; background-color: #e0f2fe; border: 1px solid #bae6fd; border-radius: 0.75rem; margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
        <i class="fa-solid fa-info-circle" style="color: #0369a1; font-size: 1.25rem; margin-top: 0.1rem;"></i>
        <div>
            <strong style="color: #0369a1; font-size: 0.9rem;">Règles d'affectation :</strong>
            <p style="color: #075985; font-size: 0.85rem; margin-top: 0.25rem; line-height: 1.5;">
                Un Superviseur Soutarah peut être assigné à <strong>UNE base</strong> OU à <strong>UNE petite entreprise</strong> (sans bases). 
                L'admin reçoit TOUJOURS toutes les demandes, même si un superviseur est assigné.
            </p>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Superviseur Soutarah</th>
                    <th>Assigné à</th>
                    <th>Type</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                <tr>
                    <td>
                        <strong>{{ $assignment->superviseurSoutarah->nom_complet }}</strong><br>
                        <small style="color: #94a3b8;">{{ $assignment->superviseurSoutarah->email }}</small>
                    </td>
                    <td>
                        @if($assignment->base_id)
                            <strong>{{ $assignment->base->nom_base }}</strong>
                        @else
                            <strong>{{ $assignment->client->nom }}</strong>
                        @endif
                    </td>
                    <td>
                        @if($assignment->base_id)
                            <span class="badge badge-info">Base</span>
                        @else
                            <span class="badge badge-warning">Entreprise</span>
                        @endif
                    </td>
                    <td>
                        @if($assignment->base_id && $assignment->base->client)
                            {{ $assignment->base->client->nom }}
                        @elseif($assignment->client)
                            {{ $assignment->client->nom }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td><code>{{ $assignment->created_at->format('d/m/Y H:i') }}</code></td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.5rem;">
                            <button onclick="openEditModal({{ $assignment->id }})" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <form action="{{ route('assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette affectation ?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Modal Edit (masqué par défaut) -->
                <div id="editModal{{ $assignment->id }}" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
                    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">Modifier l'Affectation</h3>
                            <button onclick="closeEditModal({{ $assignment->id }})" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
                        </div>
                        
                        <form action="{{ route('assignments.update', $assignment) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <p style="margin-bottom: 1rem; color: #64748b; font-size: 0.85rem;">
                                <strong>Superviseur:</strong> {{ $assignment->superviseurSoutarah->nom_complet }}
                            </p>
                            
                            <div style="margin-bottom: 1.25rem;">
                                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Type d'affectation *</label>
                                <select name="assignment_type" required style="width: 100%; padding: 0.75rem;" onchange="toggleFields('edit{{ $assignment->id }}', this.value)">
                                    <option value="base" {{ $assignment->base_id ? 'selected' : '' }}>Base</option>
                                    <option value="client" {{ $assignment->client_id ? 'selected' : '' }}>Entreprise (sans bases)</option>
                                </select>
                            </div>

                            <div id="edit{{ $assignment->id }}_base" style="margin-bottom: 1.25rem; display: {{ $assignment->base_id ? 'block' : 'none' }};">
                                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Base *</label>
                                <select name="base_id" style="width: 100%; padding: 0.75rem;">
                                    <option value="">Sélectionner une base</option>
                                    @foreach($bases as $base)
                                        <option value="{{ $base->id }}" {{ $assignment->base_id == $base->id ? 'selected' : '' }}>
                                            {{ $base->client ? $base->client->nom : 'N/A' }} - {{ $base->nom_base }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="edit{{ $assignment->id }}_client" style="margin-bottom: 1.25rem; display: {{ $assignment->client_id ? 'block' : 'none' }};">
                                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Entreprise *</label>
                                <select name="client_id" style="width: 100%; padding: 0.75rem;">
                                    <option value="">Sélectionner une entreprise</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $assignment->client_id == $client->id ? 'selected' : '' }}>
                                            {{ $client->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                                <button type="button" onclick="closeEditModal({{ $assignment->id }})" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                                    Annuler
                                </button>
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check"></i> Modifier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 3rem;">
                        <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>Aucune affectation créée</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $assignments->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>

<!-- Modal Create -->
<div id="createModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">Créer une Nouvelle Affectation</h3>
            <button onclick="closeCreateModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        
        <form action="{{ route('assignments.store') }}" method="POST">
            @csrf
            
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Superviseur Soutarah *</label>
                <select name="superviseur_soutarah_id" id="superviseur_soutarah_id" required style="width: 100%; padding: 0.75rem;">
                    <option value="">Sélectionner un superviseur</option>
                    @foreach($superviseursSoutarah as $superviseur)
                        <option value="{{ $superviseur->id }}">
                            {{ $superviseur->nom_complet }} ({{ $superviseur->email }})
                        </option>
                    @endforeach
                </select>
                @if($superviseursSoutarah->count() == 0)
                    <small style="color: #be123c; font-size: 0.75rem;">Aucun Superviseur Soutarah disponible. Créez-en d'abord.</small>
                @endif
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Type d'affectation *</label>
                <select name="assignment_type" id="assignment_type" required style="width: 100%; padding: 0.75rem;" onchange="toggleFields('create', this.value)">
                    <option value="">Sélectionner un type</option>
                    <option value="base">Base</option>
                    <option value="client">Entreprise (sans bases)</option>
                </select>
            </div>

            <div id="create_base" style="margin-bottom: 1.25rem; display: none;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Base *</label>
                <select name="base_id" id="base_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">Sélectionner une base</option>
                    @foreach($basesDisponibles as $base)
                        <option value="{{ $base->id }}">
                            {{ $base->client ? $base->client->nom : 'N/A' }} - {{ $base->nom_base }}
                        </option>
                    @endforeach
                </select>
                @if($basesDisponibles->count() == 0)
                    <small style="color: #94a3b8; font-size: 0.75rem;">Aucune base disponible (toutes sont déjà affectées).</small>
                @endif
            </div>

            <div id="create_client" style="margin-bottom: 1.25rem; display: none;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Entreprise (sans bases) *</label>
                <select name="client_id" id="client_id" style="width: 100%; padding: 0.75rem;">
                    <option value="">Sélectionner une entreprise</option>
                    @foreach($clientsDisponibles as $client)
                        <option value="{{ $client->id }}">{{ $client->nom }}</option>
                    @endforeach
                </select>
                @if($clientsDisponibles->count() == 0)
                    <small style="color: #94a3b8; font-size: 0.75rem;">Aucune entreprise sans base disponible.</small>
                @endif
            </div>
            
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeCreateModal()" style="padding: 0.65rem 1.1rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #64748b; border: none; font-weight: 700; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Créer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
}

function openEditModal(id) {
    const modal = document.getElementById('editModal' + id);
    modal.style.display = 'flex';
    
    // Déclencher le toggle pour afficher le bon champ (base ou client)
    const typeSelect = modal.querySelector('select[name="assignment_type"]');
    if (typeSelect) {
        toggleFields('edit' + id, typeSelect.value);
    }
}

function closeEditModal(id) {
    document.getElementById('editModal' + id).style.display = 'none';
}

function toggleFields(prefix, type) {
    const baseField = document.getElementById(prefix + '_base');
    const clientField = document.getElementById(prefix + '_client');
    
    if (type === 'base') {
        baseField.style.display = 'block';
        clientField.style.display = 'none';
    } else if (type === 'client') {
        baseField.style.display = 'none';
        clientField.style.display = 'block';
    } else {
        baseField.style.display = 'none';
        clientField.style.display = 'none';
    }
}
</script>
@endsection
