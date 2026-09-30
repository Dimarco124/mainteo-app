@extends('layouts.app')

@section('title', 'Tableau de bord Superviseur')

@section('content')
<div class="header">
    <div class="page-title">
        <h1>Espace Superviseur 
            @if(isset($base)) 
                - {{ $base->nom_base }}
            @elseif(isset($client))
                - {{ $client->nom }}
            @endif
        </h1>
        <p>Gestion de votre périmètre : suivi des équipements et des interventions.</p>
    </div>
    <a href="{{ route('demandes.create') }}" style="background: linear-gradient(135deg, #be123c, #e11d48); color: #fff; padding: 0.75rem 1.25rem; border-radius: 0.75rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 10px rgba(190,18,60,.2);">
        <i class="fa-solid fa-plus"></i> Nouvelle Demande
    </a>
</div>

@if(isset($base))
<!-- Info Base -->
<div style="background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 1.25rem 1.5rem; border-radius: 1rem; margin-bottom: 1.75rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-size: 0.75rem; opacity: 0.9; margin-bottom: 0.25rem;">
                <i class="fa-solid fa-building"></i> {{ $base->client->nom ?? 'Client' }}
            </div>
            <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0;">
                <i class="fa-solid fa-map-location-dot"></i> {{ $base->nom_base }}
            </h2>
            <div style="font-size: 0.8rem; opacity: 0.9; margin-top: 0.25rem;">
                Code : {{ $base->code_base }}
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2rem; font-weight: 800;">{{ $stats['equipements'] }}</div>
            <div style="font-size: 0.75rem; opacity: 0.9;">Équipements</div>
        </div>
    </div>
</div>
@elseif(isset($client))
<!-- Info Client (entreprise directe sans base) -->
<div style="background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 1.25rem 1.5rem; border-radius: 1rem; margin-bottom: 1.75rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-size: 0.75rem; opacity: 0.9; margin-bottom: 0.25rem;">
                <i class="fa-solid fa-building"></i> Entreprise Directe
            </div>
            <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0;">
                <i class="fa-solid fa-briefcase"></i> {{ $client->nom }}
            </h2>
            <div style="font-size: 0.8rem; opacity: 0.9; margin-top: 0.25rem;">
                Gestion sans base intermédiaire
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2rem; font-weight: 800;">{{ $stats['equipements'] }}</div>
            <div style="font-size: 0.75rem; opacity: 0.9;">Équipements</div>
        </div>
    </div>
</div>
@endif

<!-- KPIs -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #fffbeb; color: #b45309;"><i class="fa-solid fa-clock"></i></div>
        <div>
            <div class="stat-val">{{ $stats['demandes_attente'] }}</div>
            <div class="stat-label">En attente</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #dbeafe; color: #1e40af;"><i class="fa-solid fa-spinner"></i></div>
        <div>
            <div class="stat-val">{{ $stats['interventions_en_cours'] }}</div>
            <div class="stat-label">En cours</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="stat-val">{{ $stats['interventions_resolues'] }}</div>
            <div class="stat-label">Résolues</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #f0fdf4; color: #047857;"><i class="fa-solid fa-toolbox"></i></div>
        <div>
            <div class="stat-val">{{ $stats['equipements'] }}</div>
            <div class="stat-label">Équipements</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #faf5ff; color: #7e22ce;"><i class="fa-solid fa-calendar-week"></i></div>
        <div>
            <div class="stat-val">{{ $stats['interventions_semaine'] }}</div>
            <div class="stat-label">Cette semaine</div>
        </div>
    </div>
</div>

