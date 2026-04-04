<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Synthèse annuelle trésorerie mission {{ $annee }}</title>
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
            margin-bottom: 8px;
        }

        .header-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-lines {
            flex: 1;
            text-align: center;
        }

        .header-lines .line-1 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .header-lines .line-2 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .header h1 {
            font-size: 16px;
            margin-bottom: 2px;
            text-align: center;
        }

        .header p {
            color: #4b5563;
            text-align: center;
        }

        .section-title {
            font-weight: 700;
            margin: 10px 0 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 4px 6px;
        }

        thead th {
            background: #e5e7eb;
            font-size: 10px;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .strong {
            font-weight: 700;
        }

        .no-print {
            margin-bottom: 8px;
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
    $keysMois = array_keys($nomsMois);
    $sum = fn (array $row) => array_sum($row);
    $fmt = fn (float $v) => number_format($v, 2, ',', ' ');
    $fmtPct = fn (float $v) => number_format($v, 2, ',', ' ').'%';
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
        <h1>ETAT SYNTHETIQUE DES DÎMES ET OFFRANDES ET DES REVENUS {{ $annee }}</h1>
        <p>{{ strtoupper($missionNom) }}</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Imprimer</button>
    </div>

    <div class="section-title">CONGO</div>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Libellé</th>
                @foreach ($nomsMois as $nomMois)
                <th class="num">{{ $nomMois }}-{{ substr((string) $annee, -2) }}</th>
                @endforeach
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dîmes</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['dimes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['dimes'])) }}</td>
            </tr>
            <tr>
                <td>Offrandes</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['offrandes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['offrandes'])) }}</td>
            </tr>
            <tr class="strong">
                <td>Total Di+Off</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['total_dimes_offrandes'][$m]) }}</td>@endforeach
                <td class="num">{{ $fmt($sum($synthese['total_dimes_offrandes'])) }}</td>
            </tr>
            <tr>
                <td>% Offr. / Dîmes</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmtPct($synthese['ratio_offrandes_dimes'][$m]) }}</td>@endforeach
                @php
                    $sumDimes = $sum($synthese['dimes']);
                    $sumOff = $sum($synthese['offrandes']);
                    $ratioTotal = $sumDimes > 0 ? ($sumOff / $sumDimes) * 100 : 0;
                @endphp
                <td class="num strong">{{ $fmtPct($ratioTotal) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">REVENUS</div>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Libellé</th>
                @foreach ($nomsMois as $nomMois)
                <th class="num">{{ $nomMois }}-{{ substr((string) $annee, -2) }}</th>
                @endforeach
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dîmes (69%)</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['revenus_dimes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['revenus_dimes'])) }}</td>
            </tr>
            <tr>
                <td>Offrandes (20%)</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['revenus_offrandes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['revenus_offrandes'])) }}</td>
            </tr>
            <tr>
                <td>Autres offr.</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['autres_offrandes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['autres_offrandes'])) }}</td>
            </tr>
            <tr class="strong">
                <td>Total revenus</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['total_revenus'][$m]) }}</td>@endforeach
                <td class="num">{{ $fmt($sum($synthese['total_revenus'])) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">ETAT DU TRANSFERT DE FONDS EN DEPOT</div>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Libellé</th>
                @foreach ($nomsMois as $nomMois)
                <th class="num">{{ $nomMois }}-{{ substr((string) $annee, -2) }}</th>
                @endforeach
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>%tage dîmes (31%)</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['transfert_dimes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['transfert_dimes'])) }}</td>
            </tr>
            <tr>
                <td>%tage offr. (30%)</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['transfert_offrandes'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['transfert_offrandes'])) }}</td>
            </tr>
            <tr class="strong">
                <td>Total à transférer</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['total_a_transferer'][$m]) }}</td>@endforeach
                <td class="num">{{ $fmt($sum($synthese['total_a_transferer'])) }}</td>
            </tr>
            <tr>
                <td>Transf. Bank. effectué</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['transfert_banque_effectue'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['transfert_banque_effectue'])) }}</td>
            </tr>
            <tr>
                <td>Différence sur transfert</td>
                @foreach ($keysMois as $m)<td class="num">{{ $fmt($synthese['difference_transfert'][$m]) }}</td>@endforeach
                <td class="num strong">{{ $fmt($sum($synthese['difference_transfert'])) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
