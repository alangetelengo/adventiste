<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport membres — Impression</title>
    <style>
        @page { size: A4 portrait; margin: 14mm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 0; }
        .no-print { display: block; margin: 16px; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 8px; border: 1px solid #0f766e; background: #0d9488; color: #fff; text-decoration: none; font-size: 13px; }
        .container { max-width: 980px; margin: 0 auto; padding: 16px; }
        .title { text-align: center; margin-bottom: 10px; }
        .title h1 { margin: 0; font-size: 20px; }
        .title p { margin: 4px 0 0; font-size: 12px; color: #475569; }
        .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; font-size: 13px; }
        .row { display: flex; justify-content: space-between; border-bottom: 1px dashed #e5e7eb; padding: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; font-size: 12px; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .meta { margin-top: 20px; font-size: 12px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 36px; font-size: 12px; }
        .sign { border-top: 1px solid #9ca3af; padding-top: 4px; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            .container { padding: 0; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
    @php
        $nomsMois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    @endphp

    <div class="no-print">
        <button class="btn" onclick="window.print()">Imprimer / Exporter PDF</button>
    </div>

    <div class="container">
        <div class="title">
            <h1>RAPPORT DES MEMBRES — ÉGLISE LOCALE</h1>
            <p>Mission: {{ $rapport->egliseLocale->mission?->nom ?? '—' }} | Église: {{ $rapport->egliseLocale->nom }}</p>
            <p>
                Période:
                {{ $typesPeriode[$rapport->type_periode] ?? $rapport->type_periode }}
                —
                @if ((int) $rapport->mois > 0)
                    {{ $nomsMois[(int) $rapport->mois] ?? $rapport->mois }}
                @else
                    Année complète
                @endif
                {{ $rapport->annee }}
            </p>
        </div>

        <div class="box">
            <strong>État de transmission:</strong> {{ $etats[$rapport->etat] ?? $rapport->etat }}
            @if($rapport->soumis_le)
                <div class="meta">Soumis le {{ $rapport->soumis_le->translatedFormat('d M Y à H:i') }} par {{ $rapport->soumisPar?->name ?? '—' }}</div>
            @endif
            @if($rapport->revu_le)
                <div class="meta">Revu le {{ $rapport->revu_le->translatedFormat('d M Y à H:i') }} par {{ $rapport->revuPar?->name ?? '—' }}</div>
            @endif
            @if($rapport->commentaire_mission)
                <div class="meta">Commentaire mission: {{ $rapport->commentaire_mission }}</div>
            @endif
        </div>

        <div class="box">
            <strong>Indicateurs principaux</strong>
            <div class="grid">
                <div class="row"><span>Total membres</span><span>{{ number_format((int) $rapport->total_membres, 0, ',', ' ') }}</span></div>
                <div class="row"><span>Baptêmes immersion</span><span>{{ number_format((int) $rapport->total_baptemes_immersion, 0, ',', ' ') }}</span></div>
                <div class="row"><span>Baptêmes profession de foi</span><span>{{ number_format((int) $rapport->total_baptemes_profession_foi, 0, ',', ' ') }}</span></div>
                <div class="row"><span>Entrées par transfert</span><span>{{ number_format((int) $rapport->total_entrees_transfert, 0, ',', ' ') }}</span></div>
                <div class="row"><span>Changements de statut</span><span>{{ number_format((int) $rapport->total_changements_statut, 0, ',', ' ') }}</span></div>
            </div>
        </div>

        <div class="box">
            <strong>Répartition par statut membre</strong>
            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Effectif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($rapport->stats_statuts ?? []) as $statut)
                        <tr>
                            <td>{{ $statut['libelle'] ?? '—' }}</td>
                            <td>{{ number_format((int) ($statut['total'] ?? 0), 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Aucune donnée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="box">
            <strong>Observations du secrétaire d'église</strong>
            <p style="white-space: pre-wrap; margin: 8px 0 0;">{{ $rapport->notes_locales ?? '—' }}</p>
        </div>

        <div class="signatures">
            <div class="sign">Secrétaire d'église</div>
            <div class="sign">Secrétaire exécutif de mission</div>
        </div>
    </div>
</body>
</html>
