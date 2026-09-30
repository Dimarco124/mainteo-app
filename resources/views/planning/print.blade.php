<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning {{ $monthName }} {{ $year }} — MAINTEO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #1a1d23;
            padding: 20px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .print-page {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        /* ═══════════════════════════════════════════
           HEADER — Clean & Corporate 
           ═══════════════════════════════════════════ */
        .planning-header {
            background: linear-gradient(135deg, #0d9668 0%, #059669 40%, #10b981 100%);
            padding: 18px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .planning-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .planning-header::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: 20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            border-radius: 50%;
        }

        .header-brand {
            position: relative;
            z-index: 1;
        }

        .header-brand .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-title .logo-icon {
            width: 28px;
            height: 28px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            backdrop-filter: blur(4px);
        }

        .header-brand .brand-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            margin-top: 3px;
            padding-left: 36px;
        }

        .header-info {
            text-align: right;
            position: relative;
            z-index: 1;
        }

        .header-info .month-label {
            font-size: 22px;
            font-weight: 800;
            color: white;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-info .meta-line {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            margin-top: 2px;
        }


        /* ═══════════════════════════════════════════
           LÉGENDE — Intégrée dans la barre de stats
           ═══════════════════════════════════════════ */
        .legend-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 8px 28px;
            background: white;
            border-bottom: 2px solid #e5e7eb;
        }

        .legend-title {
            font-size: 9px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-right: 4px;
        }

        .legend-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .legend-chip .chip-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-chip.dep {
            background: #fef2f2;
            color: #b91c1c;
        }
        .legend-chip.dep .chip-dot { background: #ef4444; }

        .legend-chip.inst {
            background: #fffbeb;
            color: #b45309;
        }
        .legend-chip.inst .chip-dot { background: #f59e0b; }

        .legend-chip.maint {
            background: #eff6ff;
            color: #1d4ed8;
        }
        .legend-chip.maint .chip-dot { background: #3b82f6; }

        /* ═══════════════════════════════════════════
           CALENDRIER — Professional Grid
           ═══════════════════════════════════════════ */
        .calendar-wrap {
            padding: 8px 16px 10px;
        }

        .cal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            overflow: hidden;
        }

        /* En-tête jours */
        .cal-table thead th {
            padding: 8px 4px;
            font-size: 10px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            text-align: center;
            background: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
            border-right: 1px solid #e5e7eb;
            letter-spacing: 0.8px;
        }

        .cal-table thead th:last-child {
            border-right: none;
        }

        .cal-table thead th.th-weekend {
            color: #9ca3af;
            background: #f9fafb;
        }

        /* Cases du calendrier */
        .cal-table td {
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            padding: 0;
            height: 72px;
            width: 14.28%;
            background: white;
            transition: background 0.15s;
        }

        .cal-table tr:last-child td {
            border-bottom: none;
        }

        .cal-table td:last-child {
            border-right: none;
        }

        .cal-table td.empty-day {
            background: #f9fafb;
        }

        .cal-table td.weekend-day {
            background: #fafbfc;
        }

        .cal-table td.today-day {
            background: #ecfdf5;
            box-shadow: inset 0 0 0 2px #059669;
        }

        /* Numéro du jour */
        .day-top {
            padding: 4px 6px 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .day-num {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
        }

        .weekend-day .day-num {
            color: #9ca3af;
        }

        .today-day .day-num {
            background: #059669;
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
        }

        .day-count {
            font-size: 8px;
            font-weight: 700;
            color: #9ca3af;
            background: #f3f4f6;
            padding: 1px 5px;
            border-radius: 8px;
        }

        /* Événements */
        .day-body {
            padding: 2px 3px 3px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .evt {
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 7.5px;
            line-height: 1.2;
            font-weight: 600;
            overflow: hidden;
            border-left: 3px solid;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        /* Continuous multi-day spanning */
        .evt.evt-span-start {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            margin-right: -4px;
            padding-right: 2px;
        }

        .evt.evt-span-middle {
            border-radius: 0;
            margin-left: -4px;
            margin-right: -4px;
            padding-left: 2px;
            padding-right: 2px;
            border-left-width: 0 !important;
            box-shadow: none;
        }

        .evt.evt-span-end {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            margin-left: -4px;
            padding-left: 2px;
            border-left-width: 0 !important;
        }

        .evt.evt-depannage {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
        }

        .evt.evt-installation {
            background: #fffbeb;
            border-left-color: #f59e0b;
            color: #92400e;
        }

        .evt.evt-maintenance {
            background: #eff6ff;
            border-left-color: #3b82f6;
            color: #1e40af;
        }

        .evt-row-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .evt-time {
            font-weight: 800;
            font-size: 7px;
            background: rgba(255, 255, 255, 0.7);
            padding: 0px 2px;
            border-radius: 2px;
            margin-right: 2px;
            flex-shrink: 0;
        }

        .evt-label {
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .evt-client {
            font-size: 6.5px;
            font-weight: 500;
            opacity: 0.85;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 1px;
        }

        .evt-more {
            font-size: 7px;
            font-weight: 700;
            color: #6b7280;
            text-align: center;
            background: #f3f4f6;
            border-radius: 3px;
            padding: 1px 4px;
            margin-top: 1px;
        }

        /* ═══════════════════════════════════════════
           FOOTER
           ═══════════════════════════════════════════ */
        .planning-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 28px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            font-weight: 500;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #059669;
        }

        /* ═══════════════════════════════════════════
           BOUTON IMPRESSION — Floating
           ═══════════════════════════════════════════ */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 1000;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .btn-print.primary {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
        }

        .btn-print.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.35);
        }

        .btn-print.secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-print.secondary:hover {
            background: #f9fafb;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        /* ═══════════════════════════════════════════
           PRINT OVERRIDES
           ═══════════════════════════════════════════ */
        @media print {
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                background: white;
            }

            body {
                padding: 0;
            }

            .print-actions {
                display: none !important;
            }

            .print-page {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            .print-page,
            .calendar-wrap,
            .cal-table {
                page-break-inside: avoid;
            }

            .planning-header {
                border-radius: 0;
            }

            .cal-table {
                border-radius: 0;
            }

            .planning-footer {
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    {{-- FLOATING PRINT BUTTON --}}
    <div class="print-actions">
        <button class="btn-print secondary" onclick="window.close()">
            ✕ Fermer
        </button>
        <button class="btn-print primary" onclick="window.print()">
            🖨️ Imprimer
        </button>
    </div>


    <div class="print-page">
        {{-- HEADER --}}
        <div class="planning-header">
            <div class="header-brand">
                <div class="brand-title">
                    <span class="logo-icon">📅</span>
                    PLANNING MAINTEO
                </div>
                <div class="brand-sub">{{ $user->nom_complet }} · {{ ucfirst(str_replace('_', ' ', $user->type_utilisateur ?? '')) }}</div>
            </div>
            <div class="header-info">
                <div class="month-label">{{ strtoupper($monthName) }} {{ $year }}</div>
                <div class="meta-line">Imprimé le {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>


        {{-- LÉGENDE --}}
        <div class="legend-bar">
            <span class="legend-title">Légende</span>
            <span class="legend-chip dep">
                <span class="chip-dot"></span>
                Dépannage
            </span>
            <span class="legend-chip inst">
                <span class="chip-dot"></span>
                Installation
            </span>
            <span class="legend-chip maint">
                <span class="chip-dot"></span>
                Maintenance
            </span>
        </div>

        {{-- CALENDRIER --}}
        <div class="calendar-wrap">
            <table class="cal-table">
                <thead>
                    <tr>
                        <th>Lundi</th>
                        <th>Mardi</th>
                        <th>Mercredi</th>
                        <th>Jeudi</th>
                        <th>Vendredi</th>
                        <th class="th-weekend">Samedi</th>
                        <th class="th-weekend">Dimanche</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendar as $week)
                    <tr>
                        @foreach($week as $day)
                            @if($day === null)
                                <td class="empty-day"></td>
                            @else
                                @php
                                    $tdClasses = [];
                                    if ($day['isToday']) $tdClasses[] = 'today-day';
                                    if ($day['date']->isWeekend()) $tdClasses[] = 'weekend-day';
                                    $interventionCount = isset($day['interventions']) ? count($day['interventions']) : 0;
                                @endphp
                                <td class="{{ implode(' ', $tdClasses) }}">
                                    <div class="day-top">
                                        <span class="day-num">{{ $day['day'] }}</span>
                                        @if($interventionCount > 0)
                                            <span class="day-count">{{ $interventionCount }}</span>
                                        @endif
                                    </div>
                                    <div class="day-body">
                                        @if($interventionCount > 0)
                                            @php
                                                $maxDisplay = 3;
                                                $displayed = 0;
                                            @endphp
                                            @foreach($day['interventions'] as $intervention)
                                                @if($displayed < $maxDisplay)
                                                    @php
                                                        $type = strtolower($intervention['type'] ?? 'dépannage');
                                                        $eventClass = 'evt-' . str_replace(['é', 'è', 'ê'], 'e', $type);
                                                        
                                                        $spanClass = '';
                                                        if (!empty($intervention['is_multi_day'])) {
                                                            if ($intervention['position'] === 'start') $spanClass = 'evt-span-start';
                                                            elseif ($intervention['position'] === 'middle') $spanClass = 'evt-span-middle';
                                                            elseif ($intervention['position'] === 'end') $spanClass = 'evt-span-end';
                                                        }
                                                        $displayed++;
                                                    @endphp
                                                    <div class="evt {{ $eventClass }} {{ $spanClass }}">
                                                        <div class="evt-row-main">
                                                            <div style="display: flex; align-items: center; gap: 2px; overflow: hidden;">
                                                                @if(!empty($intervention['is_multi_day']) && $intervention['position'] === 'middle')
                                                                    <span style="font-weight: 800; font-size: 6.5px; opacity: 0.7;">➔</span>
                                                                @endif
                                                                <span class="evt-time">{{ $intervention['time'] }}</span>
                                                                <span class="evt-label">{{ $intervention['label'] }}</span>
                                                            </div>
                                                            @if(!empty($intervention['is_multi_day']) && $intervention['position'] === 'start')
                                                                <span style="font-size: 6.5px; opacity: 0.7;">➔</span>
                                                            @endif
                                                        </div>
                                                        @if(!empty($intervention['client']) && ($intervention['position'] === 'single' || $intervention['position'] === 'start'))
                                                            <div class="evt-client">{{ $intervention['client'] }}</div>
                                                        @endif
                                                        @if(isset($intervention['pourcentage_avancement']) && !empty($intervention['nombre_equipements_prevus']) && $intervention['nombre_equipements_prevus'] > 0)
                                                            <div style="width: 100%; height: 3px; background: rgba(0,0,0,0.1); border-radius: 2px; margin-top: 2px; overflow: hidden;">
                                                                <div style="width: {{ $intervention['pourcentage_avancement'] }}%; height: 100%; background: #059669;"></div>
                                                            </div>
                                                            <div style="font-size: 6px; font-weight: 800; color: #047857; margin-top: 1px; display: flex; justify-content: space-between;">
                                                                <span>{{ $intervention['nombre_equipements_traites'] }}/{{ $intervention['nombre_equipements_prevus'] }} éq.</span>
                                                                <span>{{ $intervention['pourcentage_avancement'] }}%</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if($interventionCount > $maxDisplay)
                                                <div class="evt-more">
                                                    +{{ $interventionCount - $maxDisplay }} autre{{ ($interventionCount - $maxDisplay) > 1 ? 's' : '' }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="planning-footer">
            <div class="footer-left">
                <span class="footer-dot"></span>
                MAINTEO · Système de Gestion de Maintenance
            </div>
            <div>
                © {{ now()->year }} — Document confidentiel
            </div>
        </div>
    </div>
</body>
</html>
