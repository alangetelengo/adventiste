<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('secretariat.rapports_membres.impression_title') }}</title>
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

    <div class="no-print">
        <button class="btn" onclick="window.print()">{{ __('secretariat.rapports_membres.print_export') }}</button>
    </div>

    <div class="container">
        <div class="title">
            <h1>{{ __('secretariat.rapports_membres.impression_h1') }}</h1>
            <p>{{ __('secretariat.rapports_membres.meta_mission') }}: {{ $rapport->egliseLocale->mission?->nom ?? '—' }} | {{ __('secretariat.rapports_membres.meta_church') }}: {{ $rapport->egliseLocale->nom }}</p>
            <p>
                {{ __('secretariat.rapports_membres.period_label') }}:
                {{ $typesPeriode[$rapport->type_periode] ?? $rapport->type_periode }}
                —
                @if ((int) $rapport->mois > 0)
                    {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromDate((int) $rapport->annee, (int) $rapport->mois, 1)->translatedFormat('F')) }}
                @else
                    {{ __('secretariat.rapports_membres.full_year') }}
                @endif
                {{ $rapport->annee }}
            </p>
        </div>

        <div class="box">
            <strong>{{ __('secretariat.rapports_membres.transmission_strong') }}:</strong> {{ $etats[$rapport->etat] ?? $rapport->etat }}
            @if($rapport->soumis_le)
                <div class="meta">{{ __('secretariat.rapports_membres.submitted_line', ['datetime' => $rapport->soumis_le->translatedFormat('d M Y H:i'), 'name' => $rapport->soumisPar?->name ?? '—']) }}</div>
            @endif
            @if($rapport->revu_le)
                <div class="meta">{{ __('secretariat.rapports_membres.reviewed_line', ['datetime' => $rapport->revu_le->translatedFormat('d M Y H:i'), 'name' => $rapport->revuPar?->name ?? '—']) }}</div>
            @endif
            @if($rapport->commentaire_mission)
                <div class="meta">{{ __('secretariat.rapports_membres.comment_mission') }}: {{ $rapport->commentaire_mission }}</div>
            @endif
        </div>

        <div class="box">
            <strong>{{ __('secretariat.rapports_membres.main_indicators') }}</strong>
            <div class="grid">
                <div class="row"><span>{{ __('secretariat.rapports_membres.total_members') }}</span><span>{{ number_format((int) $rapport->total_membres, 0, ',', ' ') }}</span></div>
                <div class="row"><span>{{ __('secretariat.rapports_membres.baptemes_immersion') }}</span><span>{{ number_format((int) $rapport->total_baptemes_immersion, 0, ',', ' ') }}</span></div>
                <div class="row"><span>{{ __('secretariat.rapports_membres.baptemes_profession') }}</span><span>{{ number_format((int) $rapport->total_baptemes_profession_foi, 0, ',', ' ') }}</span></div>
                <div class="row"><span>{{ __('secretariat.rapports_membres.entrees_transfert') }}</span><span>{{ number_format((int) $rapport->total_entrees_transfert, 0, ',', ' ') }}</span></div>
                <div class="row"><span>{{ __('secretariat.rapports_membres.status_changes') }}</span><span>{{ number_format((int) $rapport->total_changements_statut, 0, ',', ' ') }}</span></div>
            </div>
        </div>

        <div class="box">
            <strong>{{ __('secretariat.rapports_membres.by_status_title') }}</strong>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('secretariat.rapports_membres.col_status') }}</th>
                        <th>{{ __('secretariat.rapports_membres.col_headcount') }}</th>
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
                            <td colspan="2">{{ __('secretariat.rapports_membres.no_data_short') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="box">
            <strong>{{ __('secretariat.rapports_membres.church_secretary_observations') }}</strong>
            <p style="white-space: pre-wrap; margin: 8px 0 0;">{{ $rapport->notes_locales ?? '—' }}</p>
        </div>

        <div class="signatures">
            <div class="sign">{{ __('secretariat.rapports_membres.signature_church_secretary') }}</div>
            <div class="sign">{{ __('secretariat.rapports_membres.signature_mission_executive') }}</div>
        </div>
    </div>
</body>
</html>
