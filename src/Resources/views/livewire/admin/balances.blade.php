<div class="font-sans"
     x-data="{ flash: null }"
     x-on:coin-flash.window="flash = $event.detail.message; setTimeout(() => flash = null, 4000)">

    {{-- header --}}
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight">Wallet</h1>
            <p class="text-sm text-ink/50">Your CoinPayments balances · live rates</p>
        </div>
        <button type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-ink/70 shadow-sm ring-1 ring-black/5 transition hover:bg-ink/[0.03] disabled:opacity-60"
                wire:click="loadBalances" wire:loading.attr="disabled" wire:target="loadBalances">
            <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="loadBalances" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5.5 9a7 7 0 0 1 12-2.5L20 9M18.5 15a7 7 0 0 1-12 2.5L4 15"/></svg>
            Refresh
        </button>
    </div>

    {{-- hero · total balance --}}
    <div class="relative mb-6 animate-rise overflow-hidden rounded-[28px] bg-gradient-to-br from-brand to-brand-dark p-6 text-white shadow-card sm:p-7">
        <div class="pointer-events-none absolute -right-12 -top-20 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/4 h-48 w-48 rounded-full bg-black/15 blur-2xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px;"></div>

        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/60">Total balance</p>
                <p class="mt-2 font-display text-4xl font-bold leading-none tracking-tight sm:text-5xl">
                    @if (! is_null($totalFiat))
                        {{ $totalFiat }}<span class="ml-2 text-lg font-semibold text-white/65">{{ $defaultCurrency }}</span>
                    @else
                        <span class="text-white/70">—</span>
                    @endif
                </p>
                <p class="mt-3 inline-flex items-center gap-1.5 text-sm text-white/65">
                    <span class="grid h-5 w-5 place-items-center rounded-full bg-white/15">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 7-7"/></svg>
                    </span>
                    {{ count($balances) }} {{ \Illuminate\Support\Str::plural('asset', count($balances)) }} · estimated at live rates
                </p>
            </div>
            <span class="hidden h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20 backdrop-blur sm:grid">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.5A2.5 2.5 0 0 1 5.5 6H18a2 2 0 0 1 2 2v.5M3 8.5V17a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3M3 8.5h15.5a2 2 0 0 1 2 2V14m0 0h-4a2 2 0 1 0 0 4h4"/></svg>
            </span>
        </div>
    </div>

    {{-- flash --}}
    <div x-show="flash" x-cloak x-transition class="mb-4 flex items-center gap-2 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-500/15">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
        <span x-text="flash"></span>
    </div>

    @if ($error)
        <div class="mb-4 rounded-2xl bg-danger/8 px-4 py-3 text-sm text-danger ring-1 ring-danger/15">{{ $error }}</div>
    @endif

    {{-- loading skeleton --}}
    <div wire:loading.flex wire:target="loadBalances" class="hidden items-center gap-2 text-sm text-ink/45">
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"/></svg>
        Loading balances…
    </div>

    {{-- balances grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" wire:loading.remove wire:target="loadBalances">
        @forelse ($balances as $row)
            @php $online = strtolower((string) $row['coin_status']) === 'online'; @endphp
            <div class="group relative animate-rise overflow-hidden rounded-3xl bg-white p-5 shadow-card ring-1 ring-black/5 transition duration-200 hover:-translate-y-1 hover:shadow-[0_28px_56px_-20px_rgba(20,30,60,.28)]" wire:key="bal-{{ $row['coin'] }}">
                {{-- accent strip --}}
                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand to-brand-dark"></div>
                {{-- faint watermark --}}
                <img src="{{ $row['icon'] }}" alt="" class="pointer-events-none absolute -right-3 -top-2 h-24 w-24 object-contain opacity-[0.05] transition duration-300 group-hover:scale-110" onerror="this.remove()">

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand/15 to-brand/5 ring-1 ring-brand/10">
                            <img src="{{ $row['icon'] }}" alt="{{ $row['coin'] }}" class="h-6 w-6 object-contain" onerror="this.style.visibility='hidden'">
                        </span>
                        <div>
                            <p class="font-display font-bold leading-tight">{{ $row['coin'] }}</p>
                            <p class="inline-flex items-center gap-1 text-[11px] font-medium text-ink/45">
                                <span class="h-1.5 w-1.5 rounded-full {{ $online ? 'bg-emerald-500' : 'bg-ink/25' }}"></span>
                                {{ $row['coin_status'] ?: 'wallet' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative mt-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/40">Balance</p>
                    <p class="font-mono text-2xl font-bold leading-tight tracking-tight">{{ $row['balancef'] }}</p>
                    @if (! is_null($row['fiat']))
                        <p class="text-sm text-ink/50">≈ {{ $row['fiat'] }} {{ $defaultCurrency }}</p>
                    @elseif ($row['testnet'])
                        <p class="mt-0.5 inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-500/20">Testnet · no fiat value</p>
                    @endif
                </div>

                @if ($row['address'])
                    <div class="relative mt-4 rounded-2xl bg-brand/[0.06] p-2.5 ring-1 ring-brand/10" x-data="{ copied: false }">
                        <p class="mb-1 px-1 text-[11px] font-semibold uppercase tracking-wide text-brand/70">Deposit address</p>
                        <div class="flex items-center gap-2">
                            <code class="min-w-0 flex-1 break-all px-1 font-mono text-[11px] text-ink/80">{{ $row['address'] }}</code>
                            <button type="button" class="shrink-0 rounded-lg bg-white px-2.5 py-1.5 text-[11px] font-semibold text-brand shadow-sm ring-1 ring-black/5 active:scale-95"
                                    x-on:click="navigator.clipboard.writeText(@js($row['address'])); copied=true; setTimeout(()=>copied=false,1500)">
                                <span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied ✓</span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="relative mt-5 flex gap-2">
                    @if ($locked)
                        <button type="button" class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-ink/5 px-3 py-2.5 text-sm font-semibold text-ink/40"
                                x-data x-on:click="$dispatch('cp-show-license')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 10V7a4 4 0 0 0-8 0v3M6 10h12v10H6z"/></svg>
                            Locked — activate license
                        </button>
                    @else
                        <button type="button" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-ink/5 px-3 py-2.5 text-sm font-semibold text-ink/70 transition hover:bg-ink/10 disabled:opacity-50"
                                wire:click="topUp('{{ $row['coin'] }}')" wire:loading.attr="disabled" wire:target="topUp">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                            Top up
                        </button>
                        <button type="button" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-brand px-3 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark"
                                wire:click="openWithdraw('{{ $row['coin'] }}')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-7 7 7 7 7-7"/></svg>
                            Withdraw
                        </button>
                    @endif
                </div>
            </div>
        @empty
            @unless ($error)
                <div class="col-span-full rounded-3xl bg-white/70 p-12 text-center ring-1 ring-black/5">
                    <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-ink/[0.04] text-ink/30">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.5A2.5 2.5 0 0 1 5.5 6H18a2 2 0 0 1 2 2v.5M3 8.5V17a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3M3 8.5h17.5"/></svg>
                    </div>
                    <p class="text-sm text-ink/45">No balances to display yet.</p>
                </div>
            @endunless
        @endforelse
    </div>

    {{-- Withdrawal modal --}}
    <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4" x-show="$wire.showWithdraw" x-cloak>
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" x-show="$wire.showWithdraw" x-transition.opacity x-on:click="$wire.set('showWithdraw', false)"></div>
        <div class="relative w-full max-w-md overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
             x-show="$wire.showWithdraw"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <div class="relative overflow-hidden border-b border-black/5 bg-gradient-to-br from-brand/10 to-transparent px-5 py-5">
                <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-brand/10 blur-2xl"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand to-brand-dark text-white shadow-lg shadow-brand/30">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7m0 0H8m9 0v9"/></svg>
                        </span>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/40">Withdraw</p>
                            <h2 class="font-display text-lg font-bold leading-tight">{{ $wCoin }}</h2>
                        </div>
                    </div>
                    <button type="button" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/70 text-ink/50 ring-1 ring-black/5 backdrop-blur transition hover:bg-white" wire:click="$set('showWithdraw', false)">✕</button>
                </div>
            </div>
            <form wire:submit="withdraw" class="space-y-3 px-5 py-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-ink/55">Amount ({{ $wCoin }})</label>
                    <input type="text" wire:model="wAmount" placeholder="0.00000000"
                           class="w-full rounded-xl border-0 bg-ink/[0.04] px-3 py-2.5 font-mono text-sm ring-1 ring-black/5 focus:bg-white focus:ring-2 focus:ring-brand/40 focus:outline-none">
                    @error('wAmount') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-ink/55">Destination address</label>
                    <input type="text" wire:model="wAddress"
                           class="w-full rounded-xl border-0 bg-ink/[0.04] px-3 py-2.5 font-mono text-sm ring-1 ring-black/5 focus:bg-white focus:ring-2 focus:ring-brand/40 focus:outline-none">
                    @error('wAddress') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-ink/55">Note <span class="text-ink/35">(optional)</span></label>
                    <input type="text" wire:model="wNote" maxlength="60"
                           class="w-full rounded-xl border-0 bg-ink/[0.04] px-3 py-2.5 text-sm ring-1 ring-black/5 focus:bg-white focus:ring-2 focus:ring-brand/40 focus:outline-none">
                    @error('wNote') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2 pt-1 sm:flex-row">
                    <button type="button" class="order-2 flex-1 rounded-xl bg-ink/5 px-4 py-2.5 text-sm font-semibold text-ink/70 hover:bg-ink/10 sm:order-1" wire:click="$set('showWithdraw', false)">Cancel</button>
                    <button type="submit" class="order-1 flex-1 rounded-xl bg-brand px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/30 hover:bg-brand-dark disabled:opacity-60 sm:order-2"
                            wire:loading.attr="disabled" wire:target="withdraw">
                        <span wire:loading.remove wire:target="withdraw">Send withdrawal</span>
                        <span wire:loading wire:target="withdraw">Sending…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
