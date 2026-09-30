@extends('layouts.app')

@section('title', 'Gestion des Équipes')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Gestion des Équipes Terrain</h1>
        <p>{{ $canModify ? 'Organisez vos équipes de techniciens et nommez les chefs d\'équipe.' : 'Consultez les équipes de techniciens disponibles.' }}</p>
    </div>
    @if($canModify)
    <a href="{{ route('equipes.create') }}" class="btn-primary">
        <i class="fa-solid fa-users-gear"></i> Créer une Équipe
    </a>
    @endif
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem;">
    @forelse($equipes as $equipe)
    <div class="card" style="padding: 1.5rem;">
        <!-- En-tête de la carte équipe -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #059669, #10b981); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1.1rem;">
                    <i class="fa-solid fa-hard-hat"></i>
                </div>
                <div>
                    <strong style="font-size: 1rem; color: #0f172a;">{{ $equipe->nom_equipe }}</strong>
                    <div style="font-size: 0.8rem; color: #64748b;">
                        @php
                            // Compter UNIQUEMENT les membres (sans compter le chef séparément car il est déjà dans les membres)
                            $totalMembres = $equipe->membres->count();
                            $hasChef = $equipe->chef ? 1 : 0;
                        @endphp
                        {{ $totalMembres }} personne(s)
                        @if($hasChef)
                            <span style="color: #0369a1;">(dont 1 chef)</span>
                        @endif
                    </div>
                </div>
            </div>
            @if($canModify)
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('equipes.edit', $equipe->id) }}" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.35rem 0.65rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 700;">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form action="{{ route('equipes.destroy', $equipe->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer l\'équipe {{ $equipe->nom_equipe }} ? Cette action est irréversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.35rem 0.65rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Chef d'équipe -->
        <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 2px solid #bae6fd; border-radius: 0.75rem; padding: 0.75rem 1rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <div style="font-size: 0.75rem; color: #0369a1; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                    <i class="fa-solid fa-user-tie"></i> Chef d'Équipe
                </div>
                @if($canModify && $equipe->chef)
                    <form action="{{ route('equipes.unsetChef', $equipe->id) }}" method="POST" onsubmit="return confirm('Retirer le chef de cette équipe ? Le membre reste dans l\'équipe en tant que technicien simple.');" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="background: #fff; color: #be123c; border: 1px solid #fecdd3; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.68rem; font-weight: 700; cursor: pointer;">
                            <i class="fa-solid fa-crown" style="opacity: 0.5;"></i> Rétrograder
                        </button>
                    </form>
                @endif
            </div>
            @if($equipe->chef)
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: #0369a1; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #fff;">
                    {{ strtoupper(substr($equipe->chef->nom, 0, 1)) }}
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">{{ $equipe->chef->nom_complet }}</div>
                    <div style="font-size: 0.7rem; color: #0369a1; font-weight: 600;">
                        <i class="fa-solid fa-crown"></i> Chef
                        @if($equipe->chef->equipes && $equipe->chef->equipes->count() > 1)
                            · Chef de {{ $equipe->chef->equipes->count() }} équipes
                        @endif
                    </div>
                </div>
            </div>
            @else
            <p style="color: #0369a1; font-size: 0.85rem; font-style: italic;">Aucun chef désigné</p>
            @endif
        </div>

        <!-- Membres -->
        <div style="margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; color: #059669; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-users"></i> Autres Membres ({{ $equipe->membres->where('id', '!=', $equipe->chef_equipe)->count() }})
            </div>
            <div style="max-height: 220px; overflow-y: auto;">
                @forelse($equipe->membres->where('id', '!=', $equipe->chef_equipe) as $membre)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #f0fdf4; border: 1px solid #bbf7d0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #059669;">
                            {{ strtoupper(substr($membre->nom, 0, 1)) }}
                        </div>
                        <div style="flex: 1;">
                            <span style="font-size: 0.875rem; color: #334155; font-weight: 600;">{{ $membre->nom_complet }}</span>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                Membre
                                @if($membre->type_utilisateur === 'chef technicien')
                                     · <span style="color: #b45309;">👷 Responsable</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($canModify)
                    <div style="display: flex; gap: 0.25rem; align-items: center;">
                        @if($equipe->chef_equipe != $membre->id)
                        <form action="{{ route('equipes.setChef', ['equipe_id' => $equipe->id, 'user_id' => $membre->id]) }}" method="POST" onsubmit="return confirm('Désigner {{ $membre->nom_complet }} comme nouveau chef de l\'équipe ?');" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Promouvoir en chef" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.2rem 0.4rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 700; cursor: pointer;">
                                <i class="fa-solid fa-crown"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('equipes.removeMember', ['equipe_id' => $equipe->id, 'user_id' => $membre->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Retirer {{ $membre->nom_complet }} de cette équipe ?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #e11d48; font-size: 1.1rem; cursor: pointer; padding: 0.2rem 0.4rem; border-radius: 0.3rem;" title="Retirer du groupe">&times;</button>
                        </form>
                    </div>
                    @endif
                </div>
                @empty
                <p style="color: #94a3b8; font-size: 0.85rem; font-style: italic;">Aucun membre assigné</p>
                @endforelse
            </div>
        </div>

        <!-- Formulaire ajout membre -->
        @if($canModify)
        <form action="{{ route('equipes.addMember', $equipe->id) }}" method="POST" style="border-top: 1px solid #e2e8f0; padding-top: 1rem;">
            @csrf
            <div style="display: flex; gap: 0.5rem;">
                <select name="user_id" required style="flex: 1; padding: 0.6rem 0.75rem; font-size: 0.875rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                    <option value="">Ajouter un membre...</option>
                    @foreach($techniciens as $tech)
                        @php
                            $estDejaDans = $equipe->membres->contains('id', $tech->id) || $equipe->chef_equipe == $tech->id;
                        @endphp
                        @if(!$estDejaDans)
                        <option value="{{ $tech->id }}">
                            {{ $tech->nom_complet }}
                            @if($tech->isChefTechnicien())
                                👷
                            @endif
                            ({{ $tech->type_utilisateur }})
                        </option>
                        @endif
                    @endforeach
                </select>
                <button type="submit" class="btn-primary" style="padding: 0.6rem 1rem; font-size: 0.875rem;">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
            <p style="font-size: 0.7rem; color: #64748b; margin-top: 0.5rem; margin-bottom: 0;">
                <i class="fa-solid fa-info-circle"></i> Un membre peut être dans plusieurs équipes
            </p>
        </form>
        @endif
    </div>
    @empty
    <div class="card" style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 3rem;">
        Aucune équipe créée. Commencez par créer votre première équipe.
    </div>
    @endforelse
</div>
@endsection
