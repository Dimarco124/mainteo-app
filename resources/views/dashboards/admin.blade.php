@extends('layouts.app')

@section('title', 'Tableau de Bord Administrateur')

@section('content')
<style>
    /* Grille principale : Évolution (2/3) + Performance (1/3) */
    .dashboard-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.75rem;
        margin-bottom: 1.75rem;
    }
    /* Grilles secondaires : 2 colonnes égales */
    .dashboard-secondary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr));
        gap: 1.75rem;
        margin-bottom: 1.75rem;
    }
    /* Règle critique CSS Grid : évite que les enfants dépassent la colonne */
    .dashboard-main-grid > .card,
    .dashboard-secondary-grid > .card {
        min-width: 0;
    }
    /* Les wrappers de canvas doivent avoir une largeur définie */
    .chart-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
    }
    @media (max-width: 768px) {
        .dashboard-main-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-secondary-grid {
            grid-template-columns: 1fr;
        }
    }
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
    <div class="page-title">
        <h1>Tableau de Bord Administrateur</h1>
        <p>Vue d'ensemble de la maintenance, des équipements et des interventions en temps réel.</p>
    </div>
    <div>
        <a href="{{ route('planning.index') }}" class="btn-primary my-4">
            <i class="fa-solid fa-calendar-plus"></i> Nouveau Planning
        </a>
    </div>
</div>

<!-- 6 Cartes d'indicateurs (KPIs) avec mini graphiques -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['clients'] }}</div>
            <div class="stat-label">Clients sous contrat</div>
        </div>
        <div style="width: 70px; height: 70px;">
            <canvas id="chartClients"></canvas>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['bases'] }}</div>
            <div class="stat-label">Bases & Sites Gérés</div>
        </div>
        <div style="width: 70px; height: 70px;">
            <canvas id="chartBases"></canvas>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['equipements'] }}</div>
            <div class="stat-label">Équipements au parc</div>
        </div>
        <div style="width: 70px; height: 70px;">
            <canvas id="chartEquipements"></canvas>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['techniciens'] }}</div>
            <div class="stat-label">Techniciens Terrain</div>
        </div>
        <div style="width: 70px; height: 70px;">
            <canvas id="chartTechniciens"></canvas>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['interventions_attente'] }}</div>
            <div class="stat-label">Pannes en Attente</div>
        </div>
        <div style="width: 70px; height: 70px;">
            <canvas id="chartAttente"></canvas>
        </div>
    </div>

    <div class="stat-card">
        <div style="flex: 1;">
            <div class="stat-val">{{ $stats['fiches_froid'] }}</div>
            <div class="stat-label">Fiches Froid (F-GAS)</div>
        </div>
        <div style="width: 70px; height: 70px;">
            <canvas id="chartFiches"></canvas>
        </div>
    </div>

</div>

<div class="dashboard-main-grid">
    <!-- Graphique d'évolution des interventions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-chart-line"></i> Évolution des Interventions (7 derniers jours)</h3>
        </div>
        <div class="chart-wrapper" style="height: 320px;">
            <canvas id="interventionsChart"></canvas>
        </div>
    </div>

    <!-- Classement Techniciens -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #047857;"><i class="fa-solid fa-trophy"></i> Performance Techniciens</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.85rem; overflow-x: auto;">
            @forelse($topTechniciens as $tech)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.8rem; background-color: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0; min-width: 250px;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0;">
                        {{ strtoupper(substr($tech->nom, 0, 1)) }}
                    </div>
                    <div style="min-width: 0;">
                        <div style="font-weight: 700; font-size: 0.9rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tech->nom_complet }}</div>
                        <div style="font-size: 0.75rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tech->specialite ?? 'Frigoriste CVC' }}</div>
                    </div>
                </div>
                <span class="badge badge-success" style="flex-shrink: 0;">{{ $tech->depannages_count }}</span>
            </div>
            @empty
            <p style="color: #94a3b8; font-size: 0.85rem;">Aucune statistique disponible.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Graphiques supplémentaires -->
