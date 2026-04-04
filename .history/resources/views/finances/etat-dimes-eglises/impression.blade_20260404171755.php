<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État des dîmes des églises {{ $annee }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 16px;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        .header {
            margin-bottom: 12px;
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
            font-size: 18px;
            margin-bottom: 4px;
            text-align: center;
        }

        .header p {
            font-size: 12px;
            color: #4b5563;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
        }

        thead th {
            background: #e5e7eb;
            text-transform: uppercase;
            font-size: 11px;
        }

        tfoot td {
            font-weight: 700;
            background: #f3f4f6;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-top">
            <img src="{{ asset('images/logo_sda.png') }}" alt="Logo Église Adventiste" class="logo">
            <div class="header-lines">
                <div class="line-1">STATION MISSIONNAIRE DES EGLISES ADVENTISTES</div>
                <div class="line-2">DU SEPTIEME JOUR AU CONGO</div>
            </div>
        </div>
        <h1>ETAT DES DÎMES DES EGLISES</h1>
        <p>{{ strtoupper($missionNom) }} — Année {{ $annee }}</p>
    </div>

    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()">Imprimer</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th style="text-align:left;">Eglises</th>
                <th class="num">Objectif dîmes {{ $annee }}</th>
                <th class="num">Dîmes collectées {{ $annee }}</th>
                <th class="num">Offrandes collectées {{ $annee }}</th>
                <th class="num">Dîmes {{ $annee - 1 }}</th>
                <th class="num">Écart {{ $annee }} vs {{ $annee - 1 }}</th>
                <th class="num">Dîmes moyenne mensuelle</th>
                <th class="num">Nbre des membres</th>
                <th class="num">Pourcentage % apport</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
            <tr>
                <td>{{ $ligne['rang'] }}</td>
                <td>{{ $ligne['eglise_nom'] }}</td>
                <td class="num">{{ number_format($ligne['objectif_dimes'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['dimes_collectees'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['offrandes_collectees'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['dimes_annee_precedente'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['ecart_dimes'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['dimes_moyenne_mensuelle'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['nombre_membres'], 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['pourcentage_apport'], 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td class="num">{{ number_format($totaux['objectif_dimes'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['dimes_collectees'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['offrandes_collectees'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['dimes_annee_precedente'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['ecart_dimes'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['dimes_moyenne_mensuelle'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['nombre_membres'], 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['pourcentage_apport'], 2, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
