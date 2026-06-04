<?php

namespace Hexters\CoinPayment\Livewire\Admin;

use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Licensing\License;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('coinpayment::layouts.admin')]
#[Title('CoinPayment · Withdrawals')]
class Withdrawals extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    /** Exact withdrawal status: '', '0', '1', '2', '-1'. */
    #[Url]
    public string $status = '';

    #[Url]
    public string $coin = '';

    #[Url]
    public string $sort = 'time';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public int $perPage = 15;

    public ?string $notice = null;

    public ?string $selectedId = null;

    public ?string $error = null;

    /** The window of withdrawals fetched from the API. @var array<int, array<string, mixed>> */
    public array $all = [];

    /** How many records to pull from the API (max 100 per call). */
    public int $fetchLimit = 100;

    protected array $sortable = ['time', 'amountf', 'status'];

    public function mount(): void
    {
        $this->notice = session('coinpayment_notice');
        $this->fetch();
    }

    /** Pull the latest window of withdrawals from CoinPayments. */
    public function fetch(): void
    {
        $this->error = null;

        $response = app(CoinPaymentHelper::class)->getWithdrawalHistory(['limit' => min($this->fetchLimit, 100)]);

        if (($response['error'] ?? null) !== 'ok') {
            $this->error = $response['error'] ?? 'Unable to load withdrawal history.';
            $this->all = [];

            return;
        }

        $rows = [];

        foreach ((array) ($response['result'] ?? []) as $key => $w) {
            if (! is_array($w)) {
                continue;
            }

            $rows[] = [
                'id'           => $w['id'] ?? (is_string($key) ? $key : null),
                'time_raw'     => (int) ($w['time_created'] ?? 0),
                'time'         => isset($w['time_created']) ? date('Y-m-d H:i', (int) $w['time_created']) : '—',
                'coin'         => $w['coin'] ?? '',
                'amountf'      => $w['amountf'] ?? '',
                'status'       => (int) ($w['status'] ?? 0),
                'status_text'  => $w['status_text'] ?? '',
                'note'         => $w['note'] ?? '',
                'send_address' => $w['send_address'] ?? '',
                'send_txid'    => $w['send_txid'] ?? '',
            ];
        }

        $this->all = $rows;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCoin(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortable, true)) {
            return;
        }

        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'coin']);
        $this->resetPage();
    }

    public function show(string $id): void
    {
        if (License::locked()) {
            $this->dispatch('cp-show-license');

            return;
        }

        $this->selectedId = $id;
    }

    public function close(): void
    {
        $this->selectedId = null;
    }

    public function cancel(string $id): void
    {
        if (License::locked()) {
            $this->dispatch('cp-show-license');

            return;
        }

        $response = app(CoinPaymentHelper::class)->cancelWithdrawal($id);

        $this->notice = ($response['error'] ?? null) === 'ok'
            ? "Withdrawal {$id} cancelled."
            : 'Cancel failed: ' . ($response['error'] ?? 'unknown error');

        $this->fetch();
    }

    /** Refresh a single withdrawal via get_withdrawal_info. */
    public function refreshOne(string $id): void
    {
        if (License::locked()) {
            $this->dispatch('cp-show-license');

            return;
        }

        $response = app(CoinPaymentHelper::class)->getWithdrawalInfo($id);

        if (($response['error'] ?? null) !== 'ok') {
            $this->notice = 'Refresh failed: ' . ($response['error'] ?? 'unknown error');

            return;
        }

        $info = $response['result'] ?? [];

        foreach ($this->all as $i => $row) {
            if ($row['id'] === $id) {
                $this->all[$i] = array_merge($row, [
                    'status'       => (int) ($info['status'] ?? $row['status']),
                    'status_text'  => $info['status_text'] ?? $row['status_text'],
                    'amountf'      => $info['amountf'] ?? $row['amountf'],
                    'coin'         => $info['coin'] ?? $row['coin'],
                    'send_address' => $info['send_address'] ?? $row['send_address'],
                    'send_txid'    => $info['send_txid'] ?? $row['send_txid'],
                ]);
                break;
            }
        }

        $this->notice = "Withdrawal {$id} refreshed.";
    }

    public function render()
    {
        $sort = in_array($this->sort, $this->sortable, true) ? $this->sort : 'time';

        $filtered = collect($this->all)
            ->when($this->search !== '', fn ($c) => $c->filter(function ($r) {
                $haystack = strtolower(implode(' ', [$r['id'], $r['note'], $r['send_txid'], $r['send_address']]));

                return str_contains($haystack, strtolower($this->search));
            }))
            ->when($this->status !== '', fn ($c) => $c->where('status', (int) $this->status))
            ->when($this->coin !== '', fn ($c) => $c->where('coin', $this->coin))
            ->sortBy(
                fn ($r) => match ($sort) {
                    'amountf' => (float) $r['amountf'],
                    'status'  => (int) $r['status'],
                    default   => (int) $r['time_raw'],
                },
                SORT_REGULAR,
                $this->dir === 'desc'
            )
            ->values();

        $page = $this->getPage();
        $paginator = new LengthAwarePaginator(
            $filtered->forPage($page, $this->perPage)->values(),
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page'],
        );

        return view('coinpayment::livewire.admin.withdrawals', [
            'rows'          => $paginator,
            'coins'         => collect($this->all)->pluck('coin')->unique()->filter()->sort()->values(),
            'selected'      => collect($this->all)->firstWhere('id', $this->selectedId),
            'activeFilters' => $this->search !== '' || $this->status !== '' || $this->coin !== '',
            'windowCount'   => count($this->all),
        ]);
    }
}