<div class="dashboard-secondary-grid">
    <!-- Répartition par statut (Donut) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #0369a1;"><i class="fa-solid fa-chart-pie"></i> Répartition par Statut</h3>
        </div>
        <div class="chart-wrapper" style="height: 280px; display: flex; align-items: center; justify-content: center;">
            <canvas id="statutChart"></canvas>
        </div>
    </div>

    <!-- Urgences (Bar horizontal) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #b45309;"><i class="fa-solid fa-fire"></i> Niveau d'Urgence des Interventions</h3>
        </div>
        <div class="chart-wrapper" style="height: 280px;">
            <canvas id="urgenceChart"></canvas>
        </div>
    </div>
</div>

<!-- Graphique de répartition des équipements par entreprise (NOUVEAU) -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #7c3aed;"><i class="fa-solid fa-building-columns"></i> Répartition des Équipements par Entreprise</h3>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span style="font-size: 0.8rem; color: #64748b;"><i class="fa-solid fa-toolbox" style="color: #059669;"></i> {{ $stats['equipements'] }} équipements</span>
            <span style="font-size: 0.8rem; color: #64748b;">•</span>
            <span style="font-size: 0.8rem; color: #64748b;"><i class="fa-solid fa-building" style="color: #3b82f6;"></i> {{ $stats['clients'] }} clients</span>
        </div>
    </div>
    <div class="chart-wrapper" style="height: 350px;">
        <canvas id="equipementsParClientChart"></canvas>
    </div>
</div>

<!-- 📊 NOUVEAU : Graphique Répartition par Base -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-layer-group"></i> Répartition des Équipements par Base</h3>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span style="font-size: 0.8rem; color: #64748b;"><i class="fa-solid fa-database" style="color: #059669;"></i> {{ $stats['bases'] }} bases</span>
        </div>
    </div>
    <div class="chart-wrapper" style="height: 400px;">
        <canvas id="equipementsParBaseChart"></canvas>
    </div>
</div>

