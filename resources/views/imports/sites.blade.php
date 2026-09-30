@extends('layouts.app')

@section('title', 'Import Excel - Sites')

@section('content')
<div class="header">
    <div>
        <a href="{{ route('clients.combined', ['view' => 'sites']) }}" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <h1><i class="fa-solid fa-file-excel" style="color: #f59e0b;"></i> Import Excel - Sites</h1>
        <p style="color: #64748b; margin-top: 0.5rem;">Importez en masse les sites depuis un fichier Excel.</p>
    </div>
    <a href="{{ route('imports.sites.template') }}" class="btn-primary" style="background-color: #f59e0b;">
        <i class="fa-solid fa-download"></i> Télécharger le Modèle
    </a>
</div>

<!-- Instructions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <h3 style="margin-bottom: 1.25rem; color: #1e293b; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem;">
        <i class="fa-solid fa-circle-info" style="color: #3b82f6;"></i> Instructions d'import
    </h3>
    <ol style="margin-left: 1.5rem; line-height: 2; color: #475569;">
        <li><strong>Téléchargez le modèle Excel</strong> en cliquant sur le bouton en haut à droite</li>
        <li><strong>Remplissez le fichier</strong> avec vos données :
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; list-style: disc;">
                <li><span style="color: #dc2626; font-weight: 700;">Colonnes obligatoires :</span> Code Site, Nom Site, <strong>Code Base OU Code Client</strong></li>
                <li><span style="color: #10b981; font-weight: 700;">Colonnes optionnelles :</span> Adresse, Ville, Téléphone, Observations</li>
            </ul>
        </li>
        <li><strong>Structure hiérarchique :</strong>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; list-style: circle;">
                <li><span style="color: #0ea5e9; font-weight: 700;">Structure normale (avec base) :</span> Remplir <code style="background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 0.25rem;">Code Base</code></li>
                <li><span style="color: #8b5cf6; font-weight: 700;">Structure directe (sans base) :</span> Remplir <code style="background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 0.25rem;">Code Client</code></li>
            </ul>
        </li>
        <li><strong>Enregistrez le fichier</strong> (format .xlsx ou .xls)</li>
        <li><strong>Importez le fichier</strong> via le formulaire ci-dessous</li>
    </ol>
    
    <div style="margin-top: 1.5rem; padding: 1rem; background-color: #fef3c7; border-radius: 0.5rem;">
        <p style="margin: 0; color: #92400e; font-weight: 600;">
            <i class="fa-solid fa-triangle-exclamation"></i> 
            <strong>Important :</strong> Si le code site existe déjà, le site sera mis à jour au lieu d'être créé.
        </p>
    </div>
</div>

<!-- Formulaire d'import -->
<div class="card">
    <h3 style="margin-bottom: 1.25rem; color: #1e293b; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem;">
        <i class="fa-solid fa-cloud-upload-alt" style="color: #f59e0b;"></i> Importer le Fichier Excel
    </h3>

    @if(session('success'))
    <div style="background-color: #d1fae5; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem;">
        <p style="margin: 0; color: #047857; font-weight: 700;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </p>
    </div>
    @endif

    @if(session('warning'))
    <div style="background-color: #fef3c7; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem;">
        <p style="margin: 0; color: #92400e; font-weight: 700;">
            <i class="fa-solid fa-exclamation-triangle"></i> {{ session('warning') }}
        </p>
    </div>
    @endif

    @if(session('error'))
    <div style="background-color: #fee2e2; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem;">
        <p style="margin: 0; color: #dc2626; font-weight: 700;">
            <i class="fa-solid fa-times-circle"></i> {{ session('error') }}
        </p>
    </div>
    @endif

    @if(session('errors') && is_array(session('errors')) && count(session('errors')) > 0)
    <div style="background-color: #fee2e2; border: 1px solid #fecaca; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem;">
        <h4 style="margin-bottom: 0.75rem; color: #dc2626;">
            <i class="fa-solid fa-list"></i> Erreurs détectées :
        </h4>
        <ul style="margin-left: 1.5rem; color: #991b1b; line-height: 1.6;">
            @foreach(session('errors') as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(isset($errors) && is_object($errors) && $errors->any())
    <div style="background-color: #fee2e2; border: 1px solid #fecaca; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem;">
        <h4 style="margin-bottom: 0.75rem; color: #dc2626;">
            <i class="fa-solid fa-exclamation-circle"></i> Erreurs de validation :
        </h4>
        <ul style="margin-left: 1.5rem; color: #991b1b; line-height: 1.6;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('imports.sites.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 700; color: #1e293b;">
                Fichier Excel (.xlsx, .xls, .csv) <span style="color: #dc2626;">*</span>
            </label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                   style="width: 100%; padding: 0.75rem; border: 2px dashed #cbd5e1; border-radius: 0.5rem; cursor: pointer;"
                   onchange="this.style.borderColor='#f59e0b';">
            <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #64748b;">
                <i class="fa-solid fa-info-circle"></i> Taille maximale : 10 MB
            </p>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('clients.combined', ['view' => 'sites']) }}" class="btn-secondary">
                <i class="fa-solid fa-times"></i> Annuler
            </a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-upload"></i> Lancer l'Import
            </button>
        </div>
    </form>
</div>
@endsection
