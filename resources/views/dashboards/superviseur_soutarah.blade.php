@extends('layouts.app')

@section('title', 'Dashboard Superviseur Soutarah')

@section('content')
<style>
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.05); }
}
@keyframes glow {
    0%, 100% { box-shadow: 0 0 20px rgba(220, 38, 38, 0.5); }
    50% { box-shadow: 0 0 40px rgba(220, 38, 38, 0.8); }
}
</style>
<div class="header">
    <div>
        <h1>Tableau de Bord - Superviseur Soutarah</h1>
        <p style="color: #64748b; font-size: 0.9rem;">
            <i class="fa-solid fa-link"></i> Périmètre : 
            <strong style="color: #059669;">{{ $assignmentInfo['nom'] }}</strong>
            <span style="color: #94a3b8;">({{ $assignmentInfo['type'] === 'base' ? 'Base' : 'Company' }})</span>
        </p>
    </div>
</div>

<!-- Statistiques Principales -->
<div class="stats-grid">
    @if($assignmentInfo['type'] === 'base')
    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['bases'] }}</div>
            <div class="stat-label">Base Assignée</div>
        </div>
        <div class="stat-icon" style="background-color: #dbeafe; color: #1d4ed8;">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>
    </div>
    @endif

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['equipements'] }}</div>
            <div class="stat-label">Équipements</div>
        </div>
        <div class="stat-icon" style="background-color: #e0e7ff; color: #4f46e5;">
            <i class="fa-solid fa-microchip"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['demandes_validees_client'] }}</div>
            <div class="stat-label">Demandes à Valider</div>
        </div>
        <div class="stat-icon" style="background-color: #fef3c7; color: #b45309;">
            <i class="fa-solid fa-clipboard-check"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['demandes_operation_requise'] }}</div>
            <div class="stat-label">Opérations Requises</div>
        </div>
        <div class="stat-icon" style="background-color: #dbeafe; color: #0369a1;">
            <i class="fa-solid fa-wrench"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['interventions_en_cours'] }}</div>
            <div class="stat-label">Interventions En Cours</div>
        </div>
        <div class="stat-icon" style="background-color: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['interventions_effectuees'] }}</div>
            <div class="stat-label">Interventions Résolues</div>
        </div>
        <div class="stat-icon" style="background-color: #d1fae5; color: #047857;">
            <i class="fa-solid fa-check-circle"></i>
        </div>
    </div>

</div>

<!-- Section Demandes à Valider -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-clipboard-check"></i> Demandes Validées Client ({{ $demandesAValider->count() }})
        </h3>
        <a href="{{ route('demandes.index') }}" class="btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
            <i class="fa-solid fa-list"></i> Toutes les Demandes
        </a>
    </div>

    @if($demandesAValider->isNotEmpty())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>N° Demande</th>
                        <th>Créée le</th>
                        <th>Site</th>
                        <th>Équipement</th>
                        <th>Urgence</th>
                        <th>Validée Client</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demandesAValider as $d)
                    <tr>
                        <td><strong style="color: #059669;">#{{ $d->numero_demande }}</strong></td>
                        <td>
                            @if($d->created_at)
                                {{ is_string($d->created_at) ? $d->created_at : $d->created_at->format('d/m/Y H:i') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $d->site ? $d->site->nom_site : 'N/A' }}</td>
                        <td>{{ $d->equipement->equipement_nom }}</td>
                        <td>
                            @php
                            $urgenceStyles = [
                                'faible' => ['bg' => '#f1f5f9', 'color' => '#64748b'],
                                'moyen' => ['bg' => '#f0f9ff', 'color' => '#0369a1'],
                                'urgent' => ['bg' => '#fffbeb', 'color' => '#b45309'],
                                'critique' => ['bg' => '#fff1f2', 'color' => '#be123c']
                            ];
                            $style = $urgenceStyles[$d->niveau_urgence];
                            @endphp
                            <span class="badge" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                                {{ ucfirst($d->niveau_urgence) }}
                            </span>
                        </td>
                        <td>
                            @if($d->date_validation_client)
                                {{ is_string($d->date_validation_client) ? $d->date_validation_client : $d->date_validation_client->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('demandes.show', $d) }}" class="btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                <i class="fa-solid fa-eye"></i> Voir & Valider
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: #94a3b8;">
            <i class="fa-solid fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p>Aucune demande en attente de validation Soutarah</p>
        </div>
    @endif
</div>

<!-- Section Interventions Récentes -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-history"></i> Interventions Récentes ({{ $interventionsRecentes->count() }})
        </h3>
        <a href="{{ route('operations.index') }}" class="btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
            <i class="fa-solid fa-list"></i> Toutes les Interventions
        </a>
    </div>

    @if($interventionsRecentes->isNotEmpty())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Équipement</th>
                        <th>Technicien</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interventionsRecentes as $inter)
                    <tr>
                        <td><strong>#{{ $inter->id }}</strong></td>
                        <td>
                            @if($inter->date_demande)
                                {{ is_string($inter->date_demande) ? $inter->date_demande : $inter->date_demande->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $inter->equipement->equipement_nom ?? 'N/A' }}</td>
                        <td>{{ $inter->technicien->nom_complet ?? 'Non assigné' }}</td>
                        <td>
                            @php
                            $statutStyles = [
                                'en attente' => ['bg' => '#fffbeb', 'color' => '#b45309'],
                                'en cours' => ['bg' => '#f0f9ff', 'color' => '#0369a1'],
                                'résolu' => ['bg' => '#ecfdf5', 'color' => '#047857'],
                                'annulé' => ['bg' => '#f8fafc', 'color' => '#475569']
                            ];
                            $style = $statutStyles[$inter->statut] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                            @endphp
                            <span class="badge" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                                {{ $inter->statut }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('depannages.show', $inter) }}" style="background-color: #eff6ff; color: #1e40af; padding: 0.4rem 0.8rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.85rem; font-weight: 700;">
                                <i class="fa-solid fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 2rem; color: #94a3b8;">
            <i class="fa-solid fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p>Aucune intervention récente</p>
        </div>
    @endif
</div>

<!-- 📊 NOUVEAU : Graphique Répartition par Site -->
@if(!empty($equipementsParSite) && count($equipementsParSite) > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-map-pin"></i> Répartition des Équipements par Site</h3>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span style="font-size: 0.8rem; color: #64748b;"><i class="fa-solid fa-toolbox" style="color: #059669;"></i> {{ $stats['equipements'] }} équipements</span>
            @if($assignmentInfo['type'] === 'company')
            <span style="font-size: 0.8rem; color: #64748b;">•</span>
            <span style="font-size: 0.8rem; color: #64748b;">{{ count($equipementsParSite) }} sites</span>
            @endif
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