<!-- Filtre & Liste des Interventions Récents -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Dernières Demandes d'Intervention & Dépannage</h3>
        <a href="{{ route('demandes.index') }}" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 700;">Voir tout <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Référence Équipement</th>
                    <th>Pannes / Description</th>
                    <th>Niveau Urgence</th>
                    <th>Date Demande</th>
                    <th>Technicien Affecté</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interventionsRecentes as $dep)
                <tr>
                    <td><strong style="color: #059669;">{{ $dep->equipement_reference }}</strong></td>
                    <td>{{ Str::limit($dep->description_panne, 50) }}</td>
                    <td>
                        @if($dep->urgence === 'Urgent')
                            <span class="badge badge-danger"><i class="fa-solid fa-fire"></i> Urgent</span>
                        @elseif($dep->urgence === 'Normal')
                            <span class="badge badge-warning">Normal</span>
                        @else
                            <span class="badge badge-info">Faible</span>
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
                            <i class="fa-solid fa-wrench"></i> Traiter
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun dépannage récent.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Configuration de la police globale
        Chart.defaults.font.family = 'Varela Round';
        
        // ═══════════════════════════════════════════════════════════
        // 1. GRAPHIQUE PRINCIPAL : COURBE D'ÉVOLUTION (7 jours)
        // ═══════════════════════════════════════════════════════════
        const ctx = document.getElementById('interventionsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsJours) !!},
                datasets: [{
                    label: 'Total interventions créées',
                    data: {!! json_encode($interventionsParJour) !!},
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#059669',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }, {
                    label: 'Interventions en attente/en cours',
                    data: {!! json_encode($interventionsEnCoursParJour) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: { 
                            color: '#64748b',
                            padding: 15,
                            font: { size: 13, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: '#64748b', font: { size: 12 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 12 } }
                    }
                }
            }
        });

        // ═══════════════════════════════════════════════════════════
        // 2. GRAPHIQUE DONUT : RÉPARTITION PAR STATUT
        // ═══════════════════════════════════════════════════════════
        const ctxStatut = document.getElementById('statutChart').getContext('2d');
        new Chart(ctxStatut, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'En cours', 'Résolus'],
                datasets: [{
                    data: [
                        {{ $stats['interventions_attente'] }}, 
                        {{ $stats['interventions_en_cours'] }}, 
                        {{ $stats['interventions_effectuees'] }}
                    ],
                    backgroundColor: [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ],
                    borderColor: [
                        '#fbbf24',
                        '#3b82f6',
                        '#10b981'
                    ],
                    borderWidth: 3,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { 
                            color: '#64748b',
                            padding: 15,
                            font: { size: 13, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 }
                    }
                }
            }
        });

        // ═══════════════════════════════════════════════════════════
        // 3. GRAPHIQUE BAR HORIZONTAL : URGENCES
        // ═══════════════════════════════════════════════════════════
        const ctxUrgence = document.getElementById('urgenceChart').getContext('2d');
        new Chart(ctxUrgence, {
            type: 'bar',
            data: {
                labels: ['Critique', 'Urgent', 'Moyen', 'Faible'],
                datasets: [{
                    label: 'Nombre d\'interventions',
                    data: [
                        {{ $urgences['critique'] }},
                        {{ $urgences['urgent'] }},
                        {{ $urgences['moyen'] }},
                        {{ $urgences['faible'] }}
                    ],
                    backgroundColor: [
                        'rgba(190, 18, 60, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        '#be123c',
                        '#ef4444',
                        '#fbbf24',
                        '#3b82f6'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 40
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: '#64748b', font: { size: 12 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 13, weight: '600' } }
                    }
                }
            }
        });

        // ═══════════════════════════════════════════════════════════
        // 4. GRAPHIQUE RÉPARTITION ÉQUIPEMENTS PAR ENTREPRISE
        // ═══════════════════════════════════════════════════════════
        const ctxEquipements = document.getElementById('equipementsParClientChart').getContext('2d');
        
        // Données réelles anonymisées transmises par le contrôleur
        const equipementsParClientData = @json($equipementsParClient ?? []);
        let clientLabels = equipementsParClientData.map(item => item.nom);
        let equipementsData = equipementsParClientData.map(item => item.count);

        if (clientLabels.length === 0) {
            clientLabels = ['Aucun équipement'];
            equipementsData = [0];
        }
        
        // Couleurs variées pour chaque entreprise
        const colors = [
            '#059669', '#10b981', '#34d399', '#6ee7b7', 
            '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe',
            '#f59e0b', '#fbbf24', '#fcd34d', '#fde68a',
            '#ef4444', '#f87171', '#fca5a5', '#fecaca',
            '#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe'
        ];
        
        new Chart(ctxEquipements, {
            type: 'bar',
            data: {
                labels: clientLabels,
                datasets: [{
                    label: 'Nombre d\'équipements',
                    data: equipementsData,
                    backgroundColor: colors.slice(0, clientLabels.length).map(c => c + 'CC'),
                    borderColor: colors.slice(0, clientLabels.length),
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
                            color: '#64748b',
                            font: { size: 13, weight: '600' },
                            padding: 10
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: '#64748b', 
                            font: { size: 12, weight: '600' },
                            padding: 10,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });

        // ═══════════════════════════════════════════════════════════
        // 5. 📊 NOUVEAU : GRAPHIQUE RÉPARTITION ÉQUIPEMENTS PAR BASE
        // ═══════════════════════════════════════════════════════════
        const ctxEquipementsBase = document.getElementById('equipementsParBaseChart').getContext('2d');
        
        const equipementsParBaseData = @json($equipementsParBase ?? []);
        let baseLabels = equipementsParBaseData.map(item => item.nom);
        let baseEquipementsData = equipementsParBaseData.map(item => item.count);
        let baseClientsData = equipementsParBaseData.map(item => item.client);

        if (baseLabels.length === 0) {
            baseLabels = ['Aucune base'];
            baseEquipementsData = [0];
            baseClientsData = [''];
        }
        
        new Chart(ctxEquipementsBase, {
            type: 'bar',
            data: {
                labels: baseLabels,
                datasets: [{
                    label: 'Nombre d\'équipements',
                    data: baseEquipementsData,
                    backgroundColor: '#059669CC',
                    borderColor: '#059669',
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.75,
                    categoryPercentage: 0.85
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
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return `${context.parsed.y} équipements`;
                            },
                            afterLabel: function(context) {
                                return `Client: ${baseClientsData[context.dataIndex]}`;
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

        // ═══════════════════════════════════════════════════════════
        // MINI GRAPHIQUES POUR LES KPI CARDS
        // ═══════════════════════════════════════════════════════════
        const miniChartOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } }
        };

        // Clients - Sparkline
        new Chart(document.getElementById('chartClients'), {
            type: 'line',
            data: {
                labels: ['', '', '', '', '', '', ''],
                datasets: [{
                    data: [{{ $stats['clients'] - 3 }}, {{ $stats['clients'] - 2 }}, {{ $stats['clients'] - 1 }}, {{ $stats['clients'] }}, {{ $stats['clients'] + 1 }}, {{ $stats['clients'] }}, {{ $stats['clients'] }}],
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0
                }]
            },
            options: miniChartOptions
        });

        // Bases - Bar mini
        new Chart(document.getElementById('chartBases'), {
            type: 'bar',
            data: {
                labels: ['', '', '', '', ''],
                datasets: [{
                    data: [{{ $stats['bases'] - 2 }}, {{ $stats['bases'] - 1 }}, {{ $stats['bases'] }}, {{ $stats['bases'] + 1 }}, {{ $stats['bases'] }}],
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderRadius: 4
                }]
            },
            options: miniChartOptions
        });

        // Équipements - Radar mini
        new Chart(document.getElementById('chartEquipements'), {
            type: 'polarArea',
            data: {
                datasets: [{
                    data: [{{ $stats['equipements'] * 0.3 }}, {{ $stats['equipements'] * 0.5 }}, {{ $stats['equipements'] * 0.2 }}],
                    backgroundColor: [
                        'rgba(4, 120, 87, 0.6)',
                        'rgba(5, 150, 105, 0.6)',
                        'rgba(16, 185, 129, 0.6)'
                    ],
                    borderWidth: 0
                }]
            },
            options: miniChartOptions
        });

        // Techniciens - Doughnut
        new Chart(document.getElementById('chartTechniciens'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $stats['techniciens'] }}, 10 - {{ $stats['techniciens'] }}],
                    backgroundColor: ['#b45309', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: miniChartOptions
        });

        // Attente - Sparkline urgent
        new Chart(document.getElementById('chartAttente'), {
            type: 'line',
            data: {
                labels: ['', '', '', '', '', '', ''],
                datasets: [{
                    data: [3, 5, 2, 7, 4, {{ $stats['interventions_attente'] + 2 }}, {{ $stats['interventions_attente'] }}],
                    borderColor: '#be123c',
                    backgroundColor: 'rgba(190, 18, 60, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0
                }]
            },
            options: miniChartOptions
        });

        // Fiches Froid - Bar mini
        new Chart(document.getElementById('chartFiches'), {
            type: 'bar',
            data: {
                labels: ['', '', '', '', ''],
                datasets: [{
                    data: [{{ $stats['fiches_froid'] - 15 }}, {{ $stats['fiches_froid'] - 10 }}, {{ $stats['fiches_froid'] - 5 }}, {{ $stats['fiches_froid'] }}, {{ $stats['fiches_froid'] }}],
                    backgroundColor: 'rgba(126, 34, 206, 0.7)',
                    borderRadius: 4
                }]
            },
            options: miniChartOptions
        });
    });
</script>
@endsection
