@extends('layouts.app')

@section('title', 'Modifier l\'Équipe')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('equipes.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
        <h1>Modifier l'Équipe : {{ $equipe->nom_equipe }}</h1>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('equipes.update', $equipe->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Nom de l'Équipe *</label>
            <input type="text" name="nom_equipe" value="{{ old('nom_equipe', $equipe->nom_equipe) }}" required placeholder="ex: Équipe Froid Nord, Équipe CVC Sud..." style="width: 100%; padding: 0.75rem;">
            @error('nom_equipe')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #0369a1; margin-bottom: 0.4rem; font-weight: 700;">
                <i class="fa-solid fa-user-tie"></i> Chef d'Équipe (un seul)
            </label>
            <select name="chef_equipe" id="chef_equipe" style="width: 100%; padding: 0.75rem; border: 2px solid #bae6fd; border-radius: 0.75rem;">
                <option value="">Aucun chef</option>
                @foreach($techniciens as $chef)
                <option value="{{ $chef->id }}" {{ $equipe->chef_equipe == $chef->id ? 'selected' : '' }}>
                    {{ $chef->nom_complet }}
                    @if($chef->isChefTechnicien())
                        👷 (Déjà chef)
                    @else
                        🔧 (Technicien simple)
                    @endif
                    @php
                        $nbEquipesChef = \App\Models\Equipe::where('chef_equipe', $chef->id)->where('id', '!=', $equipe->id)->count();
                    @endphp
                    @if($nbEquipesChef > 0)
                        - Chef de {{ $nbEquipesChef }} autre(s) équipe(s)
                    @endif
                </option>
                @endforeach
            </select>
            <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                <i class="fa-solid fa-lightbulb"></i> Le chef sera automatiquement membre de l'équipe. Ne le cochez pas dans la liste ci-dessous.
            </p>
        </div>

        <!-- Section Membres -->
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #059669; margin-bottom: 0.4rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;">
                <i class="fa-solid fa-users"></i> Autres Membres de l'Équipe
            </label>
            <div style="background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 0.75rem; padding: 1rem; max-height: 250px; overflow-y: auto;">
                @forelse($techniciens as $membre)
                <label data-user-id="{{ $membre->id }}" class="membre-checkbox" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; cursor: pointer; border-radius: 0.5rem; transition: background 0.2s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" name="membres[]" value="{{ $membre->id }}" 
                           {{ $equipe->membres->contains('id', $membre->id) && $equipe->chef_equipe != $membre->id ? 'checked' : '' }}
                           style="width: 18px; height: 18px; cursor: pointer; accent-color: #059669;">
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">{{ $membre->nom_complet }}</div>
                        <div style="font-size: 0.75rem; color: #059669; font-weight: 600;">{{ $membre->type_utilisateur }}</div>
                    </div>
                    @php
                        $autresEquipes = $membre->equipes->where('id', '!=', $equipe->id)->count();
                    @endphp
                    @if($autresEquipes > 0)
                    <span style="background: #fef3c7; color: #92400e; padding: 0.15rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 700;">
                        Dans {{ $autresEquipes }} autre(s) équipe(s)
                    </span>
                    @endif
                </label>
                @empty
                <p style="text-align: center; color: #059669; padding: 1rem; font-size: 0.85rem;">Aucun technicien disponible</p>
                @endforelse
            </div>
            <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                <i class="fa-solid fa-lightbulb"></i> Un membre peut appartenir à plusieurs équipes. Le chef est automatiquement inclus.
            </p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn-primary" style="flex: 1; justify-content: center;">
                <i class="fa-solid fa-save"></i> Enregistrer les Modifications
            </button>
            <a href="{{ route('equipes.index') }}" style="flex: 1; text-align: center; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; text-decoration: none; color: #64748b; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class="fa-solid fa-times"></i> Annuler
            </a>
        </div>
    </form>
</div>

<script>
// Masquer le chef sélectionné de la liste des membres
document.getElementById('chef_equipe').addEventListener('change', function() {
    const chefId = this.value;
    const allCheckboxes = document.querySelectorAll('.membre-checkbox');
    
    allCheckboxes.forEach(function(label) {
        const userId = label.getAttribute('data-user-id');
        const checkbox = label.querySelector('input[type="checkbox"]');
        
        if (chefId && userId === chefId) {
            // Masquer et décocher si c'est le chef
            label.style.display = 'none';
            checkbox.checked = false;
            checkbox.disabled = true;
        } else {
            // Afficher les autres
            label.style.display = 'flex';
            checkbox.disabled = false;
        }
    });
});

// Au chargement, vérifier si un chef est déjà sélectionné
document.addEventListener('DOMContentLoaded', function() {
    const chefSelect = document.getElementById('chef_equipe');
    if (chefSelect.value) {
        chefSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
