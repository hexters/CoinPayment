@php
    $fmt = fn ($value) => number_format((float) $value, 2);
@endphp

<div
    x-data="{
        search: '',
        showModal: false,
        showConfirm: false,
        showResult: @js($transaction !== null),
        match(name) { return name.toLowerCase().includes(this.search.toLowerCase()); }
    }"
    x-on:transaction-created.window="showResult = true; showModal = false; showConfirm = false"
    x-on:transaction-failed.window="showConfirm = false"
    class="font-sans"
>
    @if ($fatal)
        {{-- Fatal payload / rates error --}}
        <div class="mx-auto max-w-md animate-rise rounded-3xl bg-white/80 p-8 text-center shadow-card ring-1 ring-black/5 backdrop-blur">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-danger/10 text-danger">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.42 0Z"/></svg>
            </div>
            <h1 class="font-display text-xl font-bold">Unable to load checkout</h1>
            <p class="mt-2 text-sm text-ink/60">{{ $errorMessage }}</p>
        </div>
    @else
        <div class="grid gap-5 lg:grid-cols-12 lg:gap-6">
            {{-- ───────────────── Order summary ───────────────── --}}
            <section class="lg:col-span-5">
                <div class="lg:sticky lg:top-8 animate-rise overflow-hidden rounded-3xl bg-white/85 shadow-card ring-1 ring-black/5 backdrop-blur" style="animation-delay:.06s">
                    {{-- merchant header --}}
                    <div class="relative border-b border-black/5 bg-gradient-to-br from-brand/[0.07] to-transparent px-6 py-6 text-center">
                        @if (($header['default'] ?? 'logo') === 'logo')
                            <img src="{{ url($header['type']['logo'] ?? '') }}" alt="{{ $header['type']['text'] ?? '' }}" class="mx-auto h-10 w-auto object-contain">
                        @else
                            <p class="font-display text-lg font-bold">{{ $header['type']['text'] ?? 'Payment summary' }}</p>
                        @endif
                        <p class="mt-1 text-xs font-medium text-ink/45">Order #{{ $data['order_id'] ?? '—' }}</p>
                    </div>

                    <div class="space-y-5 px-6 py-5">
                        {{-- buyer --}}
                        <dl class="space-y-1.5 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-ink/45">Name</dt>
                                <dd class="truncate font-medium">{{ $data['buyer_name'] ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-ink/45">E-mail</dt>
                                <dd class="truncate font-medium">{{ $data['buyer_email'] ?? '—' }}</dd>
                            </div>
                        </dl>

                        {{-- items --}}
                        <div class="rounded-2xl bg-ink/[0.025] p-1.5 ring-1 ring-black/5">
                            <div class="cp-scroll max-h-48 space-y-1 overflow-y-auto p-1">
                                @foreach ($data['items'] ?? [] as $item)
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white px-3 py-2.5 shadow-sm ring-1 ring-black/[0.04]">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold">{{ $item['itemDescription'] }}</p>
                                            <p class="mt-0.5 text-xs text-ink/45">{{ $fmt($item['itemPrice']) }} {{ $defaultCurrency }} × {{ $item['itemQty'] }}</p>
                                        </div>
                                        <p class="shrink-0 font-mono text-sm font-semibold">{{ $fmt($item['itemSubtotalAmount']) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- totals --}}
                        <div class="space-y-2.5 rounded-2xl bg-brand/[0.06] px-4 py-4 ring-1 ring-brand/10">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-ink/55">Total {{ $defaultCurrency }}</span>
                                <span class="font-mono font-semibold">{{ $fmt($data['amountTotal'] ?? 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-ink/55">Pay with</span>
                                <span class="inline-flex items-center gap-1.5 font-semibold">
                                    @if (!empty($defaultCoin['icon']))
                                        <img src="{{ $defaultCoin['icon'] }}" alt="" class="h-4 w-4" onerror="this.style.display='none'">
                                    @endif
                                    {{ $defaultCoin['iso'] ?? '—' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-brand/15 pt-2.5">
                                <span class="text-sm font-medium text-ink/55">You send</span>
                                <span class="font-mono text-lg font-bold text-brand">{{ $defaultCoin['amount'] ?? '—' }} <span class="text-sm">{{ $defaultCoin['iso'] ?? '' }}</span></span>
                            </div>
                        </div>

                        @if (!empty($data['note']))
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-center text-xs text-amber-700 ring-1 ring-amber-100">{{ $data['note'] }}</p>
                        @endif

                        @if ($errorMessage)
                            <div x-data x-init="$el.scrollIntoView({behavior:'smooth',block:'center'})" class="flex items-start gap-2 rounded-xl bg-danger/8 px-3 py-2.5 text-sm text-danger ring-1 ring-danger/15">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                                <span>{{ $errorMessage }}</span>
                            </div>
                        @endif

                        {{-- actions --}}
                        <div class="space-y-2.5">
                            <button
                                type="button"
                                class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-red-600 px-5 py-4 text-base font-bold text-white shadow-lg shadow-red-600/30 transition hover:bg-red-700 active:scale-[.99] disabled:opacity-60"
                                x-on:click="showConfirm = true"
                                @disabled(empty($defaultCoin['iso']))
                            >
                                Pay now
                                <svg class="h-5 w-5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            </button>

                            <button type="button" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-ink/5 px-5 py-3 text-sm font-semibold text-ink/70 transition hover:bg-ink/10 lg:hidden" x-on:click="showModal = true">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                Choose coin · {{ count($acceptedCoins) }} available
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="{{ $data['cancel_url'] ?? $data['redirect_url'] ?? '#' }}" class="text-xs font-medium text-ink/40 transition hover:text-ink/70">← Cancel transaction</a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ───────────────── Coin chooser (desktop) ───────────────── --}}
            <section class="hidden lg:col-span-7 lg:block">
                <div class="animate-rise rounded-3xl bg-white/85 p-6 shadow-card ring-1 ring-black/5 backdrop-blur" style="animation-delay:.12s">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-display text-lg font-bold">Select a coin</h2>
                        <span class="rounded-full bg-ink/5 px-2.5 py-1 text-xs font-semibold text-ink/50">{{ count($acceptedCoins) }} accepted</span>
                    </div>
                    <div class="relative mb-4">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/35" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
                        <input type="search" placeholder="Search coin…" x-model="search" class="w-full rounded-2xl border-0 bg-ink/[0.04] py-3 pl-10 pr-4 text-sm font-medium ring-1 ring-black/5 transition placeholder:text-ink/35 focus:bg-white focus:ring-2 focus:ring-brand/40 focus:outline-none">
                    </div>
                    <div class="cp-scroll max-h-[26rem] overflow-y-auto pr-1">
                        @include('coinpayment::livewire.partials.coin-list')
                    </div>
                </div>
            </section>
        </div>

        {{-- ───────────────── Confirm payment modal ───────────────── --}}
        <div class="fixed inset-0 z-[60] flex items-end justify-center p-0 sm:items-center sm:p-4" x-show="showConfirm" x-cloak>
            <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" x-show="showConfirm" x-transition.opacity x-on:click="showConfirm = false"></div>
            <div class="relative w-full max-w-sm overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                 x-show="showConfirm"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-3 grid h-16 w-16 place-items-center rounded-2xl bg-brand/10 ring-1 ring-brand/15">
                        @if (!empty($defaultCoin['icon']))
                            <img src="{{ $defaultCoin['icon'] }}" alt="" class="h-9 w-9 object-contain animate-floaty" onerror="this.style.display='none'">
                        @else
                            <svg class="h-8 w-8 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        @endif
                    </div>
                    <h2 class="font-display text-xl font-bold">Confirm payment</h2>
                    <p class="mt-1 text-sm text-ink/55">You're paying with <span class="font-semibold text-ink/80">{{ $defaultCoin['name'] ?? $defaultCoin['iso'] ?? '' }}</span></p>
                </div>

                <div class="mx-6 my-5 rounded-2xl bg-ink/[0.03] p-4 text-center ring-1 ring-black/5">
                    <p class="text-xs font-medium uppercase tracking-wide text-ink/45">You will send</p>
                    <p class="mt-1 font-mono text-2xl font-bold text-brand">{{ $defaultCoin['amount'] ?? '—' }} {{ $defaultCoin['iso'] ?? '' }}</p>
                    <p class="mt-0.5 text-xs text-ink/45">≈ {{ $fmt($data['amountTotal'] ?? 0) }} {{ $defaultCurrency }}</p>
                </div>

                <div class="flex flex-col gap-2 px-5 pb-5 sm:flex-row">
                    <button type="button"
                            class="order-2 flex-1 rounded-2xl bg-ink/5 px-5 py-3 text-sm font-semibold text-ink/70 transition hover:bg-ink/10 disabled:opacity-50 sm:order-1"
                            x-on:click="showConfirm = false"
                            wire:loading.attr="disabled" wire:target="createTransaction">
                        Cancel
                    </button>
                    <button type="button"
                            class="order-1 flex-1 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/30 transition hover:bg-red-700 active:scale-[.99] disabled:opacity-60 sm:order-2"
                            wire:click="createTransaction"
                            wire:loading.attr="disabled" wire:target="createTransaction">
                        <span wire:loading.remove wire:target="createTransaction" class="inline-flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            Yes, pay now
                        </span>
                        <span wire:loading wire:target="createTransaction" class="inline-flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"/></svg>
                            Processing…
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ───────────────── Mobile coin modal ───────────────── --}}
        <div class="fixed inset-0 z-50 lg:hidden" x-show="showModal" x-cloak>
            <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" x-show="showModal" x-transition.opacity x-on:click="showModal = false"></div>
            <div class="absolute inset-x-0 bottom-0 max-h-[85vh] rounded-t-3xl bg-white p-5 shadow-2xl"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
                <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-ink/15"></div>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold">Select a coin</h2>
                    <button type="button" class="grid h-8 w-8 place-items-center rounded-full bg-ink/5 text-ink/50" x-on:click="showModal = false">✕</button>
                </div>
                <div class="relative mb-3">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/35" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
                    <input type="search" placeholder="Search coin…" x-model="search" class="w-full rounded-2xl border-0 bg-ink/[0.04] py-3 pl-10 pr-4 text-sm font-medium ring-1 ring-black/5 focus:bg-white focus:ring-2 focus:ring-brand/40 focus:outline-none">
                </div>
                <div class="cp-scroll max-h-[55vh] overflow-y-auto pr-1">
                    @include('coinpayment::livewire.partials.coin-list')
                </div>
            </div>
        </div>

        {{-- ───────────────── Result modal ───────────────── --}}
        @if ($transaction)
            @php
                $txStatus  = (int) ($transaction['status'] ?? 0);
                $isComplete = $txStatus >= 100;
                $isPaid     = $txStatus >= 1; // funds received / confirming
                $rcv = (float) ($transaction['receivedf'] ?? 0);
                $amt = (float) ($transaction['amountf'] ?? 0);
                $isPartial = ! $isPaid && $rcv > 0 && $amt > 0 && $rcv < $amt;
                $remaining = max($amt - $rcv, 0);
                $cf = fn ($v) => rtrim(rtrim(number_format((float) $v, 8, '.', ''), '0'), '.');
            @endphp
            <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4" x-show="showResult" x-cloak
                 @if (! $isComplete && $txStatus >= 0) wire:poll.{{ config('coinpayment.poll_interval', '5s') }}="pollStatus" @endif>
                <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" x-show="showResult" x-transition.opacity></div>
                <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                     x-show="showResult"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                    {{-- header --}}
                    <div class="border-b border-black/5 px-5 py-4 text-center {{ $isComplete ? 'bg-gradient-to-br from-emerald-500/10 to-transparent' : 'bg-gradient-to-br from-brand/10 to-transparent' }}">
                        @if ($isComplete)
                            <div class="mx-auto mb-2 grid h-11 w-11 place-items-center rounded-2xl bg-emerald-500/15 text-emerald-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </div>
                            <h2 class="font-display text-lg font-bold">Payment complete</h2>
                        @else
                            <div class="mx-auto mb-2 grid h-11 w-11 place-items-center rounded-2xl bg-brand/15 text-brand">
                                <svg class="h-6 w-6 {{ $isPaid ? 'animate-spin' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    @if ($isPaid)
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="3"/><path class="opacity-90" d="M4 12a8 8 0 0 1 8-8"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                                    @endif
                                </svg>
                            </div>
                            <h2 class="font-display text-lg font-bold">{{ $isPaid ? 'Confirming payment…' : ($isPartial ? 'Almost there — send the rest' : 'Complete your payment') }}</h2>
                        @endif
                        <p class="mt-0.5 text-xs text-ink/55">{{ $transaction['status_text'] }}</p>
                    </div>

                    <div class="space-y-3 px-5 py-4">
                        {{-- amount --}}
                        <div class="rounded-2xl p-3 text-center ring-1 {{ $isComplete ? 'bg-emerald-500/[0.06] ring-emerald-500/15' : ($isPartial ? 'bg-orange-500/[0.07] ring-orange-500/20' : 'bg-ink/[0.03] ring-black/5') }}">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-ink/45">{{ $isComplete ? 'Amount paid' : ($isPartial ? 'Remaining to send' : 'Send exactly') }}</p>
                            <p class="font-mono text-xl font-bold {{ $isComplete ? 'text-emerald-600' : ($isPartial ? 'text-orange-600' : 'text-brand') }}">{{ $isPartial ? $cf($remaining) : $transaction['amountf'] }} {{ $transaction['coin'] }}</p>
                            @if ($isPartial)
                                <p class="text-[11px] text-ink/45">Received {{ $cf($rcv) }} of {{ $cf($amt) }} {{ $transaction['coin'] }} · send the rest to the same address</p>
                            @elseif (! $isPaid)
                                <p class="text-[11px] text-ink/45">{{ $transaction['confirms_needed'] }} confirmation(s) needed</p>
                            @endif
                        </div>

                        {{-- pay instructions: only while still awaiting funds --}}
                        @unless ($isPaid)
                            @php $expiresAt = (int) ($transaction['time_expires'] ?? 0); @endphp
                            @if ($expiresAt > 0)
                                <div class="flex items-center justify-between rounded-2xl bg-ink/[0.03] px-4 py-2.5 text-sm ring-1 ring-black/5"
                                     x-data="{
                                        left: 0,
                                        text: '',
                                        tick() {
                                            this.left = Math.max(0, {{ $expiresAt }} - Math.floor(Date.now() / 1000));
                                            if (this.left <= 0) { this.text = 'Expired'; return; }
                                            const h = Math.floor(this.left / 3600), m = Math.floor((this.left % 3600) / 60), s = this.left % 60;
                                            this.text = (h > 0 ? h + 'h ' : '') + m + 'm ' + String(s).padStart(2, '0') + 's';
                                        }
                                     }"
                                     x-init="tick(); setInterval(() => tick(), 1000)">
                                    <span class="text-ink/55">Time left to pay</span>
                                    <span class="font-mono font-semibold" :class="left > 0 && left < 300 ? 'text-danger' : 'text-ink'" x-text="text"></span>
                                </div>
                            @endif

                            @if (!empty($transaction['qrcode_url']))
                                <div class="flex justify-center">
                                    <img src="{{ $transaction['qrcode_url'] }}" alt="Payment QR code" class="h-32 w-32 rounded-xl bg-white p-1.5 ring-1 ring-black/5">
                                </div>
                            @endif

                            <div x-data="{ copied: false }">
                                <p class="mb-1 text-[11px] font-medium text-ink/45">Send to address</p>
                                <div class="flex items-center gap-2 rounded-xl bg-ink/[0.04] p-1.5 ring-1 ring-black/5">
                                    <code class="min-w-0 flex-1 break-all px-1.5 font-mono text-[11px] text-ink/80">{{ $transaction['address'] }}</code>
                                    <button type="button" class="shrink-0 rounded-lg bg-white px-2.5 py-1.5 text-[11px] font-semibold text-brand shadow-sm ring-1 ring-black/5 transition active:scale-95"
                                            x-on:click="navigator.clipboard.writeText(@js($transaction['address'])); copied = true; setTimeout(() => copied = false, 1500)">
                                        <span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied ✓</span>
                                    </button>
                                </div>
                                <p class="mt-1 text-[11px] text-danger/80">Do not send funds if the address has expired.</p>
                            </div>
                        @endunless

                        {{-- meta --}}
                        <dl class="space-y-1 text-xs">
                            <div class="flex justify-between gap-3"><dt class="text-ink/45">Total to send</dt><dd class="font-mono">{{ $transaction['amountf'] }} {{ $transaction['coin'] }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-ink/45">Received so far</dt><dd class="font-mono">{{ $transaction['receivedf'] }} {{ $transaction['coin'] }}{{ ! $isComplete && (int) $transaction['recv_confirms'] === 0 ? ' · unconfirmed' : '' }}</dd></div>
                            @unless ($isComplete)
                                <div class="flex justify-between gap-3"><dt class="text-ink/45">Balance remaining</dt><dd class="font-mono {{ $remaining > 0 ? 'text-brand' : '' }}">{{ $cf(max($amt - $rcv, 0)) }} {{ $transaction['coin'] }}</dd></div>
                                <div class="flex justify-between gap-3"><dt class="text-ink/45">Confirms needed</dt><dd class="font-mono">{{ $transaction['confirms_needed'] }}</dd></div>
                            @endunless
                            <div class="flex justify-between gap-3"><dt class="text-ink/45">Payment ID</dt><dd class="truncate font-mono">{{ $transaction['txn_id'] }}</dd></div>
                        </dl>
                    </div>

                    <div class="flex flex-col gap-2 border-t border-black/5 p-4 sm:flex-row">
                        <a href="{{ $transaction['status_url'] }}" target="_blank" rel="noopener" class="flex-1 rounded-xl bg-ink/5 px-4 py-2.5 text-center text-sm font-semibold text-ink/70 transition hover:bg-ink/10">Status page</a>
                        <a href="{{ $data['redirect_url'] ?? '#' }}" class="flex-1 rounded-xl px-4 py-2.5 text-center text-sm font-bold text-white shadow-lg transition {{ $isComplete ? 'bg-emerald-600 shadow-emerald-600/30 hover:bg-emerald-700' : 'bg-brand shadow-brand/30 hover:bg-brand-dark' }}">Done</a>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
