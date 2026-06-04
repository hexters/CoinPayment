<div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
    @forelse ($acceptedCoins as $coin)
        @php $isActive = ($defaultCoin['iso'] ?? null) === $coin['iso']; @endphp
        <button
            type="button"
            x-show="match(@js($coin['name']))"
            wire:click="setBilling(@js($coin['iso']))"
            wire:key="coin-{{ $coin['iso'] }}"
            x-on:click="showModal = false"
            class="group flex items-center gap-3 rounded-2xl border px-3 py-3 text-left transition active:scale-[.98]
                {{ $isActive
                    ? 'border-brand bg-brand/[0.07] ring-1 ring-brand/30'
                    : 'border-black/5 bg-white hover:border-brand/30 hover:bg-brand/[0.03]' }}"
        >
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-ink/[0.04] ring-1 ring-black/5">
                <img src="{{ $coin['icon'] }}" alt="{{ $coin['name'] }}" class="h-6 w-6 object-contain" loading="lazy" onerror="this.style.visibility='hidden'">
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold">{{ $coin['name'] }}</span>
                <span class="block truncate font-mono text-xs text-ink/50">{{ $coin['amount'] }} {{ $coin['iso'] }}</span>
            </span>
            <span class="shrink-0 {{ $isActive ? 'text-brand' : 'text-transparent group-hover:text-ink/20' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            </span>
        </button>
    @empty
        <p class="col-span-full py-8 text-center text-sm text-ink/45">No accepted coins available.</p>
    @endforelse
</div>
