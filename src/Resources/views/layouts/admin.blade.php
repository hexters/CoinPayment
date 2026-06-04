<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} · {{ $title ?? 'CoinPayment Admin' }}</title>

    @php $theme = config('coinpayment.theme', []); @endphp

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

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
                    },
                    keyframes: {
                        rise: { '0%': { opacity: 0, transform: 'translateY(14px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
                    },
                    animation: { rise: 'rise .5s cubic-bezier(.22,1,.36,1) both' },
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
        .cp-aurora {
            background:
                radial-gradient(55% 45% at 12% -10%, color-mix(in srgb, var(--cp-primary) 22%, transparent), transparent 70%),
                radial-gradient(45% 40% at 100% 0%, color-mix(in srgb, var(--cp-primary) 12%, transparent), transparent 65%);
        }
        .cp-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
        .cp-scroll::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--cp-primary) 30%, transparent); border-radius: 99px; }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen text-ink antialiased">
    <div class="fixed inset-0 -z-10 cp-aurora"></div>

    @php
        $tabs = [
            ['route' => 'coinpayment.admin.balances', 'label' => 'Balances', 'icon' => 'M19 7V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-2M21 9h-5a3 3 0 0 0 0 6h5V9Z'],
            ['route' => 'coinpayment.admin.withdrawals', 'label' => 'Withdrawals', 'icon' => 'M7 17 17 7m0 0H8m9 0v9'],
            ['route' => 'coinpayment.admin.transactions', 'label' => 'Transactions', 'icon' => 'M9 8h6M9 12h6M9 16h3M6 3h12a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 0 1 1-1Z'],
        ];
    @endphp

    {{-- Top navigation --}}
    <header class="sticky top-0 z-30 bg-brand shadow-lg shadow-brand/25">
        <div class="mx-auto flex max-w-6xl items-center justify-center gap-4 px-4 py-3 sm:justify-between">
            <a href="{{ route('coinpayment.admin.balances') }}" wire:navigate class="flex items-center">
                <img src="{{ asset('vendor/coinpayment/cps-logo-white.png') }}" alt="CoinPayments" class="h-7 w-auto">
            </a>
            {{-- desktop tabs --}}
            <nav class="hidden items-center gap-1 rounded-2xl bg-white/15 p-1 ring-1 ring-white/20 sm:flex">
                @foreach ($tabs as $tab)
                    <a href="{{ route($tab['route']) }}" wire:navigate
                       class="rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs($tab['route']) ? 'bg-white text-brand shadow-sm' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-6 pb-28 sm:py-8 sm:pb-8">
        {{ $slot }}
    </main>

    {{-- Mobile bottom navigation --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-black/5 bg-white/90 backdrop-blur-lg sm:hidden"
         style="padding-bottom:env(safe-area-inset-bottom);">
        <div class="mx-auto flex max-w-md items-stretch justify-around px-2 py-1.5">
            @foreach ($tabs as $tab)
                @php $active = request()->routeIs($tab['route']); @endphp
                <a href="{{ route($tab['route']) }}" wire:navigate
                   class="flex flex-1 flex-col items-center gap-1 rounded-2xl px-2 py-1.5 text-[11px] font-semibold transition {{ $active ? 'text-brand' : 'text-ink/45' }}">
                    <span class="grid h-9 w-9 place-items-center rounded-xl transition {{ $active ? 'bg-brand/10' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
                    </span>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    @livewire('coinpayment-license-gate')

    @livewireScripts
</body>
</html>
