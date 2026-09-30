@extends('layouts.app')

@section('title', 'Statistiques & Rapports')

@section('content')
<style>
    .filter-bar {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.75rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }

    .filter-bar label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .filter-bar select {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        border-radius: 0.6rem;
        font-size: 0.8rem;
        padding: 0.65rem 1rem;
        min-width: 140px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .filter-bar select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        outline: none;
    }

    .stats-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .kpi-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .kpi-content {
        flex: 1;
    }

    .kpi-val {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.2rem;
    }

    .kpi-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
    }

    .equipements-table-wrapper {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 0;
        margin-bottom: 1.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }

    .equipements-table-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .equipements-table-header h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #059669;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .equipements-table-body {
        overflow-x: auto;
    }

    .equipements-table {
        width: 100%;
        border-collapse: collapse;
    }

    .equipements-table thead th {
        background-color: #f8fafc;
        padding: 0.85rem 1rem;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }

    .equipements-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.8rem;
        color: #0f172a;
    }

    .equipements-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .equipements-table tbody tr:last-child td {
        border-bottom: none;
    }

    .indicator-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .indicator-critique {
        background-color: #fff1f2;
        color: #be123c;
        border: 1px solid #fecdd3;
    }

    .indicator-attention {
        background-color: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
    }

    .indicator-surveiller {
        background-color: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .indicator-normal {
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-container {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .chart-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-canvas-wrapper {
        height: 300px;
        position: relative;
    }

    @media (max-width: 768px) {
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-bar > div {
            width: 100%;
        }

        .filter-bar select {
            width: 100%;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .equipements-table thead th {
            font-size: 0.65rem;
            padding: 0.65rem 0.5rem;
        }

        .equipements-table tbody td {
            font-size: 0.75rem;
            padding: 0.75rem 0.5rem;
        }
    }
</style>

<div class="header">
    <div class="page-title">
        @if(in_array($role, ['technicien', 'chef technicien']))
            <h1>Mes Statistiques Personnelles</h1>
            <p>Suivez votre performance et votre activité</p>
        @elseif($role === 'superviseur_client')
            <h1>Statistiques de Vos Équipements</h1>
            <p>Vue synthétique pour prendre des décisions éclairées</p>
        @else
            <h1>Statistiques & Rapports de Maintenance</h1>
            <p>Vue d'ensemble complète de l'activité de maintenance</p>
        @endif
    </div>
</div>

<!-- Filtre global Mois/Année -->
<form method="GET" action="{{ route('rapports.index') }}" id="filterForm" class="filter-bar">
    <div>
        <label for="mois">Mois</label>
        <select name="mois" id="mois" onchange="document.getElementById('filterForm').submit()">
            <option value="1" {{ $mois == 1 ? 'selected' : '' }}>Janvier</option>
            <option value="2" {{ $mois == 2 ? 'selected' : '' }}>Février</option>
            <option value="3" {{ $mois == 3 ? 'selected' : '' }}>Mars</option>
            <option value="4" {{ $mois == 4 ? 'selected' : '' }}>Avril</option>
            <option value="5" {{ $mois == 5 ? 'selected' : '' }}>Mai</option>
            <option value="6" {{ $mois == 6 ? 'selected' : '' }}>Juin</option>
            <option value="7" {{ $mois == 7 ? 'selected' : '' }}>Juillet</option>
            <option value="8" {{ $mois == 8 ? 'selected' : '' }}>Août</option>
            <option value="9" {{ $mois == 9 ? 'selected' : '' }}>Septembre</option>
            <option value="10" {{ $mois == 10 ? 'selected' : '' }}>Octobre</option>
            <option value="11" {{ $mois == 11 ? 'selected' : '' }}>Novembre</option>
            <option value="12" {{ $mois == 12 ? 'selected' : '' }}>Décembre</option>
        </select>
    </div>
    <div>
        <label for="annee">Année</label>
        <select name="annee" id="annee" onchange="document.getElementById('filterForm').submit()">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div style="display: flex; align-items: flex-end;">
        <button type="submit" class="btn-primary" style="padding: 0.65rem 1.25rem; white-space: nowrap;">
            <i class="fa-solid fa-filter"></i> Filtrer
        </button>
    </div>
</form>

@if(in_array($role, ['technicien', 'chef technicien']))
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- VUE TECHNICIEN : Stats personnelles uniquement --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    
    <!-- Cartes KPIs Technicien Principales -->
    <div class="stats-kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff;">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val">{{ $statsTechnicien['interventions_mois'] }}</div>
                <div class="kpi-label">Mes Activités ce Mois</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">Dépannages, Inst. & Maintenances</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #34d399); color: #ffffff;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val">{{ $statsTechnicien['interventions_resolues'] }}</div>
                <div class="kpi-label">Travaux Clôturés</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">Résolus ou terminés</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #3b82f6, #60a5fa); color: #ffffff;">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val">{{ $statsTechnicien['taux_resolution'] }}%</div>
                <div class="kpi-label">Taux d'Achèvement</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">{{ $statsTechnicien['interventions_resolues'] }}/{{ $statsTechnicien['interventions_mois'] }} activités</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #ffffff;">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val">
                    @if($statsTechnicien['mon_rang'])
                        {{ $statsTechnicien['mon_rang'] }} <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 500;">/ {{ $statsTechnicien['total_techniciens'] }}</span>
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </div>
                <div class="kpi-label">Mon Classement</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">
                    @if($statsTechnicien['mon_total'] > 0)
                        {{ $statsTechnicien['mon_total'] }} activités tous temps
                    @else
                        En attente d'activité
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Détail par type d'activité (Cohérence complète avec le tableau de bord) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
        <!-- Dépannages -->
        <div class="kpi-card" style="padding: 1.25rem;">
            <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <div class="kpi-content">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-weight: 700; color: #0f172a; font-size: 1rem;">Dépannages</span>
                    <span class="kpi-val" style="font-size: 1.35rem; margin: 0;">{{ $statsTechnicien['depannages_mois'] }}</span>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem; display: flex; gap: 0.75rem;">
                    <span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-check"></i> {{ $statsTechnicien['depannages_resolus'] }} résolus</span>
                    <span style="color: #f59e0b; font-weight: 600;"><i class="fa-solid fa-clock"></i> {{ $statsTechnicien['depannages_en_cours'] }} en cours</span>
                </div>
            </div>
        </div>

        <!-- Installations -->
        <div class="kpi-card" style="padding: 1.25rem;">
            <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div class="kpi-content">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-weight: 700; color: #0f172a; font-size: 1rem;">Installations</span>
                    <span class="kpi-val" style="font-size: 1.35rem; margin: 0;">{{ $statsTechnicien['installations_mois'] }}</span>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem; display: flex; gap: 0.75rem;">
                    <span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-check"></i> {{ $statsTechnicien['installations_resolues'] }} terminées</span>
                    <span style="color: #f59e0b; font-weight: 600;"><i class="fa-solid fa-clock"></i> {{ $statsTechnicien['installations_en_cours'] }} en cours</span>
                </div>
            </div>
        </div>

        <!-- Maintenances -->
        <div class="kpi-card" style="padding: 1.25rem;">
            <div class="kpi-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="kpi-content">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-weight: 700; color: #0f172a; font-size: 1rem;">Maintenances</span>
                    <span class="kpi-val" style="font-size: 1.35rem; margin: 0;">{{ $statsTechnicien['maintenances_mois'] }}</span>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem; display: flex; gap: 0.75rem;">
                    <span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-check"></i> {{ $statsTechnicien['maintenances_resolues'] }} terminées</span>
                    <span style="color: #f59e0b; font-weight: 600;"><i class="fa-solid fa-clock"></i> {{ $statsTechnicien['maintenances_en_cours'] }} actives</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateurs Terrain & Métier Complémentaires -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
        <!-- Équipements traités -->
        <div class="kpi-card" style="padding: 1.15rem;">
            <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-gears"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val" style="font-size: 1.4rem; margin-bottom: 0.1rem;">{{ $statsTechnicien['equipements_traites'] }}</div>
                <div class="kpi-label">Équipements Traités</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">Réparés ou maintenus</div>
            </div>
        </div>

        <!-- Fiches Froid (F-Gas) -->
        <div class="kpi-card" style="padding: 1.15rem;">
            <div class="kpi-icon" style="background: rgba(6, 182, 212, 0.1); color: #0891b2; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-snowflake"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val" style="font-size: 1.4rem; margin-bottom: 0.1rem;">{{ $statsTechnicien['fiches_froid_mois'] }}</div>
                <div class="kpi-label">Fiches Entretien Froid</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">Saisies ce mois</div>
            </div>
        </div>

        <!-- Sites couverts -->
        <div class="kpi-card" style="padding: 1.15rem;">
            <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val" style="font-size: 1.4rem; margin-bottom: 0.1rem;">{{ $statsTechnicien['sites_visites'] }}</div>
                <div class="kpi-label">Sites Couverts</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">Sites visités ce mois</div>
            </div>
        </div>

        <!-- Interventions Urgentes -->
        <div class="kpi-card" style="padding: 1.15rem;">
            <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-val" style="font-size: 1.4rem; margin-bottom: 0.1rem;">{{ $statsTechnicien['urgences_mois'] }}</div>
                <div class="kpi-label">Missions Urgentes</div>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">Priorité urgente ou haute</div>
            </div>
        </div>
    </div>

    <!-- Graphique évolution personnelle -->
    <div class="chart-container">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fa-solid fa-chart-line" style="color: #059669;"></i>
                Mon Évolution (12 derniers mois)
            </h3>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Total cumulé : {{ $statsTechnicien['mon_total'] }} interventions</span>
        </div>
        <div class="chart-canvas-wrapper">
            <canvas id="monEvolutionChart"></canvas>
        </div>
    </div>

    <!-- Message motivation -->
    @if($statsTechnicien['mon_total'] == 0)
        <div class="card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; margin-top: 1.5rem;">
            <div style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                <h3 style="color: #475569; font-size: 1.1rem; margin-bottom: 0.35rem; font-weight: 700;">
                    Prêt pour vos prochaines missions !
                </h3>
                <p style="color: #64748b; font-size: 0.85rem; max-width: 600px; margin: 0 auto;">
                    Aucune intervention n'a encore été enregistrée pour vous ou votre équipe sur cette période. Dès que des travaux vous seront attribués et traités, votre progression s'affichera ici en temps réel.
                </p>
            </div>
        </div>
    @elseif($statsTechnicien['mon_rang'] && $statsTechnicien['mon_rang'] <= 3)
        <div class="card" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 2px solid #10b981; border-radius: 1rem; margin-top: 1.5rem;">
            <div style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏆</div>
                <h3 style="color: #059669; font-size: 1.2rem; margin-bottom: 0.35rem; font-weight: 800;">
                    Excellent travail ! Vous êtes dans le Top 3 !
                </h3>
                <p style="color: #047857; font-size: 0.85rem; max-width: 600px; margin: 0 auto;">
                    Continuez comme ça, votre performance est remarquable avec <strong>{{ $statsTechnicien['mon_total'] }}</strong> activités réalisées tous temps !
                </p>
            </div>
        </div>
    @elseif($statsTechnicien['taux_resolution'] >= 80)
        <div class="card" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 2px solid #3b82f6; border-radius: 1rem; margin-top: 1.5rem;">
            <div style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">⭐</div>
                <h3 style="color: #1d4ed8; font-size: 1.2rem; margin-bottom: 0.35rem; font-weight: 800;">
                    Très bon taux d'achèvement !
                </h3>
                <p style="color: #1e40af; font-size: 0.85rem; max-width: 600px; margin: 0 auto;">
                    <strong>{{ $statsTechnicien['taux_resolution'] }}%</strong> d'activités clôturées ce mois-ci, c'est un excellent résultat !
                </p>
            </div>
        </div>
    @else
        <div class="card" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 2px solid #10b981; border-radius: 1rem; margin-top: 1.5rem;">
            <div style="text-align: center; padding: 1.25rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">💪</div>
                <h3 style="color: #059669; font-size: 1.2rem; margin-bottom: 0.35rem; font-weight: 800;">
                    Continuez vos efforts !
                </h3>
                <p style="color: #047857; font-size: 0.85rem; max-width: 600px; margin: 0 auto;">
                    Vous avez clôturé <strong>{{ $statsTechnicien['interventions_resolues'] }}</strong> travaux sur <strong>{{ $statsTechnicien['interventions_mois'] }}</strong> activités enregistrées ce mois.
                </p>
            </div>
        </div>
    @endif

    <!-- Dernières Interventions Clôturées -->
    @if(isset($statsTechnicien['dernieres_cloturees']) && $statsTechnicien['dernieres_cloturees']->count() > 0)
    <div class="equipements-table-wrapper" style="margin-top: 1.75rem;">
        <div class="equipements-table-header">
            <h3>
                <i class="fa-solid fa-clock-rotate-left"></i>
                Mes Dernières Interventions Clôturées
            </h3>
        </div>
        <div class="equipements-table-body">
            <table class="equipements-table">
                <thead>
                    <tr>
                        <th>N° / Réf</th>
                        <th>Type</th>
                        <th>Équipement</th>
                        <th>Site</th>
                        <th>Date Demande</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statsTechnicien['dernieres_cloturees'] as $dep)
                    <tr>
                        <td><strong>#{{ $dep->id }}</strong></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: {{ $dep->type_intervention === 'Installation' ? '#f3e8ff; color: #7e22ce;' : '#e0f2fe; color: #0369a1;' }}">
                                {{ $dep->type_intervention ?? 'Dépannage' }}
                            </span>
                        </td>
                        <td>{{ $dep->equipement ? $dep->equipement->nom_equipement : 'N/A' }}</td>
                        <td>{{ $dep->equipement && $dep->equipement->site ? $dep->equipement->site->nom_site : 'N/A' }}</td>
                        <td>{{ $dep->date_demande ? \Carbon\Carbon::parse($dep->date_demande)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #ecfdf5; color: #047857;">
                                <i class="fa-solid fa-check"></i> Résolu
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('depannages.show', $dep->id) }}" class="btn-primary" style="padding: 0.3rem 0.75rem; font-size: 0.75rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@else
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- VUE SUPERVISEUR / ADMIN : Stats complètes --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

<!-- Cartes KPIs -->
<div class="stats-kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff;">
            <i class="fa-solid fa-wrench"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-val">{{ $totalInterventionsMois }}</div>
            <div class="kpi-label">Total Interventions</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #dc2626, #ef4444); color: #ffffff;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-val">{{ $equipementsEnPanne }}</div>
            <div class="kpi-label">Équipements en Panne</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #3b82f6, #60a5fa); color: #ffffff;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-val">{{ $tauxDisponibilite }}%</div>
            <div class="kpi-label">Taux Disponibilité</div>
        </div>
    </div>

    @if(in_array($role, ['admin', 'superviseur_soutarah']))
    <div class="kpi-card">
        <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #ffffff;">
            <i class="fa-solid fa-snowflake"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-val">{{ $stats['fiches_froid'] }}</div>
            <div class="kpi-label">Fiches F-GAS</div>
        </div>
    </div>
    @endif
</div>

<!-- Tableau des équipements classés par nombre d'interventions -->
<div class="equipements-table-wrapper">
    <div class="equipements-table-header">
        <h3>
            <i class="fa-solid fa-ranking-star"></i>
            Équipements classés par nombre d'interventions
        </h3>
    </div>
    <div class="equipements-table-body">
        <table class="equipements-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Marque</th>
                    <th>Site</th>
                    <th>Nb Interventions</th>
                    <th>Indicateur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipementsStats as $index => $equipement)
                <tr>
                    <td style="font-weight: 700; color: #64748b;">{{ $index + 1 }}</td>
                    <td><strong style="color: #059669;">{{ $equipement->equipement_code }}</strong></td>
                    <td>{{ $equipement->equipement_nom }}</td>
                    <td>{{ $equipement->marque ?? '-' }}</td>
                    <td>{{ $equipement->site->nom_site ?? '-' }}</td>
                    <td>
                        <span style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">
                            {{ $equipement->depannages_count }}
                        </span>
                    </td>
                    <td>
                        @if($equipement->depannages_count >= 10)
                            <span class="indicator-badge indicator-critique">
                                🔴 Critique
                            </span>
                        @elseif($equipement->depannages_count >= 6)
                            <span class="indicator-badge indicator-attention">
                                🟠 Attention
                            </span>
                        @elseif($equipement->depannages_count >= 3)
                            <span class="indicator-badge indicator-surveiller">
                                🟡 Surveiller
                            </span>
                        @else
                            <span class="indicator-badge indicator-normal">
                                🟢 Normal
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('equipements.show', $equipement->id) }}" 
                           class="btn-secondary" 
                           style="padding: 0.35rem 0.75rem; font-size: 0.75rem; text-decoration: none;">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 2rem;">
                        Aucune intervention enregistrée pour cette période.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Graphique des interventions par mois -->
