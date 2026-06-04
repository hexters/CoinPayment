<?php

namespace Hexters\CoinPayment\Livewire\Admin;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Licensing\License;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('coinpayment::layouts.admin')]
#[Title('CoinPayment · Transactions')]
class Transactions extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    /** Status group: '', waiting, confirming, complete, cancelled. */
    #[Url]
    public string $status = '';

    #[Url]
    public string $coin = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $dir = 'desc';

    #[Url]
    public int $perPage = 15;

    public ?int $selectedId = null;

    public ?string $flash = null;

    /** Columns the table may be sorted by. */
    protected array $sortable = ['order_id', 'amountf', 'status', 'created_at'];

    /** Status group -> raw status values stored in the DB. */
    protected array $statusGroups = [
        'waiting'   => ['0'],
        'confirming' => ['1', '2', '3'],
        'complete'  => ['100'],
        'cancelled' => ['-1', '-2'],
    ];

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

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
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
        $this->reset(['search', 'status', 'coin', 'from', 'to']);
        $this->resetPage();
    }

    public function show(int $id): void
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

    public function refreshStatus(string $txnId): void
    {
        $result = app(CoinPaymentHelper::class)->getstatusbytxnid($txnId);

        $this->flash = is_array($result)
            ? 'Status refreshed: ' . $result['status_text']
            : 'Error: ' . $result;
    }

    public function render()
    {
        $sort = in_array($this->sort, $this->sortable, true) ? $this->sort : 'created_at';
        $dir  = $this->dir === 'asc' ? 'asc' : 'desc';

        $query = CoinpaymentTransaction::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w) {
                $w->where('order_id', 'like', "%{$this->search}%")
                    ->orWhere('txn_id', 'like', "%{$this->search}%")
                    ->orWhere('buyer_email', 'like', "%{$this->search}%");
            }))
            ->when(isset($this->statusGroups[$this->status]), fn ($q) => $q->whereIn('status', $this->statusGroups[$this->status]))
            ->when($this->coin !== '', fn ($q) => $q->where('coin', $this->coin))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        // Numeric-aware sorting for string-typed columns.
        match ($sort) {
            'amountf' => $query->orderByRaw("CAST(amountf AS DECIMAL(30,10)) {$dir}"),
            'status'  => $query->orderByRaw("CAST(status AS DECIMAL(30,0)) {$dir}"),
            default   => $query->orderBy($sort, $dir),
        };

        return view('coinpayment::livewire.admin.transactions', [
            'transactions' => $query->paginate($this->perPage),
            'coins'        => CoinpaymentTransaction::query()->whereNotNull('coin')->distinct()->orderBy('coin')->pluck('coin'),
            'selected'     => $this->selectedId ? CoinpaymentTransaction::with('items')->find($this->selectedId) : null,
            'activeFilters' => $this->search !== '' || $this->status !== '' || $this->coin !== '' || $this->from !== '' || $this->to !== '',
        ]);
    }
}
