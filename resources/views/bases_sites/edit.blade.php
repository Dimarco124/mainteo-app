@extends('layouts.app')

@section('title', 'Modifier Base')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Modifier la Base</h1>
        <p>Mettre à jour les informations de la base d'intervention.</p>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    <form action="{{ route('bases-sites.update', $base->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                Entreprise Propriétaire <span style="color: #dc2626;">*</span>
            </label>
            <select name="client_id" required style="width: 100%; padding: 0.75rem;">
                @foreach($clients as $cl)
                <option value="{{ $cl->id }}" {{ $base->client_id == $cl->id ? 'selected' : '' }}>
                    {{ $cl->nom }} <span style="color: #64748b;">({{ $cl->code ?? 'CLI-'.$cl->id }})</span>
                </option>
                @endforeach
            </select>
            @error('client_id')
                <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Code Base <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="code_base" required value="{{ old('code_base', $base->code_base) }}" 
                       style="width: 100%; padding: 0.75rem;">
                @error('code_base')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;">
                    Nom de la Base <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="nom_base" required value="{{ old('nom_base', $base->nom_base) }}" 
                       style="width: 100%; padding: 0.75rem;">
                @error('nom_base')
                    <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('clients.combined', ['view' => 'bases']) }}" 
               style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.65rem 1.1rem; border-radius: 0.75rem; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Mettre à Jour
            </button>
        </div>
    </form>
</div>
@endsection