<!-- Demandes en attente -->
@if($demandesAttente->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #b45309;"><i class="fa-solid fa-bell"></i> Demandes en Attente ({{ $demandesAttente->count() }})</h3>
        <a href="{{ route('demandes.index') }}" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
            Voir toutes <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th># Ticket</th>
                    <th>Réf. Équipement</th>
                    <th>Description Panne</th>
                    <th>Urgence</th>
                    <th>Date Demande</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandesAttente as $dep)
                <tr>
                    <td><code>#{{ $dep->id }}</code></td>
                    <td><strong style="color: #059669;">{{ $dep->equipement_reference ?? 'N/A' }}</strong></td>
                    <td>{{ Str::limit($dep->description_panne, 50) }}</td>
                    <td>
                        @if($dep->urgence === 'Urgent')
                            <span class="badge badge-danger"><i class="fa-solid fa-fire"></i> Urgent</span>
                        @elseif($dep->urgence === 'Faible')
                            <span class="badge badge-info">Faible</span>
                        @else
                            <span class="badge badge-warning">Normal</span>
                        @endif
                    </td>
                    <td><code>{{ $dep->date_demande }}</code></td>
                    <td style="text-align: right;">
                        <a href="{{ route('depannages.show', $dep->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Détails
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Interventions récentes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-history"></i> Dernières Interventions</h3>
        <a href="{{ route('demandes.index') }}" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
            Voir tout <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th># Ticket</th>
                    <th>Réf. Équipement</th>
                    <th>Description</th>
                    <th>Urgence</th>
                    <th>Date</th>
                    <th>Technicien</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interventionsRecentes as $dep)
                <tr>
                    <td><code>#{{ $dep->id }}</code></td>
                    <td><strong style="color: #059669;">{{ $dep->equipement_reference ?? 'N/A' }}</strong></td>
                    <td>{{ Str::limit($dep->description_panne, 40) }}</td>
                    <td>
                        @if($dep->urgence === 'Urgent')
                            <span class="badge badge-danger"><i class="fa-solid fa-fire"></i> Urgent</span>
                        @elseif($dep->urgence === 'Faible')
                            <span class="badge badge-info">Faible</span>
                        @else
                            <span class="badge badge-warning">Normal</span>
                        @endif
                    </td>
                    <td><code>{{ $dep->date_demande }}</code></td>
                    <td>{{ $dep->technicien->nom_complet ?? 'Non assigné' }}</td>
                    <td>
                        @if($dep->statut === 'résolu')
                            <span class="badge badge-success">Résolu</span>
                        @elseif($dep->statut === 'en cours')
                            <span class="badge badge-info">En cours</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('depannages.show', $dep->id) }}" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem 0.75rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune intervention récente.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 📊 NOUVEAU : Graphique Répartition par Site -->
@if(!empty($equipementsParSite) && count($equipementsParSite) > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-map-pin"></i> Répartition des Équipements par Site</h3>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span style="font-size: 0.8rem; color: #64748b;"><i class="fa-solid fa-toolbox" style="color: #059669;"></i> {{ $stats['equipements'] }} équipements</span>
        </div>
    </div>
    <div style="padding: 1.5rem; height: 400px;">
        <canvas id="equipementsParSiteChart"></canvas>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Chart.defaults.font.family = 'Varela Round';
    
    const ctxSites = document.getElementById('equipementsParSiteChart').getContext('2d');
    
    const equipementsParSiteData = @json($equipementsParSite ?? []);
    let siteLabels = equipementsParSiteData.map(item => item.nom);
    let siteEquipementsData = equipementsParSiteData.map(item => item.count);

    if (siteLabels.length === 0) {
        siteLabels = ['Aucun site'];
        siteEquipementsData = [0];
    }
    
    new Chart(ctxSites, {
        type: 'bar',
        data: {
            labels: siteLabels,
            datasets: [{
                label: 'Nombre d\'équipements',
                data: siteEquipementsData,
                backgroundColor: '#059669CC',
                borderColor: '#059669',
                borderWidth: 2,
                borderRadius: 10,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    padding: 15,
                    titleFont: { size: 14, weight: '700' },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${value} équipements (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { 
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: { 
                        color: '#64748b', 
                        font: { size: 12 },
                        padding: 10,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Nombre d\'équipements',
                        color: '#059669',
                        font: { size: 13, weight: '700' },
                        padding: 10
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#475569', 
                        font: { size: 12, weight: '600' },
                        padding: 10,
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
