@extends('mobile.technicien.layout')

@section('title', 'Planning - MAINTEO Mobile')
@section('page-title', 'Mon Planning')

@section('mobile-content')
<div style="padding: 0.5rem; padding-bottom: 100px;">
    
    {{-- Légende des Couleurs --}}
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; background: #ffffff; padding: 0.75rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; overflow-x: auto;">
        <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; font-weight: 600; color: #991b1b; background: #fef2f2; padding: 0.2rem 0.5rem; border-radius: 0.4rem; border: 1px solid #fecaca; white-space: nowrap;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #dc2626; display: inline-block;"></span> Dépannage
        </div>
        <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; font-weight: 600; color: #b45309; background: #fffbeb; padding: 0.2rem 0.5rem; border-radius: 0.4rem; border: 1px solid #fde68a; white-space: nowrap;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b; display: inline-block;"></span> Installation
        </div>
        <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; font-weight: 600; color: #1d4ed8; background: #eff6ff; padding: 0.2rem 0.5rem; border-radius: 0.4rem; border: 1px solid #bfdbfe; white-space: nowrap;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6; display: inline-block;"></span> Maintenance
        </div>
        <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; font-weight: 600; color: #047857; background: #ecfdf5; padding: 0.2rem 0.5rem; border-radius: 0.4rem; border: 1px solid #a7f3d0; white-space: nowrap;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; display: inline-block;"></span> Validé Client
        </div>
    </div>
    
    {{-- Calendrier FullCalendar --}}
    <div class="card" style="margin-bottom: 1rem; padding: 0.5rem;">
        <div id="calendar"></div>
    </div>
    
    {{-- Tableau des Maintenances --}}
    <div class="card" style="margin-bottom: 1rem;">
        <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 0.75rem; border-radius: 0.75rem 0.75rem 0 0;">
            <h3 style="margin: 0; color: white; font-size: 0.9rem; font-weight: 700;">
                <i class="fa-solid fa-wrench"></i> Mes Maintenances
            </h3>
        </div>
        
        @forelse($maintenances as $m)
        <a href="{{ route('mobile.technicien.maintenances.details', $m->id) }}" style="display: block; padding: 0.75rem; border-bottom: 1px solid #e2e8f0; text-decoration: none; color: inherit;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.4rem;">
                <div style="flex: 1;">
                    <div style="font-weight: 700; color: #059669; font-size: 0.85rem; margin-bottom: 0.2rem;">
                        {{ $m->numero_maintenance }}
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        {{ $m->equipement->equipement_nom ?? 'N/A' }}
                    </div>
                </div>
                <div style="text-align: right;">
                    @if($m->statut === 'planifiée')
                        <span style="background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 600;">⏳ Planifiée</span>
                    @elseif($m->statut === 'confirmée_client')
                        <span style="background: #dbeafe; color: #1e40af; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 600;">✔️ Confirmée</span>
                    @elseif($m->statut === 'en_cours')
                        <span style="background: #eff6ff; color: #1d4ed8; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 600;">🔄 En Cours</span>
                    @elseif($m->statut === 'terminée')
                        <span style="background: #ecfdf5; color: #047857; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 600;">✅ Terminée</span>
                    @endif
                </div>
            </div>
            
            <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.7rem; color: #64748b; margin-bottom: 0.4rem;">
                <span><i class="fa-solid fa-calendar"></i> {{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y') }}</span>
                <span><i class="fa-solid fa-location-dot"></i> {{ $m->site->nom_site ?? ($m->base->nom_base ?? 'N/A') }}</span>
            </div>
            
            {{-- Boutons d'action seulement pour chef de cette maintenance --}}
            @php
                $isChefDeCette = Auth::user()->isChefTechnicien() && (
                    ($m->equipe && $m->equipe->chef_equipe == Auth::id()) ||
                    ($m->equipes && $m->equipes->contains(fn($e) => $e->chef_equipe == Auth::id()))
                );
                $isTermineDeCette = ($m->nombre_equipements_prevus > 0 && $m->nombre_equipements_restants == 0);
            @endphp
            @if($isChefDeCette)
                @if($m->statut === 'confirmée_client')
                    @php
                        $dateDebutPrevue = \Carbon\Carbon::parse($m->date_debut_prevue);
                        $maintenant = \Carbon\Carbon::now();
                        $peutDemarrer = $maintenant->gte($dateDebutPrevue);
                    @endphp
                    @if($peutDemarrer)
                    <form action="{{ route('mobile.technicien.maintenances.demarrer', $m->id) }}" method="POST" onclick="event.stopPropagation();" style="margin-top: 0.5rem;">
                        @csrf
                        <button type="submit" style="width: 100%; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                            <i class="fa-solid fa-play"></i> Démarrer la Maintenance
                        </button>
                    </form>
                    @endif
                @elseif($m->statut === 'en_cours')
                <div onclick="event.stopPropagation();" style="margin-top: 0.5rem;">
                    @if($isTermineDeCette)
                    <a href="{{ route('mobile.technicien.maintenances.rapport', $m->id) }}" style="display: block; text-align: center; width: 100%; background: #f0fdf4; color: #047857; border: 1px solid #a7f3d0; padding: 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; text-decoration: none;">
                        <i class="fa-solid fa-flag-checkered"></i> Terminer la Maintenance
                    </a>
                    @else
                    <span title="{{ $m->nombre_equipements_restants }} équipement(s) restant(s) à traiter" style="display: block; text-align: center; width: 100%; background: #f1f5f9; color: #94a3b8; border: 1px solid #cbd5e1; padding: 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; cursor: not-allowed;">
                        <i class="fa-solid fa-lock"></i> Terminer ({{ $m->nombre_equipements_restants }} rest.)
                    </span>
                    @endif
                </div>
                @endif
            @endif
        </a>
        @empty
        <div style="padding: 2rem; text-align: center; color: #94a3b8;">
            <i class="fa-solid fa-wrench" style="font-size: 2rem; opacity: 0.3; margin-bottom: 0.5rem; display: block;"></i>
            <p style="font-size: 0.8rem;">Aucune maintenance planifiée</p>
        </div>
        @endforelse
    </div>
    
    {{-- Tableau des Interventions (Dépannages + Installations) --}}
    <div class="card" style="margin-bottom: 1rem;">
        <div style="background: linear-gradient(135deg, #dc2626, #b91c1c); padding: 0.75rem; border-radius: 0.75rem 0.75rem 0 0;">
            <h3 style="margin: 0; color: white; font-size: 0.9rem; font-weight: 700;">
                <i class="fa-solid fa-tools"></i> Mes Interventions
            </h3>
        </div>
        
        @forelse($interventions as $int)
        <a href="{{ route('mobile.technicien.interventions.details', $int->id) }}" style="display: block; padding: 0.75rem; border-bottom: 1px solid #e2e8f0; text-decoration: none; color: inherit;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.4rem;">
                <div style="flex: 1;">
                    <div style="font-weight: 700; color: #dc2626; font-size: 0.85rem; margin-bottom: 0.2rem;">
                        {{ $int->demande->numero_demande ?? 'N/A' }}
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        {{ $int->equipement->equipement_nom ?? 'N/A' }}
                    </div>
                </div>
                <div style="text-align: right;">
                    @if($int->type_intervention === 'Installation')
                        <span style="background: #fffbeb; color: #b45309; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 600;">🔧 Installation</span>
                    @else
                        <span style="background: #fef2f2; color: #991b1b; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.7rem; font-weight: 600;">⚠️ Dépannage</span>
                    @endif
                </div>
            </div>
            
            <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.7rem; color: #64748b;">
                <span><i class="fa-solid fa-calendar"></i> {{ \Carbon\Carbon::parse($int->date_demande)->format('d/m/Y') }}</span>
                <span><i class="fa-solid fa-location-dot"></i> {{ $int->equipement->site->nom_site ?? 'N/A' }}</span>
            </div>
        </a>
        @empty
        <div style="padding: 2rem; text-align: center; color: #94a3b8;">
            <i class="fa-solid fa-tools" style="font-size: 2rem; opacity: 0.3; margin-bottom: 0.5rem; display: block;"></i>
            <p style="font-size: 0.8rem;">Aucune intervention planifiée</p>
        </div>
        @endforelse
    </div>
</div>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/fr.global.min.js"></script>

<style>
#calendar {
    width: 100%;
    min-height: 350px;
}

/* Mobile optimizations */
.fc .fc-toolbar {
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.fc .fc-toolbar-chunk {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.2rem;
}

.fc .fc-toolbar-title {
    font-size: 0.95rem;
    font-weight: 700;
    text-align: center;
    color: #0f172a;
}

.fc .fc-button {
    padding: 0.3rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 0.375rem;
}

.fc-col-header-cell-cushion {
    font-size: 0.7rem;
    padding: 3px;
}

.fc .fc-daygrid-day-number {
    font-size: 0.75rem;
    padding: 2px 4px;
}

.fc .fc-daygrid-day-frame {
    min-height: 50px;
}

.fc .fc-event {
    font-size: 0.7rem;
    padding: 2px 4px;
    border-radius: 3px;
    line-height: 1.2;
    cursor: pointer;
}

.fc-list-event-title, .fc-list-event-time {
    font-size: 0.8rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Vue par défaut = MOIS
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        buttonText: {
            today: 'Auj.',
            month: 'Mois',
            week: 'Semaine',
            list: 'Liste'
        },
        themeSystem: 'standard',
        events: '{{ route('planning.events') }}',
        height: 'auto',
        eventClick: function(info) {
            var props = info.event.extendedProps;
            var isMaintenance = info.event.id.startsWith('maintenance_');
            
            // Rediriger vers la bonne page de détails PWA
            if (isMaintenance) {
                var maintenanceId = info.event.id.replace('maintenance_', '');
                window.location.href = '{{ url('/mobile/technicien/maintenances') }}/' + maintenanceId;
            } else {
                var interventionId = info.event.id.replace('planif_', '');
                // Extraire l'ID de l'intervention depuis les props si disponible
                if (props.depannage_id) {
                    window.location.href = '{{ url('/mobile/technicien/interventions') }}/' + props.depannage_id;
                }
            }
        },
        eventDidMount: function(info) {
            var props = info.event.extendedProps;
            var isMaintenance = info.event.id.startsWith('maintenance_');
            
            // Tooltip
            info.el.title = info.event.title + '\n'
                + 'Équipe: ' + (props.nom_equipe || 'N/A') + '\n'
                + 'Statut: ' + (props.statut || 'Planifié');
            
            // Dégradé de progression pour maintenances
            if (isMaintenance && props.nombre_equipements_prevus !== undefined && props.nombre_equipements_prevus > 0) {
                var progression = props.pourcentage_avancement || 0;
                
                var couleurComplete = '#10b981';
                var couleurRestante = '#3b82f6';
                var bordureComplete = '#059669';
                var bordureRestante = '#2563eb';
                
                // Vérifier si c'est le début de l'événement (première ligne)
                var isStart = info.isStart !== undefined ? info.isStart : true;
                
                if (progression >= 100) {
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
                    // Les lignes suivantes restent en bleu uni
                    info.el.style.background = couleurRestante;
                    info.el.style.borderColor = bordureRestante;
                } else {
                    info.el.style.background = couleurRestante;
                    info.el.style.borderColor = bordureRestante;
                }
                
                // Badge SEULEMENT sur la première ligne
                var eventContent = info.el.querySelector('.fc-event-main, .fc-list-event-title');
                if (eventContent && isStart && !eventContent.querySelector('.progress-badge')) {
                    var badge = document.createElement('span');
                    badge.className = 'progress-badge';
                    badge.style.cssText = 'display: inline-block; background: rgba(255,255,255,0.25); color: white; padding: 1px 4px; border-radius: 3px; font-size: 0.65rem; font-weight: 700; margin-left: 4px; border: 1px solid rgba(255,255,255,0.4);';
                    badge.textContent = progression + '%';
                    
                    var titleElement = eventContent.querySelector('.fc-event-title, .fc-list-event-title');
                    if (titleElement && !titleElement.querySelector('.progress-badge')) {
                        titleElement.appendChild(badge);
                    }
                }
                
                info.el.title += '\nProgression: ' + progression + '% (' + (props.nombre_equipements_traites || 0) + '/' + props.nombre_equipements_prevus + ' éq.)';
            }
        }
    });

    calendar.render();
});
</script>
@endsection
