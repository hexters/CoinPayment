<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · {{ $title ?? 'Secure Checkout' }}</title>

    @php $theme = config('coinpayment.theme', []); @endphp

    {{-- Distinctive type pairing — standalone, never inherits the host app --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN (requested) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '{{ $theme['primary'] ?? '#2f6fed' }}',
                            dark: '{{ $theme['primary_dark'] ?? '#1f57c4' }}',
                        },
                        ink: '{{ $theme['text'] ?? '#0f1729' }}',
                        danger: '{{ $theme['danger'] ?? '#e02424' }}',
                    },
                    fontFamily: {
                        display: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'sans-serif'],
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(15,23,41,.04), 0 24px 48px -16px rgba(15,23,41,.16)',
                        glow: '0 0 0 1px rgba(255,255,255,.6) inset, 0 20px 60px -20px var(--cp-primary)',
                    },
                    keyframes: {
                        rise: { '0%': { opacity: 0, transform: 'translateY(14px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
                        floaty: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-6px)' } },
                    },
                    animation: {
                        rise: 'rise .6s cubic-bezier(.22,1,.36,1) both',
                        floaty: 'floaty 6s ease-in-out infinite',
                    },
                },
            },
        }
    </script>
    <style>
        :root {
            --cp-bg: {{ $theme['background'] ?? '#eef1f7' }};
            --cp-primary: {{ $theme['primary'] ?? '#2f6fed' }};
            --cp-primary-d: {{ $theme['primary_dark'] ?? '#1f57c4' }};
        }
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--cp-bg); }
        /* atmospheric backdrop */
        .cp-aurora {
            background:
                radial-gradient(60% 50% at 18% -8%, color-mix(in srgb, var(--cp-primary) 26%, transparent), transparent 70%),
                radial-gradient(50% 45% at 100% 0%, color-mix(in srgb, var(--cp-primary) 14%, transparent), transparent 65%),
                radial-gradient(40% 40% at 50% 120%, color-mix(in srgb, var(--cp-primary-d) 12%, transparent), transparent 70%);
        }
        .cp-grain { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.025'/%3E%3C/svg%3E"); }
        .cp-scroll::-webkit-scrollbar { width: 6px; }
        .cp-scroll::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--cp-primary) 30%, transparent); border-radius: 99px; }
        .stagger > * { animation: rise .6s cubic-bezier(.22,1,.36,1) both; }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen text-ink antialiased selection:bg-brand/20">
    <div class="fixed inset-0 -z-10 cp-aurora"></div>
    <div class="fixed inset-0 -z-10 cp-grain pointer-events-none"></div>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:py-12">
        {{ $slot }}
    </main>

    @livewire('coinpayment-license-gate')

    @livewireScripts
</body>
</html>