<div class="chart-container">
    <div class="chart-header">
        <h3 class="chart-title">
            <i class="fa-solid fa-chart-column" style="color: #7c3aed;"></i>
            Interventions par mois (12 derniers mois)
        </h3>
    </div>
    <div class="chart-canvas-wrapper">
        <canvas id="interventionsChart"></canvas>
    </div>
</div>

<!-- Top 5 Sites -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="color: #059669;"><i class="fa-solid fa-location-dot"></i> Top 5 Sites avec le plus d'interventions</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Site</th>
                    <th>Interventions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topSites as $i => $site)
                <tr>
                    <td>
                        @if($i === 0) <span style="font-size: 1.2rem;">🥇</span>
                        @elseif($i === 1) <span style="font-size: 1.2rem;">🥈</span>
                        @elseif($i === 2) <span style="font-size: 1.2rem;">🥉</span>
                        @else <strong style="color: #64748b;">{{ $i + 1 }}</strong>
                        @endif
                    </td>
                    <td><strong style="color: #0f172a;">{{ $site->nom_site }}</strong></td>
                    <td><span class="badge badge-info">{{ $site->interventions_count }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune donnée disponible.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(in_array($role, ['admin', 'superviseur_soutarah']))
    {{-- Section Performance Techniciens (Admin et Superviseur Soutarah uniquement) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: #3b82f6;"><i class="fa-solid fa-users-gear"></i> Performance des Techniciens</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Technicien</th>
                        <th>Rôle</th>
                        <th>Interventions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topTechniciens as $i => $tech)
                    <tr>
                        <td>
                            @if($i === 0) <span style="font-size: 1.2rem;">🥇</span>
                            @elseif($i === 1) <span style="font-size: 1.2rem;">🥈</span>
                            @elseif($i === 2) <span style="font-size: 1.2rem;">🥉</span>
                            @else <strong style="color: #64748b;">{{ $i + 1 }}</strong>
                            @endif
                        </td>
                        <td><strong style="color: #0f172a;">{{ $tech->nom_complet }}</strong></td>
                        <td><span class="badge badge-info">{{ $tech->type_utilisateur }}</span></td>
                        <td><span class="badge badge-success">{{ $tech->depannages_count }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">Aucune donnée disponible.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endif {{-- Fin du @else (Vue Superviseur/Admin) --}}

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Chart.defaults.font.family = 'Varela Round';
        
        @if(in_array($role, ['technicien', 'chef technicien']))
            // ═══════════════════════════════════════════════════════════
            // GRAPHIQUE TECHNICIEN : MON ÉVOLUTION PERSONNELLE
            // ═══════════════════════════════════════════════════════════
            const ctxTech = document.getElementById('monEvolutionChart').getContext('2d');
            new Chart(ctxTech, {
                type: 'line',
                data: {
                    labels: {!! json_encode($labelsMois) !!},
                    datasets: [
                        {
                            label: 'Dépannages & Installations',
                            data: {!! json_encode($statsTechnicien['mon_evolution_dep']) !!},
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#059669',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Maintenances',
                            data: {!! json_encode($statsTechnicien['mon_evolution_maint']) !!},
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 7
                        }
                    ]
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
                                font: { size: 12, weight: '600' },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            padding: 14,
                            titleFont: { size: 13, weight: '700' },
                            bodyFont: { size: 12 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { 
                                color: '#64748b', 
                                font: { size: 12 },
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 12 } }
                        }
                    }
                }
            });
        @else
            // ═══════════════════════════════════════════════════════════
            // GRAPHIQUE SUPERVISEUR/ADMIN : INTERVENTIONS PAR MOIS
            // ═══════════════════════════════════════════════════════════
            const ctx = document.getElementById('interventionsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labelsMois) !!},
                    datasets: [
                        {
                            label: 'Dépannages',
                            data: {!! json_encode($interventionsParType['depannages']) !!},
                            backgroundColor: 'rgba(5, 150, 105, 0.8)',
                            borderColor: '#059669',
                            borderWidth: 2,
                            borderRadius: 8
                        },
                        {
                            label: 'Maintenances',
                            data: {!! json_encode($interventionsParType['maintenances']) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            borderRadius: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#64748b',
                                padding: 15,
                                font: { size: 13, weight: '600' },
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            padding: 12,
                            titleFont: { size: 14, weight: '700' },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { 
                                color: '#64748b', 
                                font: { size: 12 },
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 12 } }
                        }
                    }
                }
            });
        @endif
    });
</script>
@endsection
