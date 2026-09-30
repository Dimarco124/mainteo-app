@extends('layouts.app')

@section('title', 'Planning des Interventions')

@section('content')

<div class="header">
    <div class="page-title">
        @if(in_array(Auth::user()->type_utilisateur, ['technicien', 'chef technicien']))
            <h1>📅 Mon Planning d'Interventions</h1>
            <p>Consultez votre calendrier et les interventions qui vous sont assignées.</p>
        @elseif(Auth::user()->type_utilisateur === 'demandeur')
            <h1>📅 Planning des Interventions & Maintenances</h1>
            <p>Consultez les interventions et maintenances préventives programmées sur vos sites.</p>
        @else
            <h1>Planning des Maintenances</h1>
            <p>Planifiez les maintenances préventives et programmées.</p>
        @endif
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <!-- Bouton Impression / PDF -->
        <button onclick="openPrintModal()" class="btn-primary" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
            <i class="fa-solid fa-print"></i> Imprimer / PDF
        </button>
        
        @if(in_array(Auth::user()->type_utilisateur, ['admin', 'superviseur_soutarah']))
        <a href="{{ route('maintenances.create') }}" class="btn-primary">
            <i class="fa-solid fa-calendar-plus"></i> Planifier une Maintenance
        </a>
        @endif
    </div>
</div>

<!-- Modal Impression/PDF -->
<div id="exportModal" class="notif-popup-overlay" style="display: none;">
    <div class="notif-popup-card" style="max-width: 400px;">
        <button class="notif-popup-close-x" onclick="closePrintModal()">
            <i class="fa-solid fa-times"></i>
        </button>
        
        <div class="notif-popup-icon" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
            <i class="fa-solid fa-print"></i>
        </div>
        
        <h3 class="notif-popup-title">Imprimer le Planning</h3>
        <p class="notif-popup-message">
            Sélectionnez le mois à imprimer. Une nouvelle fenêtre s'ouvrira avec une vue optimisée pour l'impression que vous pourrez sauvegarder en PDF.
        </p>
        
        <form id="printForm" action="{{ route('planning.print') }}" method="GET" target="_blank">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem;">
                    Mois
                </label>
                <select name="month" required style="width: 100%; padding: 0.6rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.4rem;">
                    Année
                </label>
                <select name="year" required style="width: 100%; padding: 0.6rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div class="notif-popup-actions">
                <button type="submit" class="notif-popup-btn-primary" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);">
                    <i class="fa-solid fa-print"></i> Ouvrir l'aperçu d'impression
                </button>
                <button type="button" class="notif-popup-btn-close" onclick="closePrintModal()">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Légende des Couleurs par Type d'Intervention -->
<div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; background: #ffffff; padding: 0.85rem 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
    <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; display: flex; align-items: center; margin-right: 0.5rem;">
        <i class="fa-solid fa-tags" style="margin-right: 0.35rem;"></i> Légende :
    </span>
    <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; color: #991b1b; background: #fef2f2; padding: 0.25rem 0.65rem; border-radius: 0.5rem; border: 1px solid #fecaca;">
        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #dc2626; display: inline-block;"></span> Dépannage
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; color: #b45309; background: #fffbeb; padding: 0.25rem 0.65rem; border-radius: 0.5rem; border: 1px solid #fde68a;">
        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #f59e0b; display: inline-block;"></span> Installation
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; color: #1d4ed8; background: #eff6ff; padding: 0.25rem 0.65rem; border-radius: 0.5rem; border: 1px solid #bfdbfe;">
        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #3b82f6; display: inline-block;"></span> Maintenance
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; color: #047857; background: #ecfdf5; padding: 0.25rem 0.65rem; border-radius: 0.5rem; border: 1px solid #a7f3d0;">
        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981; display: inline-block;"></span> Validé Client
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; color: #991b1b; background: #fee2e2; padding: 0.25rem 0.65rem; border-radius: 0.5rem; border: 1px dashed #f87171;">
        <span style="font-size: 0.85rem;">🚫</span> Jour non ouvrable (Repos / Férié)
    </div>
</div>

