@extends('layouts.app')

@section('title', 'Performance Mensuelle des Maintenances & Équipes')

@section('content')
<style>
.kpi-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.kpi-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
}
.cadence-badge-excel {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.cadence-badge-good {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}
.cadence-badge-warn {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
</style>

<div class="header" style="margin-bottom: 1.5rem;">
    <div class="page-title">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">
            📊 Performance Mensuelle des Maintenances & Équipes
        </h1>
        <p style="color: #64748b; font-size: 0.88rem; margin-top: 0.25rem;">
            Suivi des objectifs, des réalisations et de la cadence d'intervention pour 
            <strong style="color: #059669;">{{ ucfirst(\Carbon\Carbon::create()->month((int)$month)->translatedFormat('F')) }} {{ $year }}</strong>
        </p>
    </div>
    
    <div>
        {{-- Filtre Sélecteur Mois / Année --}}
        <form method="GET" action="{{ route('kpis.equipes') }}" style="display: flex; gap: 0.75rem; align-items: center; background: white; padding: 0.5rem 0.85rem; border-radius: 0.75rem; border: 1.5px solid #cbd5e1; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 0.45rem;">
                <i class="fa-solid fa-calendar-days" style="color: #059669; font-size: 0.95rem;"></i>
                <select name="month" style="border: none; background: transparent; font-weight: 700; color: #0f172a; font-size: 0.88rem; cursor: pointer; outline: none;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ ucfirst(\Carbon\Carbon::create()->month($m)->translatedFormat('F')) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div style="width: 1px; height: 20px; background: #cbd5e1;"></div>
            <div>
                <select name="year" style="border: none; background: transparent; font-weight: 700; color: #0f172a; font-size: 0.88rem; cursor: pointer; outline: none;">
                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn-primary" style="padding: 0.45rem 0.95rem; font-size: 0.82rem; border-radius: 0.5rem;">
                <i class="fa-solid fa-filter"></i> Actualiser
            </button>
        </form>
    </div>
</div>

{{-- 4 CARTES SYNTHÉTIQUES MENSUELLES --}}
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    {{-- 1. Objectif du Mois --}}
    <div class="kpi-stat-card">
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                Objectif Prévu (Mois)
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                {{ number_format($totalEquipementsPrevusMois, 0, ',', ' ') }} <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">éq.</span>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem;">
                <i class="fa-solid fa-calendar-check" style="color: #3b82f6;"></i> {{ $nbMaintenancesMois }} maintenance(s) planifiée(s)
            </div>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
            <i class="fa-solid fa-bullseye"></i>
        </div>
    </div>

    {{-- 2. Réalisé dans le Mois --}}
    <div class="kpi-stat-card">
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                Traités au Total (Mois)
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #059669; line-height: 1.2;">
                {{ number_format($totalEquipementsTraitesMois, 0, ',', ' ') }} <span style="font-size: 0.9rem; font-weight: 600; color: #047857;">éq.</span>
            </div>
            <div style="font-size: 0.78rem; color: #059669; margin-top: 0.35rem; font-weight: 600;">
                <i class="fa-solid fa-clipboard-check"></i> {{ $totalRapports }} rapport(s) d'équipes
            </div>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
            <i class="fa-solid fa-gears"></i>
        </div>
    </div>

    {{-- 3. Reste à Réaliser ce Mois --}}
    @php
        $estMoisComplete = ($totalEquipementsPrevusMois > 0 && $totalEquipementsRestantsMois == 0);
    @endphp
    <div class="kpi-stat-card" style="{{ $estMoisComplete ? 'border-color: #86efac; background: #f0fdf4;' : '' }}">
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: {{ $estMoisComplete ? '#047857' : '#c2410c' }}; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                Reste à Traiter (Mois)
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: {{ $estMoisComplete ? '#059669' : '#ea580c' }}; line-height: 1.2;">
                {{ number_format($totalEquipementsRestantsMois, 0, ',', ' ') }} <span style="font-size: 0.9rem; font-weight: 600; color: {{ $estMoisComplete ? '#047857' : '#c2410c' }};">éq.</span>
            </div>
            <div style="font-size: 0.78rem; color: {{ $estMoisComplete ? '#047857' : '#9a3412' }}; margin-top: 0.35rem; font-weight: 600;">
                {{ $estMoisComplete ? '✅ 100% de l\'objectif mensuel atteint' : 'À finaliser sur les sites' }}
            </div>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: {{ $estMoisComplete ? '#d1fae5' : '#ffedd5' }}; color: {{ $estMoisComplete ? '#059669' : '#ea580c' }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
            <i class="fa-solid {{ $estMoisComplete ? 'fa-circle-check' : 'fa-hourglass-half' }}"></i>
        </div>
    </div>

    {{-- 4. Taux d'accomplissement mensuel --}}
    <div class="kpi-stat-card">
        <div style="flex: 1;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                Taux de Réalisation
            </div>
            <div style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                {{ $tauxRealisationMois }}%
            </div>
            <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 0.5rem;">
                <div style="width: {{ $tauxRealisationMois }}%; height: 100%; background: linear-gradient(135deg, #059669, #10b981); border-radius: 3px;"></div>
            </div>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
    </div>
</div>

{{-- BANDEAU RÉCAPITULATIF STATUTS DU MOIS --}}
<div style="background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div style="font-size: 0.85rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-list-check" style="color: #059669;"></i>
        <span>Bilan des maintenances du mois :</span>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; font-size: 0.82rem;">
        <span style="background: #f1f5f9; color: #334155; padding: 0.25rem 0.65rem; border-radius: 9999px; font-weight: 700;">
            Total : {{ $nbMaintenancesMois }}
        </span>
        <span style="background: #dcfce7; color: #166534; padding: 0.25rem 0.65rem; border-radius: 9999px; font-weight: 700;">
            ✅ {{ $nbMaintenancesTerminees }} terminée(s)
        </span>
        <span style="background: #e0f2fe; color: #075985; padding: 0.25rem 0.65rem; border-radius: 9999px; font-weight: 700;">
            🔄 {{ $nbMaintenancesEnCours }} en cours
        </span>
        <span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.65rem; border-radius: 9999px; font-weight: 700;">
            ⏳ {{ $nbMaintenancesPlanifiees }} planifiée(s)
        </span>
        <span style="color: #64748b; font-weight: 600; border-left: 1px solid #cbd5e1; padding-left: 1rem;">
            Cadence globale : <strong>{{ $cadenceMoyenneGlobale }} éq./j</strong>
        </span>
    </div>
</div>

{{-- SECTION 1 : PERFORMANCE PAR ÉQUIPE SUR LE MOIS --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-trophy" style="color: #f59e0b;"></i> Performance & Cadence par Équipe
        </h2>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
            Objectif standard : <strong>8 équipements / jour / équipe</strong>
        </span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Équipe</th>
                    <th>Chef d'Équipe</th>
                    <th style="text-align: center;">Maintenances (Mois)</th>
                    <th style="text-align: center;">Éq. Traités (Mois)</th>
                    <th style="text-align: center;">Part du Cumul</th>
                    <th style="text-align: center;">Jours Actifs</th>
                    <th style="text-align: center;">Cadence Réalisée</th>
                    <th style="text-align: center;">Rapports</th>
                    <th style="text-align: center;">Anomalies</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipesStats as $item)
                    @php
                        $cadence = $item['cadence_moyenne'];
                        $cadenceClass = 'cadence-badge-good';
                        $cadenceLabel = 'Standard';
                        if ($cadence >= 8) {
                            $cadenceClass = 'cadence-badge-excel';
                            $cadenceLabel = 'Optimale (≥ 8)';
                        } elseif ($cadence < 6 && $item['jours_actifs'] > 0) {
                            $cadenceClass = 'cadence-badge-warn';
                            $cadenceLabel = 'À accélérer (< 6)';
                        }
                    @endphp
                    <tr>
                        <td style="font-weight: 700; color: #0f172a;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 30px; height: 30px; border-radius: 6px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <span>{{ $item['equipe']->nom_equipe }}</span>
                            </div>
                        </td>
                        <td style="color: #475569; font-weight: 600; font-size: 0.85rem;">
                            {{ $item['equipe']->chef->nom_complet ?? ($item['equipe']->chef->nom ?? 'Non assigné') }}
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #334155;">
                            {{ $item['nb_maintenances'] }}
                        </td>
                        <td style="text-align: center;">
                            <span style="background: #ecfdf5; color: #047857; padding: 0.25rem 0.65rem; border-radius: 0.4rem; font-weight: 800; font-size: 0.9rem; border: 1px solid #a7f3d0;">
                                +{{ number_format($item['total_traites'], 0, ',', ' ') }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: 800; font-size: 0.85rem; color: #0284c7;">
                                {{ $item['part_contribution'] }}%
                            </span>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #475569;">
                            {{ $item['jours_actifs'] }} j
                        </td>
                        <td style="text-align: center;">
                            <span class="{{ $cadenceClass }}" style="padding: 0.25rem 0.65rem; border-radius: 0.4rem; font-weight: 800; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="{{ $cadenceLabel }}">
                                {{ $cadence }} éq./j
                            </span>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            {{ $item['total_rapports'] }}
                        </td>
                        <td style="text-align: center;">
                            @if($item['anomalies_count'] > 0)
                                <span class="badge badge-warning" style="font-weight: 700; padding: 0.25rem 0.55rem;">
                                    ⚠️ {{ $item['anomalies_count'] }}
                                </span>
                            @else
                                <span class="badge badge-success" style="padding: 0.2rem 0.5rem;">0</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 0.5rem; color: #cbd5e1; display: block;"></i>
                            Aucune activité d'équipe enregistrée pour ce mois.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION 2 : MAINTENANCES PLANIFIÉES DANS CE MOIS ET LEUR PROGRESSION --}}
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-calendar-check" style="color: #059669;"></i> Maintenances Planifiées pour {{ ucfirst(\Carbon\Carbon::create()->month((int)$month)->translatedFormat('F')) }} {{ $year }}
        </h2>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">
            {{ $nbMaintenancesMois }} maintenance(s) dans le périmètre mensuel
        </span>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>N° Maintenance</th>
                    <th>Date Début</th>
                    <th>Client / Base / Site</th>
                    <th>Équipes Affectées</th>
                    <th style="text-align: center;">Objectif Prévu</th>
                    <th style="text-align: center;">Traités Total</th>
                    <th style="text-align: center;">Reste sur Site</th>
                    <th style="width: 220px;">Progression</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenancesMois as $m)
                    @php
                        $restantsSite = $m->nombre_equipements_restants;
                    @endphp
                    <tr>
                        <td style="font-weight: 700;">
                            <code style="color: #059669; font-size: 0.85rem; background: #ecfdf5; padding: 0.2rem 0.45rem; border-radius: 4px; border: 1px solid #a7f3d0;">
                                {{ $m->numero_maintenance }}
                            </code>
                        </td>
                        <td style="font-size: 0.82rem; color: #475569; white-space: nowrap;">
                            📅 {{ \Carbon\Carbon::parse($m->date_debut_prevue)->format('d/m/Y H:i') }}
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            <div>{{ $m->site->nom_site ?? ($m->client->nom ?? 'N/A') }}</div>
                            @if($m->base)
                            <small style="color: #64748b; font-size: 0.75rem;"><i class="fa-solid fa-location-dot"></i> {{ $m->base->nom_base }}</small>
                            @endif
                        </td>
                        <td style="color: #334155; font-size: 0.82rem;">
                            @if($m->equipes && $m->equipes->count() > 0)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                                    @foreach($m->equipes as $eq)
                                    <span style="background: #f1f5f9; color: #334155; padding: 0.15rem 0.45rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                        {{ $eq->nom_equipe }}
                                    </span>
                                    @endforeach
                                </div>
                            @elseif($m->equipe)
                                <span style="font-weight: 600;">{{ $m->equipe->nom_equipe }}</span>
                            @else
                                <span style="color: #94a3b8;">Non affectée</span>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #0f172a;">
                            {{ $m->nombre_equipements_prevus }} <span style="font-size: 0.75rem; color: #64748b;">éq.</span>
                        </td>
                        <td style="text-align: center; font-weight: 800; color: #059669;">
                            {{ $m->nombre_equipements_traites }} <span style="font-size: 0.75rem; color: #047857;">éq.</span>
                        </td>
                        <td style="text-align: center; font-weight: 800;">
                            @if($restantsSite == 0 && $m->nombre_equipements_prevus > 0)
                                <span style="color: #059669; background: #d1fae5; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.78rem;">0 (Terminé)</span>
                            @else
                                <span style="color: #ea580c; background: #ffedd5; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.78rem;">{{ $restantsSite }} éq.</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden;">
                                    <div style="width: {{ $m->pourcentage_avancement }}%; height: 100%; background: linear-gradient(135deg, #059669, #10b981); border-radius: 5px;"></div>
                                </div>
                                <span style="font-weight: 800; font-size: 0.78rem; color: #065f46; min-width: 42px;">{{ $m->pourcentage_avancement }}%</span>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @if($m->statut === 'terminée')
                                <span class="badge badge-success">Terminée</span>
                            @elseif($m->statut === 'en_cours')
                                <span class="badge badge-info">En cours</span>
                            @else
                                <span class="badge badge-warning">Planifiée</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('maintenances.show', $m->id) }}" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem; text-decoration: none; border-radius: 0.4rem; font-weight: 700;">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 0.5rem; color: #cbd5e1; display: block;"></i>
                            Aucune maintenance planifiée pour {{ ucfirst(\Carbon\Carbon::create()->month((int)$month)->translatedFormat('F')) }} {{ $year }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

