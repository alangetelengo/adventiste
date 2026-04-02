<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport dîmes et offrandes {{ $mois }}/{{ $annee }}</title>
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
    $nomsMois = [1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'];
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
@endphp

<body>
    <div class="header">
        <div class="header-top">
            <img src="{{ asset('images/logo_adventiste.jpg') }}" alt="Logo Église Adventiste" class="logo">
            <div class="header-lines">
                <div class="line-1">STATION MISSIONNAIRE DES EGLISES ADVENTISTES</div>
                <div class="line-2">DU SEPTIEME JOUR AU CONGO</div>
            </div>
        </div>
        <h1>RAPPORT DÎMES ET OFFRANDES</h1>
        <p>{{ strtoupper((string) ($rapport->mission?->nom ?? 'MISSION')) }} — Mois : {{ ($nomsMois[(int) $mois] ?? $mois).'-'.substr((string) $annee, -2) }}</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Imprimer</button>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Désignation</th>
                <th class="num">Pourcentage</th>
                <th class="num">Total du mois</th>
                <th class="num">Total précédent</th>
                <th class="num">Total cumulé</th>
            </tr>
        </thead>
        <tbody>
            <tr class="title-row">
                <td>DIMES DES EGLISES</td>
                <td class="num"></td>
                <td class="num yellow">{{ $fmt((float) $rapport->dimes_eglises) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->dimes_eglises) }}</td>
            </tr>
            <tr class="title-row">
                <td>AUTRES DÎMES</td>
                <td class="num"></td>
                <td class="num">{{ $fmt((float) $rapport->autres_dimes) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->autres_dimes) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>TOTAL RECETTES DÎMES DU MOIS</strong></td>
                <td class="num"></td>
                <td class="num">{{ $fmt((float) $rapport->totalDimesMois()) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->totalDimesMois()) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL OFFRANDES DU MOIS</td>
                <td class="num"></td>
                <td class="num yellow">{{ $fmt((float) $rapport->offrandes_mois) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->offrandes_mois) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL REVENUS DU MOIS</td>
                <td class="num"></td>
                <td class="num">{{ $fmt((float) $rapport->totalRecettesMois()) }}</td>
                <td class="num">-</td>
                <td class="num">{{ $fmt((float) $rapport->totalRecettesMois()) }}</td>
            </tr>
            <tr class="title-row"><td>DIME DE LA DIME (UNION)</td><td class="num">8%</td><td class="num">{{ $fmt($val('dime_union', 'mois')) }}</td><td class="num">{{ $fmt($val('dime_union', 'precedent')) }}</td><td class="num">{{ $fmt($val('dime_union', 'cumule')) }}</td></tr>
            <tr class="title-row"><td>POURCENTAGE DE LA DIME</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>FONDS CONF. GENERALE</td><td class="num">2,6%</td><td class="num">{{ $fmt($val('fonds_cg', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_cg', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_cg', 'cumule')) }}</td></tr>
            <tr><td>FONDS INSTITUTIONS DAO</td><td class="num">2,0%</td><td class="num">{{ $fmt($val('fonds_dao_inst', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_dao_inst', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_dao_inst', 'cumule')) }}</td></tr>
            <tr><td>FONDS DE RETRAITES DAO</td><td class="num">12,0%</td><td class="num">{{ $fmt($val('fonds_retraites', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_retraites', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_retraites', 'cumule')) }}</td></tr>
            <tr><td>FONDS DIME PARTAGEE DAO</td><td class="num">7,4%</td><td class="num">{{ $fmt($val('fonds_dime_partagee', 'mois')) }}</td><td class="num">{{ $fmt($val('fonds_dime_partagee', 'precedent')) }}</td><td class="num">{{ $fmt($val('fonds_dime_partagee', 'cumule')) }}</td></tr>
            <tr class="total-row"><td>TOTAL POURCENTAGE DE DIME &gt;&gt;&gt;&gt;&gt;&gt;&gt;</td><td class="num"></td><td class="num">{{ $fmt($val('total_pct_dime', 'mois')) }}</td><td class="num">{{ $fmt($val('total_pct_dime', 'precedent')) }}</td><td class="num">{{ $fmt($val('total_pct_dime', 'cumule')) }}</td></tr>

            <tr class="title-row"><td>REPARTITION OFFRANDE</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr class="title-row"><td>CONF.GENERALE / DIVISION</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>FONDS CHAMPS MONDIALE - CG</td><td class="num">20%</td><td class="num">{{ $fmt($val('off_cg', 'mois')) }}</td><td class="num">{{ $fmt($val('off_cg', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_cg', 'cumule')) }}</td></tr>
            <tr><td>FONDS OFFRANDE - DAO</td><td class="num">5%</td><td class="num">{{ $fmt($val('off_dao', 'mois')) }}</td><td class="num">{{ $fmt($val('off_dao', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_dao', 'cumule')) }}</td></tr>
            <tr class="title-row"><td>UNION MISSION DE L'AFRIQUE CENTRALE</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>FONDS OFFRANDE UMAC</td><td class="num">5%</td><td class="num">{{ $fmt($val('off_umac', 'mois')) }}</td><td class="num">{{ $fmt($val('off_umac', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_umac', 'cumule')) }}</td></tr>
            <tr class="total-row"><td>TOTAL OFFRANDE (CG, DAO &amp; UMAC) &gt;&gt;&gt;&gt;&gt;&gt;&gt;</td><td class="num"></td><td class="num">{{ $fmt($val('total_off_haut', 'mois')) }}</td><td class="num">{{ $fmt($val('total_off_haut', 'precedent')) }}</td><td class="num">{{ $fmt($val('total_off_haut', 'cumule')) }}</td></tr>
            <tr class="total-row"><td>TOTAL RAPPORT (GC-DAO-UMAC)</td><td class="num"></td><td class="num">{{ $fmt($val('total_rapport', 'mois')) }}</td><td class="num">{{ $fmt($val('total_rapport', 'precedent')) }}</td><td class="num">{{ $fmt($val('total_rapport', 'cumule')) }}</td></tr>

            <tr class="title-row"><td>MISSION / FEDERATION</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>REPARTITION OFFRANDE</td><td class="num">20%</td><td class="num">{{ $fmt($val('off_mission', 'mois')) }}</td><td class="num">{{ $fmt($val('off_mission', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_mission', 'cumule')) }}</td></tr>
            <tr><td>OFFRANDE SPEC./PROJET</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr class="total-row"><td>TOTAL FONDS MISSION</td><td class="num"></td><td class="num">{{ $fmt($val('off_mission', 'mois')) }}</td><td class="num">{{ $fmt($val('off_mission', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_mission', 'cumule')) }}</td></tr>

            <tr class="title-row"><td>EGLISE LOCALE</td><td class="num"></td><td class="num"></td><td class="num"></td><td class="num"></td></tr>
            <tr><td>REPARTITION OFFRANDE</td><td class="num">50%</td><td class="num">{{ $fmt($val('off_locale', 'mois')) }}</td><td class="num">{{ $fmt($val('off_locale', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_locale', 'cumule')) }}</td></tr>
            <tr><td>OFFRANDE SPEC./PROJET</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>FONDS CONSTRUCTION EGLISE LOCALE</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr class="total-row"><td>TOTAL FONDS EGLISE LOCALE</td><td class="num"></td><td class="num">{{ $fmt($val('off_locale', 'mois')) }}</td><td class="num">{{ $fmt($val('off_locale', 'precedent')) }}</td><td class="num">{{ $fmt($val('off_locale', 'cumule')) }}</td></tr>

            <tr class="title-row"><td colspan="5">AUTRES DÎMES</td></tr>
            <tr><td>DIMES OUVRIERS DE BUREAU</td><td class="num"></td><td class="num yellow">{{ $fmt((float) $rapport->autres_dimes) }}</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>DIMES OUVRIERS GOC ET GOB</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>DIMES PIONNIERS MISSI. GLOB.</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>DIMES RE &amp; LIBRAIRIE</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>DIMES SPECIALES</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr><td>DIMES ECOLES &amp; COLLEGES</td><td class="num"></td><td class="num yellow">-</td><td class="num">-</td><td class="num">-</td></tr>
            <tr class="total-row"><td>TOTAL AUTRES DIMES &gt;&gt;&gt;&gt;&gt;&gt;&gt;</td><td class="num"></td><td class="num">{{ $fmt((float) $rapport->autres_dimes) }}</td><td class="num">-</td><td class="num">-</td></tr>
        </tbody>
    </table>
</body>

</html>
