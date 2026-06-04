@php
    $badge = fn ($status) => match ((int) $status) {
        2       => ['Complete', 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20'],
        1       => ['Pending', 'bg-blue-500/10 text-blue-700 ring-blue-500/20'],
        -1      => ['Cancelled', 'bg-danger/10 text-danger ring-danger/20'],
        default => ['Awaiting confirmation', 'bg-amber-500/10 text-amber-700 ring-amber-500/20'],
    };
    $inputCls = 'rounded-xl border-0 bg-white py-2.5 text-sm font-medium shadow-sm ring-1 ring-black/5 focus:ring-2 focus:ring-brand/40 focus:outline-none';
@endphp

<div class="font-sans">
    {{-- header --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight">Withdrawals</h1>
            <p class="text-sm text-ink/50">Create new withdrawals from the <a href="{{ route('coinpayment.admin.balances') }}" class="font-semibold text-brand hover:underline" wire:navigate>Balances</a> page.</p>
        </div>
        <button type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-ink/70 shadow-sm ring-1 ring-black/5 transition hover:bg-ink/[0.03] disabled:opacity-60"
                wire:click="fetch" wire:loading.attr="disabled" wire:target="fetch">
            <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="fetch" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5.5 9a7 7 0 0 1 12-2.5L20 9M18.5 15a7 7 0 0 1-12 2.5L4 15"/></svg>
            Refresh
        </button>
    </div>

    {{-- email confirmation / action notice --}}
    @if ($notice)
        <div x-data="{ show: true }" x-show="show" class="mb-4 flex items-start gap-3 rounded-2xl bg-amber-500/10 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-500/20">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/></svg>
            <span class="flex-1">{{ $notice }}</span>
            <button type="button" class="shrink-0 text-amber-700/60 hover:text-amber-900" x-on:click="show = false">✕</button>
        </div>
    @endif

    @if ($error)
        <div class="mb-4 rounded-2xl bg-danger/8 px-4 py-3 text-sm text-danger ring-1 ring-danger/15">{{ $error }}</div>
    @endif

    {{-- toolbar --}}
    <div class="mb-4 rounded-3xl bg-white/85 p-3 shadow-card ring-1 ring-black/5 backdrop-blur">
        <div class="flex flex-col gap-2.5 lg:flex-row lg:flex-wrap lg:items-center">
            <div class="relative min-w-0 flex-1 lg:max-w-xs">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/35" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
                <input type="search" placeholder="Search id / note / address / tx…" wire:model.live.debounce.400ms="search" class="{{ $inputCls }} w-full pl-10 pr-3">
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:flex sm:items-center">
                <select wire:model.live="status" class="{{ $inputCls }} px-3">
                    <option value="">All status</option>
                    <option value="0">Awaiting confirmation</option>
                    <option value="1">Pending</option>
                    <option value="2">Complete</option>
                    <option value="-1">Cancelled</option>
                </select>
                <select wire:model.live="coin" class="{{ $inputCls }} px-3">
                    <option value="">All coins</option>
                    @foreach ($coins as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2.5 lg:ml-auto">
                <select wire:model.live="perPage" class="{{ $inputCls }} px-3">
                    @foreach ([10, 15, 25, 50] as $n)
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

    {{-- table --}}
    @php
        $head = function ($field) use ($sort, $dir) {
            $active = $sort === $field;
            return [$active, $active ? ($dir === 'asc' ? 'M8 14l4-4 4 4' : 'M8 10l4 4 4-4') : 'M8 9l4-4 4 4M8 15l4 4 4-4'];
        };
    @endphp
    <div class="overflow-hidden rounded-3xl bg-white/85 shadow-card ring-1 ring-black/5 backdrop-blur">
        <div class="cp-scroll overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="border-b border-black/5 text-left text-[11px] font-semibold uppercase tracking-wide text-ink/40">
                        <th class="px-5 py-3">ID</th>
                        @foreach (['amountf' => ['Amount', 'text-right'], 'status' => ['Status', ''], 'time' => ['Created', 'text-right']] as $field => [$label, $align])
                            @php [$active, $icon] = $head($field); @endphp
                            <th class="px-5 py-3 {{ $align }}">
                                <button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 transition hover:text-ink {{ $active ? 'text-brand' : '' }}">
                                    {{ $label }}
                                    <svg class="h-3.5 w-3.5 {{ $active ? '' : 'opacity-40' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                </button>
                            </th>
                            @if ($field === 'status')
                                <th class="px-5 py-3">Note</th>
                            @endif
                        @endforeach
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]" wire:loading.class="opacity-40" wire:target="fetch,cancel,refreshOne">
                    @forelse ($rows as $row)
                        @php [$label, $cls] = $badge($row['status']); @endphp
                        <tr wire:key="wd-{{ $row['id'] }}" wire:click="show('{{ $row['id'] }}')" class="cursor-pointer transition hover:bg-brand/[0.03]">
                            <td class="px-5 py-3">
                                <p class="font-mono text-xs font-semibold">{{ $row['id'] }}</p>
                                @if ($row['send_txid'])
                                    <p class="font-mono text-[11px] text-ink/40">tx: {{ \Illuminate\Support\Str::limit($row['send_txid'], 16) }}</p>
                                @elseif ($row['send_address'])
                                    <p class="truncate font-mono text-[11px] text-ink/40">{{ $row['send_address'] }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-mono">{{ $row['amountf'] }} {{ $row['coin'] }}</td>
                            <td class="px-5 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $cls }}">{{ $label }}</span></td>
                            <td class="px-5 py-3 text-ink/60">{{ $row['note'] ?: '—' }}</td>
                            <td class="px-5 py-3 text-right text-ink/50">{{ $row['time'] }}</td>
                            <td class="px-5 py-3 text-right">
                                @if ($row['status'] === 0)
                                    <button type="button"
                                            class="rounded-lg bg-danger/10 px-3 py-1.5 text-xs font-semibold text-danger transition hover:bg-danger/15 disabled:opacity-50"
                                            wire:click.stop="cancel('{{ $row['id'] }}')"
                                            wire:confirm="Cancel withdrawal {{ $row['id'] }}?"
                                            wire:loading.attr="disabled" wire:target="cancel">
                                        Cancel
                                    </button>
                                @else
                                    <span class="text-ink/30">›</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-ink/45">No withdrawals found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $rows->links() }}</div>

    <p class="mt-2 text-xs text-ink/40">Showing the most recent {{ $windowCount }} withdrawal(s) fetched from CoinPayments. Sorting &amp; filtering apply to this window.</p>

    {{-- Detail modal --}}
    @if ($selected)
        @php [$sLabel, $sCls] = $badge($selected['status']); @endphp
        @php $sComplete = $selected['status'] === 2; @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
            <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" wire:click="close"></div>
            <div class="relative max-h-[92vh] w-full max-w-md animate-rise overflow-y-auto rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl">
                {{-- header --}}
                <div class="relative overflow-hidden border-b border-black/5 bg-gradient-to-br from-brand/10 to-transparent px-5 py-5">
                    <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-brand/10 blur-2xl"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand to-brand-dark text-white shadow-lg shadow-brand/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7m0 0H8m9 0v9"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/40">Withdrawal</p>
                                <h2 class="truncate font-display text-lg font-bold leading-tight">{{ $selected['id'] }}</h2>
                            </div>
                        </div>
                        <button type="button" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/70 text-ink/50 ring-1 ring-black/5 backdrop-blur transition hover:bg-white" wire:click="close">✕</button>
                    </div>
                    <span class="relative mt-3 inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $sCls }}">{{ $sLabel }}</span>
                </div>

                <div class="space-y-4 px-5 py-5">
                    {{-- amount hero --}}
                    <div class="rounded-2xl p-4 text-center ring-1 {{ $sComplete ? 'bg-emerald-500/[0.06] ring-emerald-500/15' : 'bg-ink/[0.03] ring-black/5' }}">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/45">Amount</p>
                        <p class="font-mono text-2xl font-bold {{ $sComplete ? 'text-emerald-600' : 'text-ink' }}">{{ $selected['amountf'] }} {{ $selected['coin'] }}</p>
                    </div>

                    @if ($selected['status'] === 0)
                        <div class="flex items-start gap-2 rounded-2xl bg-amber-500/10 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-500/20">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/></svg>
                            <span>Awaiting your e-mail confirmation. Open the CoinPayments e-mail and click the confirmation link, or cancel below.</span>
                        </div>
                    @endif

                    <dl class="divide-y divide-black/[0.05] rounded-2xl bg-ink/[0.02] px-4 text-sm ring-1 ring-black/5">
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Status</dt><dd>{{ $selected['status_text'] }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Note</dt><dd class="min-w-0 truncate">{{ $selected['note'] ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Created</dt><dd>{{ $selected['time'] }}</dd></div>
                        @if ($selected['send_address'])
                            <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">Sent to</dt><dd class="min-w-0 truncate font-mono text-xs">{{ $selected['send_address'] }}</dd></div>
                        @endif
                        @if ($selected['send_txid'])
                            <div class="flex justify-between gap-3 py-2.5"><dt class="text-ink/45">TX ID</dt><dd class="min-w-0 truncate font-mono text-xs">{{ $selected['send_txid'] }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="flex flex-col gap-2 border-t border-black/5 p-4 sm:flex-row">
                    <button type="button" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-ink/5 px-4 py-2.5 text-sm font-semibold text-ink/70 transition hover:bg-ink/10 disabled:opacity-60"
                            wire:click="refreshOne('{{ $selected['id'] }}')" wire:loading.attr="disabled" wire:target="refreshOne">
                        <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="refreshOne" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5.5 9a7 7 0 0 1 12-2.5L20 9M18.5 15a7 7 0 0 1-12 2.5L4 15"/></svg>
                        <span wire:loading.remove wire:target="refreshOne">Refresh status</span>
                        <span wire:loading wire:target="refreshOne">Refreshing…</span>
                    </button>
                    @if ($selected['status'] === 0)
                        <button type="button" class="flex-1 rounded-xl bg-danger px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-danger/30 transition hover:opacity-90 disabled:opacity-60"
                                wire:click="cancel('{{ $selected['id'] }}')" wire:confirm="Cancel withdrawal {{ $selected['id'] }}?"
                                wire:loading.attr="disabled" wire:target="cancel">
                            Cancel withdrawal
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