<!-- Calendrier FullCalendar -->
<div class="card calendar-card" style="margin-bottom: 1.75rem;">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-calendar-days"></i> Calendrier des Interventions</h3>
    </div>
    <div class="calendar-card-body" style="padding: 1rem;">
        <div class="calendar-scroll-wrapper">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Tableau des Maintenances avec Onglets par Statut -->
<div class="card" style="margin-bottom: 1.75rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h3 class="card-title"><i class="fa-solid fa-wrench"></i> Maintenances</h3>
        
        <!-- Onglets de Filtrage -->
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button class="tab-btn active" data-status="all" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #059669; color: white; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                Toutes ({{ $maintenances->count() }})
            </button>
            <button class="tab-btn" data-status="planifiée" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                Planifiées ({{ $maintenances->where('statut', 'planifiée')->count() }})
            </button>
            <button class="tab-btn" data-status="confirmée_client" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                Confirmées ({{ $maintenances->where('statut', 'confirmée_client')->count() }})
            </button>
            <button class="tab-btn" data-status="en_cours" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                En Cours ({{ $maintenances->where('statut', 'en_cours')->count() }})
            </button>
            <button class="tab-btn" data-status="terminée" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                Terminées ({{ $maintenances->where('statut', 'terminée')->count() }})
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>N° Maintenance</th>
                    <th>Type</th>
                    <th>Équipement</th>
                    <th>Client / Site</th>
                    <th>Équipe / Technicien</th>
                    <th>Date Prévue</th>
                    <th>Statut</th>
                    <th style="text-align: right; white-space: nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="maintenances-tbody">
                @forelse($maintenances as $m)
                <tr data-status="{{ $m->statut }}">
                    <td>
                        <a href="{{ route('maintenances.show', $m->id) }}" style="color: #059669; font-weight: 700; text-decoration: none;">
                            {{ $m->numero_maintenance }}
                        </a>
                    </td>
                    <td>
                        @if($m->type_maintenance === 'préventive')
                        <span style="font-size: 0.8rem; color: #1d4ed8;">🛡️ Préventive</span>
                        @else
                        <span style="font-size: 0.8rem; color: #b45309;">🔧 Corrective</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $m->equipement->equipement_nom ?? 'N/A' }}</strong><br>
                        <small style="color: #64748b;">{{ $m->equipement->equipement_code ?? '' }}</small>
                    </td>
                    <td>
                        <strong>{{ $m->client->nom ?? 'N/A' }}</strong><br>
                        <small style="color: #64748b;">
                            <i class="fa-solid fa-location-dot"></i>
                            {{ $m->site->nom_site ?? ($m->base->nom_base ?? 'N/A') }}
                        </small>
                    </td>
                    <td>
                        @if($m->equipes && $m->equipes->isNotEmpty())
                            <span style="color: #059669; font-weight: 600;">
                                <i class="fa-solid fa-users"></i> {{ $m->equipes->pluck('nom_equipe')->join(', ') }}
                            </span>
                        @elseif($m->equipe)
                            <span style="color: #059669; font-weight: 600;">
                                <i class="fa-solid fa-users"></i> {{ $m->equipe->nom_equipe }}
                            </span>
                        @elseif($m->technicien)
                            <span style="color: #0369a1; font-weight: 600;">
                                <i class="fa-solid fa-user"></i> {{ $m->technicien->nom_complet }}
                            </span>
                        @else
                            <span style="color: #94a3b8;">Non assigné</span>
                        @endif
                    </td>
                    <td><code>{{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y H:i') }}</code></td>
                    <td>
                        @if($m->statut === 'planifiée')
                            <span class="badge badge-warning">⏳ Planifiée</span>
                        @elseif($m->statut === 'confirmée_client')
                            <span class="badge badge-info">✔️ Confirmée</span>
                        @elseif($m->statut === 'en_cours')
                            <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">🔄 En Cours</span>
                        @elseif($m->statut === 'terminée')
                            @if($m->statut_validation_client === 'validé')
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">✅ Validée Client</span>
                            @else
                                <span class="badge badge-success">✅ Terminée</span>
                            @endif
                        @elseif($m->statut === 'annulée')
                            <span class="badge badge-danger">❌ Annulée</span>
                        @else
                            <span class="badge" style="background: #f1f5f9; color: #64748b;">{{ $m->statut }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 0.35rem; white-space: nowrap;">
                        {{-- Superviseur Client : Confirmer --}}
                        @if(Auth::user()->isSuperviseurClient() && $m->statut === 'planifiée')
                        <form action="{{ route('maintenances.confirmer', $m->id) }}" method="POST" style="display: inline; margin: 0;">
                            @csrf
                            <button type="submit" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="fa-solid fa-check"></i> Confirmer
                            </button>
                        </form>
                        
                        {{-- Technicien : Démarrer (seulement si chef de l'équipe assignée) --}}
                        @elseif(Auth::user()->isTechnicien() && $m->statut === 'confirmée_client')
                            @php
                                $dateDebutPrevue = \Carbon\Carbon::parse($m->date_debut_prevue);
                                $maintenant = \Carbon\Carbon::now();
                                $peutDemarrer = $maintenant->gte($dateDebutPrevue);
                                $isChefDeCette = Auth::user()->isChefTechnicien()
                                    && (
                                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                                    );
                            @endphp
                            @if($isChefDeCette)
                                @if($peutDemarrer)
                                <form action="{{ route('maintenances.demarrer', $m->id) }}" method="POST" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <i class="fa-solid fa-play"></i> Démarrer
                                    </button>
                                </form>
                                @else
                                <button type="button" disabled style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; cursor: not-allowed; opacity: 0.6; display: inline-flex; align-items: center; gap: 0.3rem;" title="Démarrage prévu le {{ $dateDebutPrevue->format('d/m/Y à H:i') }}">
                                    <i class="fa-solid fa-clock"></i> Programmé
                                </button>
                                @endif
                            @endif
                        
                        {{-- Technicien : Terminer (seulement si chef et tous équipements traités) --}}
                        @elseif(Auth::user()->isTechnicien() && $m->statut === 'en_cours')
                            @php
                                $isChefDeCette = Auth::user()->isChefTechnicien()
                                    && (
                                        ($m->equipe && $m->equipe->chef_equipe == Auth::id())
                                        || ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                                    );
                                $isTermineDeCette = ($m->nombre_equipements_prevus > 0 && $m->nombre_equipements_restants == 0);
                            @endphp
                            @if($isChefDeCette && $isTermineDeCette)
                            <a href="{{ route('maintenances.rapportForm', $m->id) }}" style="background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="fa-solid fa-flag-checkered"></i> Terminer
                            </a>
                            @endif
                        
                        {{-- Admin ou Superviseur Soutarah : Voir, Modifier, Supprimer --}}
                        @elseif(Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah())
                            <a href="{{ route('maintenances.show', $m->id) }}" title="Voir les détails" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                            <a href="{{ route('maintenances.edit', $m->id) }}" title="Modifier la maintenance" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="fa-solid fa-pen-to-square"></i> Modifier
                            </a>
                            <form action="{{ route('maintenances.destroy', $m->id) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Confirmer la suppression de la maintenance {{ $m->numero_maintenance }} ? Cette action est irréversible.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Supprimer la maintenance" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </button>
                            </form>

                        {{-- Autres rôles : Voir détails --}}
                        @else
                            <a href="{{ route('maintenances.show', $m->id) }}" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                        @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 3rem;">
                        <i class="fa-solid fa-wrench" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Aucune maintenance planifiée pour le moment.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/fr.global.min.js"></script>

<style>
/* Conteneur et Défilement Horizontal pour FullCalendar */
.calendar-scroll-wrapper {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#calendar {
    width: 100%;
    min-height: 380px;
}

.calendar-card {
    max-width: 100%;
    overflow: hidden;
}

@media (max-width: 768px) {
    .calendar-card-body {
        padding: 0.5rem 0.25rem !important;
    }

    /* Permet le défilement horizontal fluide de la vue mois sur mobile */
    #calendar {
        min-width: 600px !important;
    }

    .fc .fc-toolbar {
        flex-direction: column !important;
        align-items: center !important;
        gap: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }

    .fc .fc-toolbar-chunk {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        gap: 0.2rem !important;
    }

    .fc .fc-toolbar-title {
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        text-align: center !important;
        color: #0f172a;
    }

    .fc .fc-button {
        padding: 0.25rem 0.45rem !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        border-radius: 0.375rem !important;
    }

    .fc-theme-standard .fc-scrollgrid {
        border-radius: 0.375rem;
    }

    .fc-col-header-cell-cushion {
        font-size: 0.7rem !important;
        padding: 3px !important;
    }

    .fc .fc-daygrid-day-number {
        font-size: 0.72rem !important;
        padding: 2px 4px !important;
    }

    .fc .fc-daygrid-day-frame {
        min-height: 48px !important;
    }

    .fc .fc-event {
        font-size: 0.7rem !important;
        padding: 2px 4px !important;
        border-radius: 3px !important;
        line-height: 1.2 !important;
    }

    .fc-list-event-title, .fc-list-event-time {
        font-size: 0.8rem !important;
    }
}
</style>

<script>
// Filtrage des maintenances par statut
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tbody = document.getElementById('maintenances-tbody');
    const rows = tbody.querySelectorAll('tr[data-status]');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            
            // Mettre à jour les styles des boutons
            tabBtns.forEach(b => {
                b.style.background = '#f8fafc';
                b.style.color = '#64748b';
                b.classList.remove('active');
            });
            this.style.background = '#059669';
            this.style.color = 'white';
            this.classList.add('active');
            
            // Filtrer les lignes
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});

// FullCalendar
document.addEventListener('DOMContentLoaded', function () {
    var isMobile = window.innerWidth <= 768;
    var calendarEl = document.getElementById('calendar');
    var canManageMaintenances = {{ (Auth::user()->isAdmin() || Auth::user()->isSuperviseurSoutarah()) ? 'true' : 'false' }};
    var csrfToken = '{{ csrf_token() }}';

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Toujours afficher en vue mois, même sur mobile
        locale: 'fr',
        headerToolbar: isMobile
            ? { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' }
            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        buttonText: {
            today: isMobile ? 'Auj.' : "Aujourd'hui",
            month: 'Mois',
            week: 'Semaine',
            list: 'Liste'
        },
        themeSystem: 'standard',
        events: '{{ route('planning.events') }}',
        height: isMobile ? 'auto' : 600,
        eventClick: function(info) {
            var props = info.event.extendedProps;
            var isMaintenance = info.event.id.startsWith('maintenance_') || props.is_non_working_day;
            
            // Données générales
            var typeTitle = props.is_non_working_day 
                ? 'Jour non ouvrable (Repos / Férié)' 
                : (props.type || (isMaintenance ? 'Maintenance' : 'Intervention'));
            var mainTitle = props.is_non_working_day
                ? 'Jour sauté pour la ' + (props.numero ? 'maintenance ' + props.numero : 'maintenance')
                : (info.event.title || 'Détails de l\'intervention');
            var clientName = props.client_nom || '';
            var siteName = props.site || 'Non spécifié';
            if (props.base) {
                siteName += ' (' + props.base + ')';
            }

            var equipeName = props.nom_equipe || 'Non assignée';
            if (props.chef_equipe) {
                equipeName += '<br><small style="color: #64748b; font-size: 0.78rem;">Chef : ' + props.chef_equipe + '</small>';
            }
            if (props.technicien && props.technicien !== 'Non affecté' && props.technicien !== 'Non assigné' && !equipeName.includes(props.technicien)) {
                equipeName += '<br><small style="color: #64748b; font-size: 0.78rem;">🔧 ' + props.technicien + '</small>';
            }
            
            // Formatage de la période
            var periodeText = 'Non planifiée';
            if (info.event.start) {
                var sDate = new Date(info.event.start);
                periodeText = sDate.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
                if (info.event.end) {
                    var eDate = new Date(info.event.end);
                    periodeText += ' au ' + eDate.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
                }
            }

            // Statut badge styling
            var statutText = props.statut || 'Planifié';
            var statutBg = '#fffbeb';
            var statutColor = '#b45309';
            var statutLower = statutText.toLowerCase();
            if (statutLower.includes('cours')) {
                statutBg = '#eff6ff'; statutColor = '#1d4ed8';
            } else if (statutLower.includes('planifi') || statutLower.includes('attente')) {
                statutBg = '#fffbeb'; statutColor = '#b45309';
            } else if (statutLower.includes('termin') || statutLower.includes('resolu') || statutLower.includes('résolu') || statutLower.includes('closed') || statutLower.includes('clôtur') || statutLower.includes('clotur')) {
                statutBg = '#ecfdf5'; statutColor = '#047857';
            } else if (statutLower.includes('valid')) {
                statutBg = '#ecfdf5'; statutColor = '#047857';
            }

            // Urgence badge
            var urgBadge = '';
            var urgence = (props.urgence || '').toLowerCase();
            if (urgence === 'urgent' || urgence === 'critique') {
                urgBadge = '<span style="font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.55rem; border-radius: 9999px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;"><i class="fa-solid fa-fire"></i> Urgent</span>';
            } else if (urgence === 'faible') {
                urgBadge = '<span style="font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.55rem; border-radius: 9999px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Faible</span>';
            } else if (props.urgence) {
                urgBadge = '<span style="font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.55rem; border-radius: 9999px; background: #fffbeb; color: #d97706; border: 1px solid #fde68a;">Normal</span>';
            }

            // RI Soutarah badge
            var riBadge = '';
            if (props.ri_soutarah) {
                riBadge = '<span style="font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.6rem; border-radius: 9999px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;"><i class="fa-solid fa-barcode"></i> ' + props.ri_soutarah + '</span>';
            }

            // Bloc Équipement à dépanner / intervenir
            var equipementBlock = '';
            var eqCode = props.equipement_code || '';
            var eqNom = props.equipement_nom || '';
            if (eqCode || eqNom) {
                var eqExtra = '';
                if (props.equipement_marque || props.equipement_modele) {
                    var mm = [props.equipement_marque, props.equipement_modele].filter(Boolean).join(' - ');
                    eqExtra = '<div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;"><i class="fa-solid fa-tag" style="color: #94a3b8;"></i> Modèle : ' + mm + '</div>';
                }
                equipementBlock = '<div style="background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 0.75rem; padding: 0.85rem 1rem; box-shadow: 0 1px 2px rgba(37,99,235,0.04);">'
                    + '<div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #1d4ed8; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">'
                    + '<i class="fa-solid fa-gear"></i> Équipement Concerné'
                    + '</div>'
                    + '<div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; line-height: 1.4;">'
                    + (eqCode ? '<span style="background: #0f172a; color: #fff; padding: 0.2rem 0.55rem; border-radius: 0.4rem; font-family: monospace; font-size: 0.88rem; margin-right: 0.45rem;">' + eqCode + '</span>' : '')
                    + (eqNom || '')
                    + '</div>'
                    + eqExtra
                    + '</div>';
            }

            // Demandeur
            var demandeurBlock = '';
            if (props.demandeur_nom) {
                var telHtml = '';
                if (props.demandeur_tel) {
                    telHtml = '<a href="tel:' + props.demandeur_tel + '" style="font-size: 0.75rem; color: #059669; font-weight: 700; text-decoration: none; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 0.4rem; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 0.25rem;"><i class="fa-solid fa-phone"></i> ' + props.demandeur_tel + '</a>';
                }
                demandeurBlock = '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 0.9rem;">'
                    + '<div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">'
                    + '<i class="fa-solid fa-user" style="color: #64748b;"></i> Demandeur'
                    + '</div>'
                    + '<div style="font-size: 0.88rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; flex-wrap: wrap;">'
                    + '<span>' + props.demandeur_nom + '</span>'
                    + telHtml
                    + '</div>'
                    + '</div>';
            }



            // Progression (si Maintenance avec équipements)
            var progressionBlock = '';
            if (isMaintenance && props.nombre_equipements_prevus !== undefined && props.nombre_equipements_prevus > 0) {
                var progression = props.pourcentage_avancement || 0;
                var traites = props.nombre_equipements_traites || 0;
                var prevus = props.nombre_equipements_prevus || 0;

                progressionBlock = '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.9rem 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">'
                    + '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">'
                    + '<span style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: #475569; display: flex; align-items: center; gap: 0.4rem;">'
                    + '<i class="fa-solid fa-chart-pie" style="color: #059669;"></i> Avancement des travaux'
                    + '</span>'
                    + '<span style="font-size: 0.82rem; font-weight: 800; color: #059669; background: #ecfdf5; padding: 0.2rem 0.6rem; border-radius: 9999px;">'
                    + progression + '%'
                    + '</span>'
                    + '</div>'
                    + '<div style="background: #e2e8f0; border-radius: 9999px; height: 8px; overflow: hidden; margin-bottom: 0.5rem;">'
                    + '<div style="background: linear-gradient(90deg, #10b981, #059669); height: 100%; width: ' + progression + '%; border-radius: 9999px; transition: width 0.3s ease;"></div>'
                    + '</div>'
                    + '<div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #64748b;">'
                    + '<span>Équipements traités : <strong style="color: #0f172a;">' + traites + '</strong> / ' + prevus + '</span>'
                    + '<span>Restants : <strong style="color: #d97706;">' + Math.max(0, prevus - traites) + '</strong></span>'
                    + '</div>'
                    + '</div>';
            }

            // Boutons d'action horizontaux
            var actionsHtml = '';
            if (isMaintenance && props.maintenance_id) {
                actionsHtml += '<a href="/maintenances/' + props.maintenance_id + '" style="padding: 0.5rem 0.9rem; background: #059669; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(5,150,105,0.2);"><i class="fa-solid fa-eye"></i> Consulter la Fiche</a>';
                
                if (canManageMaintenances) {
                    actionsHtml += '<a href="/maintenances/' + props.maintenance_id + '/edit" style="padding: 0.5rem 0.9rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 0.5rem; text-decoration: none; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>';
                    actionsHtml += '<form action="/maintenances/' + props.maintenance_id + '" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm(\'Confirmer la suppression de cette maintenance ? Cette action est irréversible.\');">'
                        + '<input type="hidden" name="_token" value="' + csrfToken + '">'
                        + '<input type="hidden" name="_method" value="DELETE">'
                        + '<button type="submit" style="padding: 0.5rem 0.9rem; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 0.5rem; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-trash"></i> Supprimer</button>'
                        + '</form>';
                }
            } else if (props.depannage_id) {
                actionsHtml += '<a href="/depannages/' + props.depannage_id + '" style="padding: 0.5rem 1rem; background: #059669; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(5,150,105,0.2);"><i class="fa-solid fa-eye"></i> Consulter l\'Intervention</a>';
                if (props.demande_id) {
                    actionsHtml += '<a href="/demandes/' + props.demande_id + '" style="padding: 0.5rem 0.9rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 0.5rem; text-decoration: none; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-file-lines"></i> Demande</a>';
                }
            } else if (props.demande_id) {
                actionsHtml += '<a href="/demandes/' + props.demande_id + '" style="padding: 0.5rem 1rem; background: #2563eb; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-eye"></i> Consulter la Demande</a>';
            }

            var modal = document.createElement('div');
            modal.className = 'fc-custom-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.55);display:flex;align-items:center;justify-content:center;z-index:10000;padding:1rem;backdrop-filter:blur(4px);animation:fadeIn 0.15s ease-out;';
            modal.onclick = function(e) { if (e.target === modal) modal.remove(); };

            modal.innerHTML = '<div style="background: #ffffff; border-radius: 1rem; max-width: 560px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e2e8f0; animation: scaleUp 0.18s ease-out;">'
                + '<!-- En-tête modal -->'
                + '<div style="padding: 1.15rem 1.4rem; background: #ffffff; border-bottom: 1px solid #f1f5f9; display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem;">'
                + '<div style="display: flex; align-items: flex-start; gap: 0.75rem;">'
                + '<div style="width: 42px; height: 42px; border-radius: 10px; background: ' + (isMaintenance ? '#ecfdf5' : '#eff6ff') + '; color: ' + (isMaintenance ? '#059669' : '#2563eb') + '; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; margin-top: 2px;">'
                + '<i class="fa-solid fa-' + (isMaintenance ? 'screwdriver-wrench' : 'wrench') + '"></i>'
                + '</div>'
                + '<div>'
                + '<div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.35rem;">'
                + '<span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: ' + (isMaintenance ? '#059669' : '#2563eb') + ';">' + typeTitle + '</span>'
                + '<span style="font-size: 0.72rem; font-weight: 700; padding: 0.12rem 0.55rem; border-radius: 9999px; background: ' + statutBg + '; color: ' + statutColor + ';">' + statutText + '</span>'
                + urgBadge
                + riBadge
                + '</div>'
                + '<h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; line-height: 1.35;">' + mainTitle + '</h3>'
                + (clientName ? '<div style="font-size: 0.8rem; color: #059669; font-weight: 700; margin-top: 0.25rem;"><i class="fa-solid fa-building"></i> ' + clientName + '</div>' : '')
                + '</div>'
                + '</div>'
                + '<button onclick="this.closest(\'.fc-custom-modal\').remove()" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s;" onmouseover="this.style.background=\'#fee2e2\';this.style.color=\'#ef4444\';" onmouseout="this.style.background=\'#f8fafc\';this.style.color=\'#64748b\';">'
                + '<i class="fa-solid fa-xmark"></i>'
                + '</button>'
                + '</div>'
                + '<!-- Corps du modal -->'
                + '<div style="padding: 1.25rem 1.4rem; display: flex; flex-direction: column; gap: 0.75rem; background: #fafbfc; max-height: 70vh; overflow-y: auto;">'
                + equipementBlock
                + '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">'
                + '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 0.9rem;">'
                + '<div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">'
                + '<i class="fa-solid fa-location-dot" style="color: #059669;"></i> Site / Emplacement'
                + '</div>'
                + '<div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">' + siteName + '</div>'
                + '</div>'
                + '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 0.9rem;">'
                + '<div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">'
                + '<i class="fa-solid fa-users" style="color: #2563eb;"></i> Équipe / Intervenant'
                + '</div>'
                + '<div style="font-size: 0.88rem; font-weight: 700; color: #1e293b;">' + equipeName + '</div>'
                + '</div>'
                + '</div>'
                + '<div style="display: grid; grid-template-columns: ' + (demandeurBlock ? '1fr 1fr' : '1fr') + '; gap: 0.75rem;">'
                + '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 0.9rem;">'
                + '<div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">'
                + '<i class="fa-solid fa-calendar-days" style="color: #d97706;"></i> Période planifiée'
                + '</div>'
                + '<div style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">' + periodeText + '</div>'
                + '</div>'
                + demandeurBlock
                + '</div>'
                + progressionBlock
                + '</div>'
                + '<!-- Footer modal -->'
                + '<div style="padding: 0.9rem 1.4rem; background: #ffffff; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">'
                + '<button onclick="this.closest(\'.fc-custom-modal\').remove()" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: all 0.15s;">'
                + 'Fermer'
                + '</button>'
                + '<div style="display: inline-flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">'
                + actionsHtml
                + '</div>'
                + '</div>'
                + '</div>';

            document.body.appendChild(modal);
            
            // Ajouter les animations CSS si nécessaire
            if (!document.getElementById('modalAnimation')) {
                var style = document.createElement('style');
                style.id = 'modalAnimation';
                style.textContent = '@keyframes scaleUp { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } } @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }';
                document.head.appendChild(style);
            }
        },
        eventDidMount: function(info) {
            var props = info.event.extendedProps;
            var isMaintenance = info.event.id.startsWith('maintenance_');
            
            // Tooltip de base
            info.el.title = info.event.title + '\n'
                + 'Équipe: ' + (props.nom_equipe || 'N/A') + '\n'
                + 'Statut: ' + (props.statut || 'Planifié');
            
            // Cas spécifique : Jour non ouvrable (sauté)
            if (props.is_non_working_day) {
                info.el.style.background = '#fee2e2';
                info.el.style.borderColor = '#f87171';
                info.el.style.borderStyle = 'dashed';
                info.el.style.color = '#991b1b';
                info.el.style.fontWeight = '700';
                info.el.title = 'Jour non ouvrable sauté dans le planning de la maintenance ' + (props.numero || '');
                var titleEl = info.el.querySelector('.fc-event-title, .fc-list-event-title');
                if (titleEl) {
                    titleEl.style.color = '#991b1b';
                    titleEl.style.fontWeight = '800';
                    titleEl.style.fontSize = '0.74rem';
                }
                return;
            }

            // Pour les maintenances: appliquer un dégradé de progression
            if (isMaintenance && props.nombre_equipements_prevus !== undefined && props.nombre_equipements_prevus > 0) {
                var progression = props.pourcentage_avancement || 0;
                
                // Couleurs de base pour les maintenances
                var couleurComplete = '#10b981'; // Vert (progression complétée)
                var couleurRestante = '#3b82f6'; // Bleu (couleur de base des maintenances)
                var bordureComplete = '#059669';
                var bordureRestante = '#2563eb';
                
                // Vérifier si c'est le début de l'événement (première ligne)
                // FullCalendar fournit info.isStart pour savoir si c'est la première ligne d'un événement multi-lignes
                var isStart = info.isStart !== undefined ? info.isStart : true;
                
                // Appliquer un dégradé linéaire selon la progression
                if (progression >= 100) {
                    // 100% terminé = tout vert
                    info.el.style.background = couleurComplete;
                    info.el.style.borderColor = bordureComplete;
                } else if (progression > 0 && isStart) {
                    // Dégradé SEULEMENT sur la première ligne
                    info.el.style.background = 'linear-gradient(to right, ' 
                        + couleurComplete + ' 0%, '
                        + couleurComplete + ' ' + progression + '%, '
                        + couleurRestante + ' ' + progression + '%, '
                        + couleurRestante + ' 100%)';
                    info.el.style.borderColor = bordureRestante;
                } else if (progression > 0 && !isStart) {
                    // Les lignes suivantes (continuation) restent en bleu uni
                    info.el.style.background = couleurRestante;
                    info.el.style.borderColor = bordureRestante;
                } else {
                    // 0% = tout bleu
                    info.el.style.background = couleurRestante;
                    info.el.style.borderColor = bordureRestante;
                }
                
                // Ajouter un badge avec le pourcentage SEULEMENT sur la première ligne
                var eventContent = info.el.querySelector('.fc-event-title, .fc-list-event-title');
                if (eventContent && isStart && !eventContent.querySelector('.progress-badge')) {
                    var badge = document.createElement('span');
                    badge.className = 'progress-badge';
                    badge.style.cssText = 'display: inline-block; background: rgba(255,255,255,0.25); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 6px; border: 1px solid rgba(255,255,255,0.4);';
                    badge.textContent = progression + '%';
                    eventContent.appendChild(badge);
                }
                
                // Ajouter au tooltip
                info.el.title += '\nProgression: ' + progression + '% (' + (props.nombre_equipements_traites || 0) + '/' + props.nombre_equipements_prevus + ' éq.)';
            }
        }
    });

    calendar.render();

    // Adapter la vue au resize (portrait ↔ paysage)
    window.addEventListener('resize', function () {
        var nowMobile = window.innerWidth <= 768;
        if (nowMobile !== isMobile) {
            isMobile = nowMobile;
            calendar.changeView('dayGridMonth'); // Toujours en vue mois
            calendar.setOption('height', isMobile ? 'auto' : 600);
            calendar.setOption('headerToolbar', isMobile
                ? { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' }
            );
        }
    });
});

// Gestion de la modale d'impression
function openPrintModal() {
    document.getElementById('exportModal').style.display = 'flex';
}

function closePrintModal() {
    document.getElementById('exportModal').style.display = 'none';
}

// Fermer la modale si on clique en dehors
document.getElementById('exportModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePrintModal();
    }
});

// Fermer avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePrintModal();
    }
});
</script>
@endsection
