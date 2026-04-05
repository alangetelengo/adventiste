<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('impression_ventilation.html_title', ['mois' => $mois, 'annee' => $annee]) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 10px;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        .header {
            margin-bottom: 10px;
        }

        .header-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-lines {
            flex: 1;
            text-align: center;
        }

        .header-lines .line-1 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .header-lines .line-2 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .header h1 {
            font-size: 16px;
            margin-bottom: 4px;
            text-align: center;
        }

        .header p {
            color: #374151;
            font-size: 12px;
            text-align: center;
        }

        .no-print {
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 5px 6px;
            vertical-align: middle;
        }

        th {
            background: #e5e7eb;
            text-transform: uppercase;
            font-size: 10px;
        }

        .num {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .title-row td {
            background: #d1d5db;
            font-weight: 700;
            text-transform: uppercase;
        }

        .total-row td {
            background: #f3f4f6;
            font-weight: 700;
        }

        .yellow {
            background: #fef08a;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

@php
    $parCode = [];
    foreach ($lignes as $ligne) {
        if (! $ligne->estTitre() && $ligne->code) {
            $parCode[$ligne->code] = $montantsParLigne[$ligne->id] ?? ['precedent' => 0, 'mois' => 0, 'cumule' => 0];
        }
    }
    $fmt = fn (float $v) => number_format($v, 2, ',', ' ');
    $val = function (string $code, string $col) use ($parCode): float {
        return isset($parCode[$code][$col]) ? (float) $parCode[$code][$col] : 0.0;
    };
    $iv = fn (string $key) => __('impression_ventilation.'.$key);
    $monthShort = \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromDate((int) $annee, (int) $mois, 1)->translatedFormat('F')).'-'.substr((string) $annee, -2);
@endphp

<body>
    <div class="header">
        <div class="header-top">
            <img src="{{ asset('images/logo_sda.png') }}" alt="{{ $iv('logo_alt') }}" class="logo">
            <div class="header-lines">
                <div class="line-1">{{ $iv('header_line_1') }}</div>
                <div class="line-2">{{ $iv('header_line_2') }}</div>
            </div>
        </div>
        <h1>{{ $iv('h1') }}</h1>
        <p>{{ __('impression_ventilation.subtitle', ['mission' => strtoupper((string) ($rapport->mission?->nom ?? $iv('mission_fallback'))), 'month' => $monthShort]) }}</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">{{ $iv('print') }}</button>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">{{ $iv('th_designation') }}</th>
                <th class="num">{{ $iv('th_pct') }}</th>
                <th class="num">{{ $iv('th_month') }}</th>
                <th class="num">{{ $iv('th_prev') }}</th>
                <th class="num">{{ $iv('th_cumul') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="title-row">
                <td>{{ $iv('row_dimes_eglises') }}</td>
                <td class="num"></td>
                <td class="num yellow">{{ $fmt((float) $rapport->dimes_eglises) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->dimes_eglises) }}</td>
            </tr>
            <tr class="title-row">
                <td>{{ $iv('row_autres_dimes') }}</td>
                <td class="num"></td>
                <td class="num">{{ $fmt((float) $rapport->autres_dimes) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->autres_dimes) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>{{ $iv('row_total_recettes_dimes') }}</strong></td>
                <td class="num"></td>
                <td class="num">{{ $fmt((float) $rapport->totalDimesMois()) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->totalDimesMois()) }}</td>
            </tr>
            <tr class="total-row">
                <td>{{ $iv('row_total_offrandes') }}</td>
                <td class="num"></td>
                <td class="num yellow">{{ $fmt((float) $rapport->offrandes_mois) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->offrandes_mois) }}</td>
            </tr>
            <tr class="total-row">
                <td>{{ $iv('row_total_revenus') }}</td>
                <td class="num"></td>
                <td class="num">{{ $fmt((float) $rapport->totalRecettesMois()) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->totalRecettesMois()) }}</td>
            </tr>
            <tr class="title-row"><td>{{ $iv('row_dime_union') }}</td><td class="num">8%</td><td class="num">{{ $fmt($val('dime_union', 'mois')) }}</td><td class="num">{{ $fmt($val('dime_union', 'precedent')) }}</td><td class="num">{{ $fmt($val('dime_union', 'cumule')) }}</td></tr>
            <tr class="title-row"><td>{{ $iv('row_pct_dime_header') }}</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>{{ $iv('row_fonds_cg') }}</td><td class="num">2,6%</td><td class="num">{{ $fmt($val('fonds_cg', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_cg', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_cg', 'cumule')) }}</td></tr>
            <tr><td>{{ $iv('row_fonds_dao_inst') }}</td><td class="num">2,0%</td><td class="num">{{ $fmt($val('fonds_dao_inst', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_dao_inst', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_dao_inst', 'cumule')) }}</td></tr>
            <tr><td>{{ $iv('row_fonds_retraites') }}</td><td class="num">12,0%</td><td class="num">{{ $fmt($val('fonds_retraites', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_retraites', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_retraites', 'cumule')) }}</td></tr>
            <tr><td>{{ $iv('row_fonds_dime_partagee') }}</td><td class="num">7,4%</td><td class="num">{{ $fmt($val('fonds_dime_partagee', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_dime_partagee', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_dime_partagee', 'cumule')) }}</td></tr>
            <tr class="total-row"><td>{{ $iv('row_total_pct_dime') }}</td><td class="num"></td><td class="num">{{ $fmt($val('total_pct_dime', 'mois')) }}</td><td class="num">{{ $fmt($val('total_pct_dime', 'precedent')) }}</td><td class="num">{{ $fmt($val('total_pct_dime', 'cumule')) }}</td></tr>

            <tr class="title-row"><td>{{ $iv('row_repartition_offrande') }}</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr class="title-row"><td>{{ $iv('row_conf_division') }}</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>{{ $iv('row_fonds_champs_mondiale') }}</td><td class="num">20%</td><td class="num">{{ $fmt($val('off_cg', 'mois')) }}</td><td class="num">{{ $fmt($val('off_cg', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_cg', 'cumule')) }}</td></tr>
            <tr><td>{{ $iv('row_fonds_offrande_dao') }}</td><td class="num">5%</td><td class="num">{{ $fmt($val('off_dao', 'mois')) }}</td><td class="num">{{ $fmt($val('off_dao', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_dao', 'cumule')) }}</td></tr>
            <tr class="title-row"><td>{{ $iv('row_union_umac') }}</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>{{ $iv('row_fonds_offrande_umac') }}</td><td class="num">5%</td><td class="num">{{ $fmt($val('off_umac', 'mois')) }}</td><td class="num">{{ $fmt($val('off_umac', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_umac', 'cumule')) }}</td></tr>
            <tr class="total-row"><td>{{ $iv('row_total_offrande_haut') }}</td><td class="num"></td><td class="num">{{ $fmt($val('total_off_haut', 'mois')) }}</td><td class="num">{{ $fmt($val('total_off_haut', 'precedent')) }}</td><td class="num">{{ $fmt($val('total_off_haut', 'cumule')) }}</td></tr>
            <tr class="total-row"><td>{{ $iv('row_total_rapport') }}</td><td class="num"></td><td class="num">{{ $fmt($val('total_rapport', 'mois')) }}</td><td class="num">{{ $fmt($val('total_rapport', 'precedent')) }}</td><td class="num">{{ $fmt($val('total_rapport', 'cumule')) }}</td></tr>

            <tr class="title-row"><td>{{ $iv('row_mission_federation') }}</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>{{ $iv('row_repartition_offrande') }}</td><td class="num">20%</td><td class="num">{{ $fmt($val('off_mission', 'mois')) }}</td><td class="num">{{ $fmt($val('off_mission', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_mission', 'cumule')) }}</td></tr>
            <tr><td>{{ $iv('row_offrande_spec') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr class="total-row"><td>{{ $iv('row_total_fonds_mission') }}</td><td class="num"></td><td class="num">{{ $fmt($val('off_mission', 'mois')) }}</td><td class="num">{{ $fmt($val('off_mission', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_mission', 'cumule')) }}</td></tr>

            <tr class="title-row"><td>{{ $iv('row_eglise_locale') }}</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>{{ $iv('row_repartition_offrande') }}</td><td class="num">50%</td><td class="num">{{ $fmt($val('off_locale', 'mois')) }}</td><td class="num">{{ $fmt($val('off_locale', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_locale', 'cumule')) }}</td></tr>
            <tr><td>{{ $iv('row_offrande_spec') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>{{ $iv('row_fonds_construction_locale') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr class="total-row"><td>{{ $iv('row_total_fonds_locale') }}</td><td class="num"></td><td class="num">{{ $fmt($val('off_locale', 'mois')) }}</td><td class="num">{{ $fmt($val('off_locale', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_locale', 'cumule')) }}</td></tr>

            <tr class="title-row"><td colspan="5">{{ $iv('section_autres_dimes') }}</td></tr>
            <tr><td>{{ $iv('row_dimes_ouvriers_bureau') }}</td><td class="num"></td><td class="num yellow">{{ $fmt((float) $rapport->autres_dimes) }}</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>{{ $iv('row_dimes_ouvriers_goc') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>{{ $iv('row_dimes_pionniers') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>{{ $iv('row_dimes_re_librairie') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>{{ $iv('row_dimes_speciales') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>{{ $iv('row_dimes_ecoles') }}</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr class="total-row"><td>{{ $iv('row_total_autres_dimes') }}</td><td class="num"></td><td class="num">{{ $fmt((float) $rapport->autres_dimes) }}</td><td class="num">-</td><td class="num">-</td></tr>
        </tbody>
    </table>
</body>

</html>
