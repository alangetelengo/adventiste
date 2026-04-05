<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — {{ __('ui.login.heading') }}</title>
    @include('partials.favicon')
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #0b1320; color: #fff; min-height: 100vh; display: grid; place-items: center; }
        .shell { width: min(1040px, 94vw); display: grid; grid-template-columns: minmax(0, 1fr) 360px; background: #fff; color: #0f172a; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,.35); position: relative; }
        .locale-fixed { position: absolute; top: 14px; right: 14px; z-index: 5; }
        .hero { background: radial-gradient(circle at 20% 80%, rgba(0,180,100,.35), transparent 45%), linear-gradient(135deg, #0a0f15, #111b24, #0a0f15); padding: 42px; position: relative; }
        .hero h1 { margin-top: 18px; font-size: 2.2rem; color: #f3f4f6; }
        .hero p { margin-top: 10px; color: #cbd5e1; line-height: 1.5; }
        .logo { width: 152px; height: 152px; border-radius: 50%; object-fit: contain; background: #fff; padding: 10px; border: 2px solid rgba(212,168,75,.7); display: block; box-sizing: border-box; }
        .form { padding: 36px 26px; }
        .form h2 { font-size: 1.7rem; margin-bottom: 6px; }
        .muted { color: #64748b; margin-bottom: 18px; font-size: .95rem; }
        .group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 6px; font-size: .85rem; font-weight: 700; color: #334155; }
        input[type="email"], input[type="password"] { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px; font-size: .95rem; }
        input:focus { outline: none; border-color: #00a86b; box-shadow: 0 0 0 3px rgba(0,168,107,.15); }
        .remember { display: flex; align-items: center; gap: 8px; margin: 8px 0 16px; color: #475569; font-size: .9rem; }
        .btn { width: 100%; border: 0; border-radius: 10px; padding: 12px; background: linear-gradient(135deg, #00b464, #009d58); color: #fff; font-weight: 700; cursor: pointer; }
        .form-submit-spinner { display: inline-block; width: 1em; height: 1em; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: form-submit-spin .6s linear infinite; vertical-align: -.2em; margin-right: .35rem; }
        .form-submit-loading { opacity: .78; cursor: not-allowed; }
        @keyframes form-submit-spin { to { transform: rotate(360deg); } }
        .err { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; font-size: .88rem; }
        /* Sélecteur FR | EN (sans Tailwind sur cette page) */
        .locale-segmented {
            display: inline-flex;
            align-items: stretch;
            padding: 3px;
            gap: 2px;
            background: #e8eef3;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, .06);
        }
        .locale-segmented__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.75rem;
            padding: 7px 14px;
            border: 0;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-decoration: none;
            color: #64748b;
            background: transparent;
            cursor: pointer;
            transition: color .15s ease, background .15s ease, box-shadow .15s ease;
        }
        .locale-segmented__btn:hover:not(.is-active) {
            color: #0f172a;
            background: rgba(255, 255, 255, .85);
        }
        .locale-segmented__btn.is-active {
            color: #fff;
            background: linear-gradient(135deg, #00b464, #009d58);
            box-shadow: 0 2px 10px rgba(0, 180, 100, .35);
        }
        .locale-segmented__btn:focus-visible {
            outline: 2px solid #00a86b;
            outline-offset: 2px;
        }
        @media (max-width: 860px) { .shell { grid-template-columns: 1fr; } .hero .logo { width: 128px; height: 128px; padding: 8px; margin: 0 auto; } .hero { text-align: center; } .hero h1 { margin-top: 14px; } }
    </style>
</head>
<body>
    <div class="shell">
        <div class="locale-fixed">
            @include('partials.locale-switcher', ['variant' => 'login'])
        </div>
        <section class="hero">
            <img src="{{ asset('images/logo_sda.png') }}" alt="{{ config('app.name') }}" class="logo" width="152" height="152">
            <h1>{{ config('app.name') }}</h1>
            <p>{{ __('ui.hero_tagline') }}</p>
            <p class="muted" style="text-align: center;">{{ __('ui.hero_footer') }}</p>
        </section>
        <section class="form">
            <h2>{{ __('ui.login.heading') }}</h2>
            <p class="muted">{{ __('ui.login.subheading') }}</p>

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" data-loading-text="{{ __('ui.signing_in') }}">
                @csrf
                <div class="group">
                    <label for="email">{{ __('ui.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>
                <div class="group">
                    <label for="password">{{ __('ui.password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                </div>
                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    {{ __('ui.remember_me') }}
                </label>
                <button type="submit" class="btn" data-loading-text="{{ __('ui.signing_in') }}">{{ __('ui.sign_in') }}</button>
            </form>
        </section>
    </div>
    <script>
        (function () {
            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (form.dataset.skipSubmitLoading === '1') return;

                var submitter = event.submitter;
                var btn = submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
                    ? submitter
                    : form.querySelector('button[type="submit"]:not([disabled]), input[type="submit"]:not([disabled])');
                if (!btn) return;
                if (btn.dataset.loading === '1') return;

                btn.dataset.loading = '1';
                if (!btn.dataset.originalHtml && btn instanceof HTMLButtonElement) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                var loadingText = btn.dataset.loadingText || form.dataset.loadingText || @json(__('ui.loading'));
                if (btn instanceof HTMLButtonElement) {
                    btn.innerHTML = '<span class="form-submit-spinner"></span> ' + loadingText;
                } else {
                    btn.value = loadingText;
                }

                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
                btn.classList.add('form-submit-loading');
            }, true);
        })();
    </script>
</body>
</html>
