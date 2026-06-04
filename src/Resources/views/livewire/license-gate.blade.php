<div>
    @if ($locked)
        <div class="fixed inset-0 z-[100] flex items-end justify-center p-0 sm:items-center sm:p-4"
             x-data="{ open: true }"
             x-on:cp-show-license.window="open = true"
             x-show="open" x-cloak>
            <div class="absolute inset-0 bg-ink/60 backdrop-blur-md" x-on:click="open = false"></div>

            <div class="relative w-full max-w-md animate-rise overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                {{-- header --}}
                <div class="relative overflow-hidden bg-gradient-to-br from-brand to-brand-dark px-6 py-6 text-center text-white">
                    <div class="pointer-events-none absolute -right-10 -top-12 h-32 w-32 rounded-full bg-white/15 blur-2xl"></div>
                    <button type="button" class="absolute right-4 top-4 grid h-8 w-8 place-items-center rounded-full bg-white/15 text-white/80 ring-1 ring-white/20 transition hover:bg-white/25" x-on:click="open = false">✕</button>
                    <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20 backdrop-blur">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 10V7a4 4 0 0 0-8 0v3M6 10h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Zm6 5h.01"/></svg>
                    </div>
                    <h2 class="font-display text-xl font-bold">Activate your license</h2>
                    <p class="mx-auto mt-1 max-w-xs text-sm text-white/70">This feature is part of the licensed edition. Enter your serial number to unlock it on this server.</p>
                </div>

                {{-- body --}}
                <form wire:submit="activate" class="space-y-3 px-6 py-5">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-ink/55">Serial number</label>
                        <input type="text" wire:model="serial" placeholder="HEXCP-PRO-XXXX-XXXX" autocomplete="off" spellcheck="false"
                               class="w-full rounded-xl border-0 bg-ink/[0.04] px-3 py-3 text-center font-mono text-sm tracking-wider ring-1 ring-black/5 focus:bg-white focus:ring-2 focus:ring-brand/40 focus:outline-none">
                        @if ($error)
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-danger">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                                {{ $error }}
                            </p>
                        @endif
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark disabled:opacity-60"
                            wire:loading.attr="disabled" wire:target="activate">
                        <span wire:loading.remove wire:target="activate">Activate</span>
                        <span wire:loading wire:target="activate">Activating…</span>
                    </button>

                    <a href="{{ $purchaseUrl }}" target="_blank" rel="noopener"
                       class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-ink/5 px-4 py-3 text-sm font-semibold text-ink/70 transition hover:bg-ink/10">
                        Don't have a serial? Buy a license
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7m0 0H8m9 0v9"/></svg>
                    </a>
                </form>
            </div>
        </div>
    @endif
</div>
