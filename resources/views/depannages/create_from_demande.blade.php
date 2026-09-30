@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-wrench"></i> Créer Opération Technique
                        <small class="ms-2">depuis Demande #{{ $demande->numero_demande }}</small>
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Rappel de la demande -->
                    <div class="alert alert-info mb-4">
                        <h6><strong>Demande d'origine :</strong></h6>
                        @if($demande->site)
                        <p class="mb-1"><strong>Site:</strong> {{ $demande->site->nom_site }}</p>
                        @endif
                        <p class="mb-1"><strong>Équipement:</strong> {{ $demande->equipement->equipement_nom }}</p>
                        <p class="mb-1"><strong>Description:</strong> {{ $demande->description }}</p>
                        <p class="mb-0"><strong>Urgence:</strong> <span class="badge bg-warning">{{ ucfirst($demande->niveau_urgence) }}</span></p>
                    </div>

                    <form action="{{ route('depannages.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="demande_id" value="{{ $demande->id }}">
                        <input type="hidden" name="type_intervention" value="Dépannage">
                        <input type="hidden" name="equipement_id" value="{{ $demande->equipement_id }}">
                        <input type="hidden" name="description_panne" value="{{ $demande->description }}">
                        <input type="hidden" name="urgence" value="{{ ucfirst($demande->niveau_urgence) }}">

                        <h5 class="text-primary mb-3">Assignation de l'Équipe</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="equipe_id" class="form-label">Équipe *</label>
                                <select name="equipe_id" id="equipe_id" class="form-select @error('equipe_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner une équipe</option>
                                    @foreach($equipes as $equipe)
                                        <option value="{{ $equipe->id }}" {{ old('equipe_id') == $equipe->id ? 'selected' : '' }}>
                                            {{ $equipe->nom_equipe }} 
                                            @if($equipe->chef)
                                                (Chef: {{ $equipe->chef->nom_complet }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('equipe_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">
                                    <i class="fa-solid fa-lock"></i> Période d'Intervention Convenue (Fixe)
                                </label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="date_debut_prevue" class="form-label small text-muted">Date de Début</label>
                                        <input type="date" name="date_debut_prevue" id="date_debut_prevue" 
                                               class="form-control" readonly style="background-color: #f8fafc; cursor: not-allowed;"
                                               value="{{ old('date_debut_prevue', $demande->date_debut_souhaitee?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="date_fin_prevue" class="form-label small text-muted">Date de Fin</label>
                                        <input type="date" name="date_fin_prevue" id="date_fin_prevue" 
                                               class="form-control" readonly style="background-color: #f8fafc; cursor: not-allowed;"
                                               value="{{ old('date_fin_prevue', $demande->date_debut_souhaitee?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <small class="text-muted">Fixée selon la demande du client</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes Additionnelles</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('demandes.show', $demande) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer l'Opération Technique
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
