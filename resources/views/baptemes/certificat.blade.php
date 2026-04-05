<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('bapteme_certificat.document_title') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            color: #0f172a;
            background: #f5f6f8;
            padding: 22px;
        }
        .toolbar {
            max-width: 1200px;
            margin: 0 auto 14px auto;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            text-decoration: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-primary {
            background: #059669;
            border-color: #047857;
            color: #fff;
        }
        .btn-primary:hover {
            background: #047857;
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d7dce3;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
            page-break-after: always;
            break-after: page;
        }
        .page:last-of-type {
            page-break-after: auto;
            break-after: auto;
        }

        .diamond-frame {
            border: 2px solid #c8c8c8;
            padding: 14px;
            position: relative;
            background: #fff;
        }
        .diamond-frame::before,
        .diamond-frame::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 12px;
            background:
                linear-gradient(45deg, #d9d9d9 25%, transparent 25%) -8px 0/16px 16px,
                linear-gradient(-45deg, #2f2f2f 25%, transparent 25%) -8px 0/16px 16px,
                linear-gradient(45deg, transparent 75%, #b7b7b7 75%) -8px 0/16px 16px,
                linear-gradient(-45deg, transparent 75%, #4a4a4a 75%) -8px 0/16px 16px;
        }
        .diamond-frame::before { top: 0; }
        .diamond-frame::after { bottom: 0; }

        .page-one-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .card {
            border: 3px solid #303030;
            min-height: 100%;
            padding: 20px 24px;
            position: relative;
        }
        .card-engagement {
            border: 1px solid #cfd3d8;
            padding: 14px 16px;
            background: #fff;
        }

        .title-main {
            margin: 0 0 8px 0;
            font-size: 34px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.6px;
        }
        .title-upper { text-transform: uppercase; }
        .small { margin: 2px 0; font-size: 14px; }

        .engagement-title {
            margin: 2px 0 8px 0;
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .engagement {
            margin: 0;
            font-size: 14px;
            line-height: 1.42;
            padding-left: 18px;
        }
        .engagement li { margin-bottom: 5px; }
        .quote {
            margin-top: 10px;
            font-size: 12px;
            text-align: center;
            font-style: italic;
        }

        .line-block { margin-top: 12px; font-size: 30px; line-height: 1.95; }
        .field {
            display: inline-block;
            min-width: 520px;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px 4px;
            font-weight: 700;
        }
        .field-sm {
            display: inline-block;
            min-width: 360px;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px 4px;
            font-weight: 700;
        }
        .field-xs {
            display: inline-block;
            min-width: 220px;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px 4px;
            font-weight: 700;
        }

        .cert-title {
            margin: 14px 0 10px 0;
            font-size: 54px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .institution {
            text-align: center;
            line-height: 1.35;
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 17px;
        }
        .institution strong {
            font-size: 20px;
        }
        .institution-with-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .institution-logo {
            width: 86px;
            height: 86px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .institution-text {
            text-align: center;
            line-height: 1.35;
        }

        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0.08;
            font-size: 240px;
            font-weight: 700;
            color: #3b82f6;
            z-index: 0;
        }
        .card-content { position: relative; z-index: 1; }

        .signature-row {
            margin-top: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
            font-size: 28px;
        }
        .sig-line {
            margin-top: 60px;
            border-top: 1px solid #111827;
            padding-top: 5px;
            text-align: center;
        }
        .divider {
            margin: 20px 0;
            border-top: 1px dashed #cbd5e1;
        }

        .admission-title {
            margin-top: 12px;
            text-align: center;
            font-size: 34px;
            text-transform: uppercase;
            border-top: 6px solid #222;
            padding-top: 10px;
            font-weight: 700;
        }

        .doctrines-title {
            margin: 2px 0 12px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .doctrine-columns {
            column-count: 2;
            column-gap: 22px;
            font-size: 14px;
            line-height: 1.34;
        }
        .doctrine-columns p {
            margin: 0 0 8px 0;
            break-inside: avoid;
        }
        .doctrine-columns strong {
            font-weight: 700;
        }
        .meta {
            margin-top: 10px;
            text-align: right;
            font-size: 12px;
            color: #475569;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
            }

            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .page {
                width: 285mm;
                height: 198mm;
                max-width: none;
                margin: 0 0 2mm 0;
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 4mm;
                overflow: hidden;
                page-break-after: always;
                break-after: page;
            }
            .page:last-of-type {
                page-break-after: auto;
                break-after: auto;
            }

            .diamond-frame {
                border-width: 1px;
                padding: 6px;
            }
            .diamond-frame::before,
            .diamond-frame::after {
                height: 7px;
                background-size: 12px 12px, 12px 12px, 12px 12px, 12px 12px;
            }

            .page-one-grid {
                gap: 8px;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .card,
            .card-engagement {
                min-height: auto;
                padding: 8px 10px;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .engagement-title {
                font-size: 20px;
                margin-bottom: 4px;
            }
            .engagement {
                font-size: 10px;
                line-height: 1.24;
                padding-left: 12px;
            }
            .engagement li {
                margin-bottom: 2px;
            }
            .quote {
                font-size: 8px;
                margin-top: 4px;
            }

            .institution {
                font-size: 10px;
                margin: 1px 0 3px 0;
            }
            .institution strong {
                font-size: 12px;
            }
            .institution-with-logo {
                gap: 8px;
                margin: 1px 0 3px 0;
            }
            .institution-logo {
                width: 54px;
                height: 54px;
            }
            .cert-title {
                font-size: 18px;
                margin: 6px 0 5px 0;
                letter-spacing: 0.3px;
            }
            .line-block {
                font-size: 9.5px;
                line-height: 1.34;
                margin-top: 5px;
            }
            .field { min-width: 170px; }
            .field-sm { min-width: 120px; }
            .field-xs { min-width: 72px; }

            .admission-title {
                font-size: 13px;
                border-top-width: 2px;
                padding-top: 4px;
                margin-top: 6px;
            }
            .signature-row {
                margin-top: 6px;
                font-size: 9px;
                gap: 8px;
            }
            .sig-line {
                margin-top: 10px;
                padding-top: 2px;
            }
            .watermark {
                font-size: 88px;
                opacity: 0.05;
            }

            .doctrines-title {
                font-size: 10px;
                margin: 1px 0 4px;
            }
            .doctrine-columns {
                font-size: 7px;
                line-height: 1.16;
                column-gap: 8px;
            }
            .doctrine-columns p {
                margin-bottom: 2px;
            }
            .meta {
                font-size: 7px;
                margin-top: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn btn-primary" onclick="window.print()">{{ __('bapteme_certificat.print') }}</button>
        <a class="btn" href="{{ route('baptemes.show', $bapteme) }}">{{ __('bapteme_certificat.back_record') }}</a>
    </div>

    <section class="page diamond-frame">
        <div class="page-one-grid">
            <div class="card-engagement">
                <h1 class="engagement-title">{{ __('bapteme_certificat.engagement_title') }}</h1>
                <ol class="engagement">
                    @foreach (__('bapteme_certificat.engagement_items') as $engagementItem)
                        <li>{{ $engagementItem }}</li>
                    @endforeach
                </ol>
                <p class="quote">{{ __('bapteme_certificat.quote_matthew') }}</p>
                <p class="quote">{{ __('bapteme_certificat.quote_revelation') }}</p>
                <p class="quote"><strong>{{ __('bapteme_certificat.quote_candidate_signature') }}</strong></p>
            </div>

            <div class="card">
                <div class="watermark">⛪</div>
                <div class="card-content">
                    <div class="institution-with-logo">
                        <img src="{{ asset('images/logo_sda.png') }}" alt="{{ __('bapteme_certificat.logo_alt', ['app' => config('app.name')]) }}" class="institution-logo">
                        <div class="institution-text">
                            <div><strong>{{ __('bapteme_certificat.institution_line1') }}</strong></div>
                            <div>{{ __('bapteme_certificat.institution_line2') }}</div>
                            <div><strong>{{ __('bapteme_certificat.institution_line3') }}</strong></div>
                        </div>
                    </div>
                    <h2 class="cert-title">{{ __('bapteme_certificat.cert_title') }}</h2>

                    <div class="line-block">
                        <div>{{ __('bapteme_certificat.cert_intro') }}</div>
                        <div>{{ __('bapteme_certificat.cert_honorific') }} <span class="field">{{ $bapteme->nom }} {{ $bapteme->prenom }}</span></div>
                        <div>
                            @if ($bapteme->type_bapteme === \App\Models\Bapteme::TYPE_PROFESSION_FOI)
                                {{ __('bapteme_certificat.cert_received_profession') }}
                            @else
                                {{ __('bapteme_certificat.cert_received_immersion') }}
                            @endif
                        </div>
                        <div>{{ __('bapteme_certificat.cert_at') }} <span class="field-sm">{{ $bapteme->lieu_bapteme ?? '____________________' }}</span> {{ __('bapteme_certificat.cert_on') }} <span class="field-xs">{{ $bapteme->date_bapteme?->format('d/m/Y') ?? '____/____/______' }}</span></div>
                        <div>{{ __('bapteme_certificat.cert_by') }}  <span class="field-sm">{{ $bapteme->officiant ?? '____________________' }}</span></div>
                        <div>{{ __('bapteme_certificat.cert_church_of') }} <span class="field-sm">{{ $bapteme->egliseLocale?->nom ?? '____________________' }}</span></div>
                        <div> <span class="field-sm">{{ $bapteme->egliseLocale?->district?->nom ?? '____________________' }}</span></div>
                        <div>{{ __('bapteme_certificat.cert_officiant_signature') }} <span class="field-sm"></span></div>
                    </div>

                    <div class="admission-title">{{ __('bapteme_certificat.admission_title') }}</div>
                    <div class="line-block" style="margin-top: 10px;">
                        <div>{{ __('bapteme_certificat.admission_received') }}</div>
                        <div>{{ __('bapteme_certificat.admission_from') }} <span class="field-sm">{{ $bapteme->egliseLocale?->nom ?? '____________________' }}</span> {{ __('bapteme_certificat.admission_on') }} <span class="field-xs">{{ ($bapteme->membre?->date_admission_eglise ?? $bapteme->date_bapteme)?->format('d/m/Y') ?? '____/____/______' }}</span></div>
                    </div>

                    <div class="signature-row">
                        <div></div>
                        <div>
                            <div class="sig-line">{{ __('bapteme_certificat.secretary_label') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="page diamond-frame">
        <h3 class="doctrines-title">{{ __('bapteme_certificat.doctrines_title') }}</h3>
        <div class="doctrine-columns">
            @foreach (__('bapteme_certificat.doctrine_items') as $doctrineItem)
                <p><strong>{{ $loop->iteration }}.</strong> {{ $doctrineItem }}</p>
            @endforeach
        </div>
    </section>
</body>
</html>

