@extends('layouts.app')

@section('title', 'Assignment Requis')

@section('content')
<div style="max-width: 600px; margin: 4rem auto; text-align: center;">
    <div style="background-color: #fffbeb; border: 2px solid #fde68a; border-radius: 1rem; padding: 3rem;">
        <div style="font-size: 4rem; color: #b45309; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-link-slash"></i>
        </div>
        
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #92400e; margin-bottom: 1rem;">
            Assignment Manquant
        </h1>
        
        <p style="font-size: 0.95rem; color: #78350f; line-height: 1.6; margin-bottom: 2rem;">
            Votre compte <strong>Superviseur Soutarah</strong> n'est pas encore assigné à une base ou une company.
            <br><br>
            Contactez l'administrateur pour qu'il vous assigne un périmètre via le menu <strong>Assignments</strong>.
        </p>

        <div style="background-color: #ffffff; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 1.5rem; text-align: left;">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: #92400e; margin-bottom: 0.75rem;">
                <i class="fa-solid fa-info-circle"></i> Rôle du Superviseur Soutarah
            </h3>
            <ul style="font-size: 0.85rem; color: #78350f; line-height: 1.8; margin: 0; padding-left: 1.5rem;">
                <li>Possède les <strong>mêmes interfaces que l'admin</strong></li>
                <li>Voit et gère <strong>uniquement les données de SA base/company assignée</strong></li>
                <li>Valide les demandes d'intervention de son périmètre</li>
                <li>Crée et assigne des opérations techniques</li>
                <li>Aide l'admin à distribuer la charge de travail</li>
            </ul>
        </div>

        <a href="{{ route('profile.show') }}" style="display: inline-block; margin-top: 2rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; border-radius: 0.75rem; text-decoration: none; font-weight: 700;">
            <i class="fa-solid fa-user"></i> Voir Mon Profil
        </a>
    </div>
</div>
@endsection
