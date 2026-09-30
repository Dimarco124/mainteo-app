<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Planning {{ $monthName }} {{ $year }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Varela Round', 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #1e293b;
            background: #ffffff;
            padding: 15mm;
        }
        
        .page-container {
            max-width: 100%;
            height: 100%;
            display: table;
            table-layout: fixed;
        }
        
        /* HEADER - Style MAINTEO Chic */
        .header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            padding: 12px 18px;
            margin-bottom: 10px;
            border-radius: 8px;
            color: white;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.2);
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }
        
        .header h1 {
            font-size: 16px;
            color: white;
            margin-bottom: 2px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .header .subtitle {
            font-size: 8px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 400;
        }
        
        .header-right .month-year {
            font-size: 14px;
            font-weight: 700;
            color: white;
            letter-spacing: 1.5px;
        }
        
        .header-right .user-info {
            font-size: 7px;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 2px;
        }
        
        /* LÉGENDE - Compacte et élégante */
        .legend {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .legend-label {
            font-weight: 700;
            color: #475569;
            font-size: 7px;
            margin-right: 8px;
            letter-spacing: 0.5px;
        }
        
        .legend-item {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: 700;
            margin-right: 8px;
            border: 1.5px solid;
        }
        
        .legend-item.depannage {
            background: #fef2f2;
            color: #991b1b;
            border-color: #dc2626;
        }
        
        .legend-item.installation {
            background: #fffbeb;
            color: #b45309;
            border-color: #f59e0b;
        }
        
        .legend-item.maintenance {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #3b82f6;
        }
        
        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }
        
        /* CALENDRIER - Compact et Pro */
        .fc-calendar {
            background: white;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }
        
        .fc-calendar-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .fc-calendar-table thead {
            background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
        }
        
        .fc-calendar-table th {
            padding: 6px 4px;
            font-size: 7px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            text-align: center;
            border-right: 1px solid #e2e8f0;
            border-bottom: 2px solid #cbd5e1;
            letter-spacing: 0.5px;
        }
        
        .fc-calendar-table th:last-child {
            border-right: none;
        }
        
        .fc-calendar-table td {
            border: 1px solid #e2e8f0;
            border-right: 1px solid #cbd5e1;
            padding: 0;
            vertical-align: top;
            height: 68px;
            width: 14.28%;
            background: white;
        }
        
        .fc-calendar-table td:last-child {
            border-right: none;
        }
        
        .fc-calendar-table td.fc-day-other {
            background: #f8fafc;
            opacity: 0.4;
        }
        
        .fc-calendar-table td.fc-day-sat,
        .fc-calendar-table td.fc-day-sun {
            background: #fafafa;
        }
        
        .fc-calendar-table td.fc-day-today {
            background: #ecfdf5;
            border: 2px solid #059669;
        }
        
        .fc-day-top {
            padding: 3px 5px;
            min-height: 18px;
            background: rgba(248, 250, 252, 0.4);
            border-bottom: 1px solid #f1f5f9;
        }
        
        .fc-day-number {
            font-size: 9px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .fc-day-today .fc-day-top {
            background: rgba(5, 150, 105, 0.08);
        }
        
        .fc-day-today .fc-day-number {
            background: #059669;
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            display: inline-block;
            min-width: 18px;
            text-align: center;
            font-size: 8px;
        }
        
        .fc-event-container {
            padding: 2px 3px 3px 3px;
        }
        
        .fc-event {
            padding: 2px 3px;
            margin-bottom: 2px;
            border-radius: 3px;
            font-size: 6.5px;
            line-height: 1.3;
            border-left: 3px solid;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
        }
        
        .fc-event.fc-event-depannage {
            background: #fee2e2;
            border-color: #dc2626;
            color: #991b1b;
        }
        
        .fc-event.fc-event-installation {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .fc-event.fc-event-maintenance {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
        }
        
        .fc-event-time {
            font-weight: 700;
            margin-right: 3px;
        }
        
        .fc-event-more {
            font-size: 6px;
            color: #64748b;
            text-align: center;
            padding: 2px;
            font-weight: 700;
            background: #f8fafc;
            border-radius: 2px;
            margin-top: 1px;
        }
        
        /* FOOTER - Élégant */
        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 6.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <h1>📅 PLANNING DES INTERVENTIONS</h1>
                    <div class="subtitle">{{ $user->nom_complet }} • {{ ucfirst(str_replace('_', ' ', $user->type_utilisateur)) }}</div>
                </div>
                <div class="header-right">
                    <div class="month-year">{{ strtoupper($monthName) }} {{ $year }}</div>
                    <div class="user-info">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
        </div>
        
        <!-- LÉGENDE -->
        <div class="legend">
            <span class="legend-label">TYPES D'INTERVENTIONS :</span>
            <span class="legend-item depannage">
                <span class="legend-dot" style="background: #dc2626;"></span>
                DÉPANNAGE
            </span>
            <span class="legend-item installation">
                <span class="legend-dot" style="background: #f59e0b;"></span>
                INSTALLATION
            </span>
            <span class="legend-item maintenance">
                <span class="legend-dot" style="background: #3b82f6;"></span>
                MAINTENANCE
            </span>
        </div>
        
        <!-- CALENDRIER STYLE FULLCALENDAR -->
        <div class="fc-calendar">
            <table class="fc-calendar-table">
                <thead>
                    <tr>
                        <th>LUN</th>
                        <th>MAR</th>
                        <th>MER</th>
                        <th>JEU</th>
                        <th>VEN</th>
                        <th>SAM</th>
                        <th>DIM</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendar as $week)
                    <tr>
                        @foreach($week as $day)
                            @if($day === null)
                                <td class="fc-day-other"></td>
                            @else
                                @php
                                    $dayClasses = ['fc-day'];
                                    if ($day['isToday']) $dayClasses[] = 'fc-day-today';
                                    if ($day['date']->dayOfWeek == 6) $dayClasses[] = 'fc-day-sat';
                                    if ($day['date']->dayOfWeek == 0) $dayClasses[] = 'fc-day-sun';
                                @endphp
                                <td class="{{ implode(' ', $dayClasses) }}">
                                    <div class="fc-day-top">
                                        <span class="fc-day-number">{{ $day['day'] }}</span>
                                    </div>
                                    <div class="fc-event-container">
                                        @if(isset($day['interventions']) && count($day['interventions']) > 0)
                                            @php
                                                $maxDisplay = 3;
                                                $displayed = 0;
                                            @endphp
                                            @foreach($day['interventions'] as $intervention)
                                                @if($displayed < $maxDisplay)
                                                    @php
                                                        $type = strtolower($intervention['type'] ?? 'dépannage');
                                                        $eventClass = 'fc-event-' . str_replace(['é', 'è', 'ê'], 'e', $type);
                                                        $displayed++;
                                                    @endphp
                                                    <div class="fc-event {{ $eventClass }}">
                                                        <span class="fc-event-time">{{ $intervention['time'] }}</span>
                                                        {{ $intervention['label'] }}
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if(count($day['interventions']) > $maxDisplay)
                                                <div class="fc-event-more">
                                                    +{{ count($day['interventions']) - $maxDisplay }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- FOOTER -->
        <div class="footer">
            MAINTEO GMAO • SYSTÈME DE GESTION DE MAINTENANCE © {{ now()->year }} • DOCUMENT CONFIDENTIEL
        </div>
    </div>
</body>
</html>