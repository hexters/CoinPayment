@php
    $badge = function ($t) {
        $status   = (int) $t->status;
        $received = (float) $t->receivedf;
        $amount   = (float) $t->amountf;

        return match (true) {
            $status >= 100 => ['Complete', 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20'],
            $status >= 1   => ['Confirming', 'bg-blue-500/10 text-blue-700 ring-blue-500/20'],
            $status < 0    => ['Cancelled', 'bg-danger/10 text-danger ring-danger/20'],
            $received > 0 && $amount > 0 && $received < $amount => ['Partial', 'bg-orange-500/10 text-orange-700 ring-orange-500/20'],
            default        => ['Waiting', 'bg-amber-500/10 text-amber-700 ring-amber-500/20'],
        };
    };
    $cf = fn ($v) => rtrim(rtrim(number_format((float) $v, 8, '.', ''), '0'), '.');
    $inputCls = 'rounded-xl border-0 bg-white py-2.5 text-sm font-medium shadow-sm ring-1 ring-black/5 focus:ring-2 focus:ring-brand/40 focus:outline-none';
@endphp

<div class="font-sans">
    {{-- header --}}
    <div class="mb-4 flex items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight">Transactions</h1>
            <p class="text-sm text-ink/50">{{ $transactions->total() }} record(s)</p>
        </div>
    </div>

    {{-- toolbar --}}
    <div class="mb-4 rounded-3xl bg-white/85 p-3 shadow-card ring-1 ring-black/5 backdrop-blur">
        <div class="flex flex-col gap-2.5 lg:flex-row lg:flex-wrap lg:items-center">
            {{-- search --}}
            <div class="relative min-w-0 flex-1 lg:max-w-xs">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/35" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
                <input type="search" placeholder="Search order / txn / email…" wire:model.live.debounce.400ms="search" class="{{ $inputCls }} w-full pl-10 pr-3">
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4 lg:flex lg:items-center">
                {{-- status --}}
                <select wire:model.live="status" class="{{ $inputCls }} px-3">
                    <option value="">All status</option>
                    <option value="waiting">Waiting</option>
                    <option value="confirming">Confirming</option>
                    <option value="complete">Complete</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                {{-- coin --}}
                <select wire:model.live="coin" class="{{ $inputCls }} px-3">
                    <option value="">All coins</option>
                    @foreach ($coins as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>

                {{-- date range --}}
                <input type="date" wire:model.live="from" class="{{ $inputCls }} px-3" title="From date">
                <input type="date" wire:model.live="to" class="{{ $inputCls }} px-3" title="To date">
            </div>

            <div class="flex items-center gap-2.5 lg:ml-auto">
                {{-- per page --}}
                <select wire:model.live="perPage" class="{{ $inputCls }} px-3">
                    @foreach ([10, 15, 25, 50, 100] as $n)
                        <option value="{{ $n }}">{{ $n }} / page</option>
                    @endforeach
                </select>

                @if ($activeFilters)
                    <button type="button" wire:click="resetFilters" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-ink/5 px-3 py-2.5 text-sm font-semibold text-ink/60 transition hover:bg-ink/10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if ($flash)
        <div class="mb-4 flex items-center gap-2 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-500/15">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ $flash }}
        </div>
    @endif

    {{-- table --}}
    @php
        $head = function ($field, $label) use ($sort, $dir) {
            $active = $sort === $field;
            $icon = $active ? ($dir === 'asc' ? 'M8 14l4-4 4 4' : 'M8 10l4 4 4-4') : 'M8 9l4-4 4 4M8 15l4 4 4-4';
            return [$active, $icon];
        };
    @endphp
    <div class="overflow-hidden rounded-3xl bg-white/85 shadow-card ring-1 ring-black/5 backdrop-blur">
        <div class="cp-scroll overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead>
                    <tr class="border-b border-black/5 text-left text-[11px] font-semibold uppercase tracking-wide text-ink/40">
                        @foreach (['order_id' => 'Order', 'amountf' => 'Amount', 'status' => 'Status', 'created_at' => 'Created'] as $field => $label)
                            @php [$active, $icon] = $head($field, $label); @endphp
                            <th class="px-5 py-3 {{ $field === 'amountf' ? 'text-right' : '' }}">
                                <button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 transition hover:text-ink {{ $active ? 'text-brand' : '' }}">
                                    {{ $label }}
                                    <svg class="h-3.5 w-3.5 {{ $active ? '' : 'opacity-40' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                </button>
                            </th>
                            @if ($field === 'order_id')
                                <th class="px-5 py-3">Buyer</th>
                            @endif
                        @endforeach
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @forelse ($transactions as $trx)
                        @php [$label, $cls] = $badge($trx); @endphp
                        <tr class="cursor-pointer transition hover:bg-brand/[0.03]" wire:key="trx-{{ $trx->id }}" wire:click="show({{ $trx->id }})">
                            <td class="px-5 py-3">
                                <p class="font-semibold">{{ $trx->order_id }}</p>
                                <p class="font-mono text-[11px] text-ink/40">{{ \Illuminate\Support\Str::limit($trx->txn_id, 18) }}</p>
                            </td>
                            <td class="px-5 py-3 text-ink/60">{{ $trx->buyer_email ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-mono">{{ $trx->amountf ?: '—' }} {{ $trx->coin }}</td>
                            <td class="px-5 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $cls }}">{{ $label }}</span></td>
                            <td class="px-5 py-3 text-ink/50">{{ optional($trx->created_at)->format(config('coinpayment.font.date_format', 'd/m/y H:i')) }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-ink/30">›</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-ink/45">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>

    {{-- Detail modal --}}
    @if ($selected)
        @php
            [$dLabel, $dCls] = $badge($selected);
            $dRemaining = (float) $selected->amountf - (float) $selected->receivedf;
            $dIsPartial = (int) $selected->status < 1 && (float) $selected->receivedf > 0 && $dRemaining > 0;
        @endphp
        @php $dComplete = (int) $selected->status >= 100; @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
            <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" wire:click="close"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg animate-rise overflow-y-auto rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl">
                {{-- header --}}
                <div class="relative overflow-hidden border-b border-black/5 bg-gradient-to-br from-brand/10 to-transparent px-5 py-5">
                    <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-brand/10 blur-2xl"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand to-brand-dark text-white shadow-lg shadow-brand/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6M9 12h6M9 16h3M6 3h12a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 0 1 1-1Z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/40">Order</p>
                                <h2 class="truncate font-display text-lg font-bold leading-tight">{{ $selected->order_id }}</h2>
                            </div>
                        </div>
                        <button type="button" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/70 text-ink/50 ring-1 ring-black/5 backdrop-blur transition hover:bg-white" wire:click="close">✕</button>
                    </div>
                    <span class="relative mt-3 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $dCls }}">{{ $dLabel }} · {{ $selected->status_text }}</span>
                </div>

                <div class="space-y-4 px-5 py-5">
                    {{-- amount hero --}}
                    <div class="rounded-2xl p-4 text-center ring-1 {{ $dComplete ? 'bg-emerald-500/[0.06] ring-emerald-500/15' : 'bg-ink/[0.03] ring-black/5' }}">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/45">Amount</p>
                        <p class="font-mono text-2xl font-bold {{ $dComplete ? 'text-emerald-600' : 'text-ink' }}">{{ $selected->amountf }} {{ $selected->coin }}</p>
                        <p class="text-xs text-ink/45">{{ $selected->amount_total_fiat }} {{ $selected->currency_code }}</p>
                    </div>

                    @if ($dIsPartial)
                        <div class="flex items-start gap-2 rounded-2xl bg-orange-500/10 px-4 py-3 text-sm text-orange-800 ring-1 ring-orange-500/20">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                            <span>Partial payment — short by <strong class="font-mono">{{ $cf($dRemaining) }} {{ $selected->coin }}</strong>. Ask the buyer to send the remainder to the same address, or refund.</span>
                        </div>
                    @endif

                    {{-- details --}}
                    <dl class="divide-y divide-black/[0.05] rounded-2xl bg-ink/[0.02] px-4 text-sm ring-1 ring-black/5">
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Txn ID</dt><dd class="min-w-0 truncate font-mono text-xs">{{ $selected->txn_id }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Buyer</dt><dd class="min-w-0 truncate">{{ $selected->buyer_name ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">E-mail</dt><dd class="min-w-0 truncate">{{ $selected->buyer_email ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Received</dt><dd class="font-mono">{{ $selected->receivedf }} {{ $selected->coin }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Address</dt><dd class="min-w-0 truncate font-mono text-xs">{{ $selected->address }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Created</dt><dd>{{ optional($selected->created_at)->format(config('coinpayment.font.date_format', 'd/m/y H:i')) }}</dd></div>
                    </dl>

                    @if ($selected->items->isNotEmpty())
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-ink/40">Items</p>
                            <div class="divide-y divide-black/[0.05] overflow-hidden rounded-2xl bg-ink/[0.02] ring-1 ring-black/5">
                                @foreach ($selected->items as $item)
                                    <div class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm">
                                        <span class="min-w-0 truncate">{{ $item->description }} <span class="text-ink/40">×{{ $item->qty }}</span></span>
                                        <span class="shrink-0 font-mono">{{ number_format((float) $item->subtotal, 2) }} {{ $item->currency_code }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-2 border-t border-black/5 p-4 sm:flex-row">
                    @if ($selected->txn_id)
                        <button type="button" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-ink/5 px-4 py-2.5 text-sm font-semibold text-ink/70 transition hover:bg-ink/10 disabled:opacity-60"
                                wire:click="refreshStatus('{{ $selected->txn_id }}')" wire:loading.attr="disabled" wire:target="refreshStatus">
                            <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="refreshStatus" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5.5 9a7 7 0 0 1 12-2.5L20 9M18.5 15a7 7 0 0 1-12 2.5L4 15"/></svg>
                            <span wire:loading.remove wire:target="refreshStatus">Refresh status</span>
                            <span wire:loading wire:target="refreshStatus">Refreshing…</span>
                        </button>
                    @endif
                    @if ($selected->status_url)
                        <a href="{{ $selected->status_url }}" target="_blank" rel="noopener" class="flex-1 rounded-xl bg-brand px-4 py-2.5 text-center text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">Open on CoinPayments ↗</a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
