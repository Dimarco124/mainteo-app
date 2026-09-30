@extends('layouts.app')

@section('title', 'Signaler une Panne / Demande d\'Intervention')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('depannages.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
        <h1>
            @if(Auth::user()->type_utilisateur === 'utilisateur')
                Signaler une Panne sur votre Site
            @else
                Créer / Signaler une Demande d'Intervention
            @endif
        </h1>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('depannages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Type d'Intervention -->
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                Type d'Intervention * <span style="color: #be123c;">(Obligatoire)</span>
            </label>
            <select name="type_intervention" required style="width: 100%; padding: 0.75rem; background-color: #f8fafc; border: 2px solid #cbd5e1; font-weight: 600;">
                <option value="">-- Sélectionner le type --</option>
                <option value="Dépannage">🔧 Dépannage (Réparation d'une panne)</option>
                <option value="Installation">⚙️ Installation (Mise en place d'un nouvel équipement)</option>
            </select>
            @error('type_intervention')
            <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <!-- Sélection de l'Équipement concerné -->
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                Équipement Concerné *
            </label>
            <select name="equipement_id" id="equipement_select" style="width: 100%; padding: 0.75rem;">
                <option value="">Sélectionner un équipement dans votre parc...</option>
                @foreach($equipements as $eq)
                <option value="{{ $eq->id }}" data-site="{{ $eq->site_id }}">
                    {{ $eq->equipement_code }} — {{ $eq->equipement_nom }} ({{ $eq->marque ?? 'N/A' }} {{ $eq->modele ?? '' }}) {{ $eq->baseSite ? '— Site: '.$eq->baseSite->nom_base : '' }}
                </option>
                @endforeach
            </select>
            <div style="margin-top: 0.4rem; font-size: 0.75rem; color: #94a3b8;">
                Ou saisissez manuellement la référence si l'équipement n'est pas dans la liste :
            </div>
            <input type="text" name="equipement_reference" placeholder="ex: Groupe Froid Daikin #EQ-984" style="width: 100%; padding: 0.6rem; margin-top: 0.3rem;">
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
            <!-- Urgence -->
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Niveau d'Urgence *</label>
                <select name="urgence" required style="width: 100%; padding: 0.75rem;">
                    <option value="Urgent">🚨 Urgent (Arrêt de production / Fuite)</option>
                    <option value="Normal" selected>⚠️ Normal (Bruit anormal / Révision)</option>
                    <option value="Faible">ℹ️ Faible (Entretien programmable)</option>
                </select>
            </div>

            <!-- Date de début souhaitée -->
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Date de Début Souhaitée</label>
                <input type="date" name="date_debut_souhaitee" id="date_debut_souhaitee" min="{{ date('Y-m-d') }}" value="{{ old('date_debut_souhaitee') }}" style="width: 100%; padding: 0.75rem;">
                @error('date_debut_souhaitee')
                    <span style="color: #be123c; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Date de fin souhaitée -->

        </div>

        @if(Auth::user()->type_utilisateur === 'admin' && !empty($techniciens))
        <!-- Option d'assignation directe RÉSERVÉE À L'ADMIN UNIQUEMENT -->
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
            <div style="font-size: 0.85rem; font-weight: 700; color: #047857; margin-bottom: 0.75rem;">
                <i class="fa-solid fa-user-gear"></i> Affectation Immédiate (Optionnel - Admin uniquement)
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem;">Technicien Assigné</label>
                    <select name="technicien_id" style="width: 100%; padding: 0.65rem;">
                        <option value="">Affecter plus tard</option>
                        @foreach($techniciens as $t)
                        <option value="{{ $t->id }}">{{ $t->nom_complet }} ({{ $t->type_utilisateur }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem;">Date Prevue d'Intervention</label>
                    <input type="date" name="date_prevue" style="width: 100%; padding: 0.65rem;">
                </div>
            </div>
        </div>
        @endif

        <!-- Description du problème -->
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">Description détaillée du Problème / Constat *</label>
            <textarea name="description_panne" rows="4" required placeholder="Décrire les symptômes constatés, les codes d'erreurs, le bruit, l'emplacement exact..." style="width: 100%; padding: 0.75rem;"></textarea>
        </div>

        <!-- Pièces Jointes : Photo & Document -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    <i class="fa-solid fa-camera" style="color: #059669;"></i> Photo de la Panne (Image)
                </label>
                <input type="file" name="photo_panne" accept="image/*" style="width: 100%; padding: 0.5rem; background: #fff; border: 1px dashed #cbd5e1; border-radius: 0.6rem;">
                <span style="font-size: 0.75rem; color: #94a3b8;">PNG, JPG, WEBP (Max 10Mo)</span>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 0.4rem; font-weight: 600;">
                    <i class="fa-solid fa-paperclip" style="color: #059669;"></i> Fichier Joint (PDF / Doc / Photo)
                </label>
                <input type="file" name="fichier_joint" accept=".pdf,.doc,.docx,.jpg,.png,.zip" style="width: 100%; padding: 0.5rem; background: #fff; border: 1px dashed #cbd5e1; border-radius: 0.6rem;">
                <span style="font-size: 0.75rem; color: #94a3b8;">PDF, Word, Image, Zip (Max 10Mo)</span>
            </div>
        </div>

        <button type="submit" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #ffffff; border: none; padding: 0.85rem 1.5rem; border-radius: 0.75rem; font-weight: 700; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 10px rgba(190, 18, 60, 0.2); display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-paper-plane"></i> Transmettre la Demande d'Intervention
        </button>
    </form>
</div>
@endsection
