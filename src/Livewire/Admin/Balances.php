<?php

namespace Hexters\CoinPayment\Livewire\Admin;

use Hexters\CoinPayment\Licensing\License;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('coinpayment::layouts.admin')]
#[Title('CoinPayment · Balances')]
class Balances extends Component
{
    use ApiCallTrait;

    /** @var array<int, array<string, mixed>> */
    public array $balances = [];

    public ?string $error = null;

    public string $defaultCurrency = '';

    public ?string $totalFiat = null;

    public bool $showWithdraw = false;

    public string $wCoin = '';

    #[Validate('required|numeric|gt:0')]
    public $wAmount = '';

    #[Validate('required|string')]
    public string $wAddress = '';

    #[Validate('nullable|max:60')]
    public string $wNote = '';

    public function mount(): void
    {
        $this->loadBalances();
    }

    public function loadBalances(): void
    {
        $this->error = null;
        $this->defaultCurrency = config('coinpayment.default_currency');

        $response = $this->api_call('balances');

        if (($response['error'] ?? null) !== 'ok') {
            $this->error = $response['error'] ?? 'Unable to load balances.';
            $this->balances = [];

            return;
        }

        // Exchange rates (in BTC) used to convert each coin balance to fiat.
        $rates    = $this->api_call('rates', ['short' => 1]);
        $rateMap  = ($rates['error'] ?? null) === 'ok' ? ($rates['result'] ?? []) : [];
        $fiatRate = (float) ($rateMap[$this->defaultCurrency]['rate_btc'] ?? 0);
        $exclude  = (array) config('coinpayment.fiat_exclude', []);

        $mapped = collect($response['result'])
            ->map(function ($data, $coin) use ($rateMap, $fiatRate, $exclude) {
                $balancef = (float) $data['balancef'];
                $coinRate = (float) ($rateMap[$coin]['rate_btc'] ?? 0);
                $testnet  = in_array($coin, $exclude, true);
                $fiatRaw  = (! $testnet && $fiatRate > 0 && $coinRate > 0) ? $balancef * $coinRate / $fiatRate : null;

                return [
                    'coin'        => $coin,
                    'balancef'    => number_format($balancef, 8),
                    'fiat'        => $fiatRaw !== null ? number_format($fiatRaw, 2) : null,
                    'fiat_raw'    => $fiatRaw,
                    'testnet'     => $testnet,
                    'coin_status' => $data['coin_status'] ?? '',
                    'status'      => $data['status'] ?? '',
                    'address'     => null,
                    'icon'        => 'https://www.coinpayments.net/images/coins/' . $coin . '.png',
                ];
            })
            ->values();

        $totals = $mapped->pluck('fiat_raw')->filter(fn ($v) => $v !== null);
        $this->totalFiat = $totals->isNotEmpty() ? number_format($totals->sum(), 2) : null;

        $this->balances = $mapped->all();
    }

    public function topUp(string $coin): void
    {
        if (License::locked()) {
            $this->dispatch('cp-show-license');

            return;
        }

        $response = $this->api_call('get_deposit_address', ['currency' => $coin]);

        if (($response['error'] ?? null) !== 'ok') {
            $this->error = $response['error'] ?? 'Unable to fetch deposit address.';

            return;
        }

        $this->balances = collect($this->balances)
            ->map(function ($row) use ($coin, $response) {
                if ($row['coin'] === $coin) {
                    $row['address'] = $response['result']['address'] ?? null;
                }

                return $row;
            })
            ->all();
    }

    public function openWithdraw(string $coin): void
    {
        if (License::locked()) {
            $this->dispatch('cp-show-license');

            return;
        }

        $this->resetValidation();
        $this->wCoin    = $coin;
        $this->wAmount  = '';
        $this->wAddress = '';
        $this->wNote    = '';
        $this->showWithdraw = true;
    }

    public function withdraw(): void
    {
        if (License::locked()) {
            $this->dispatch('cp-show-license');

            return;
        }

        $this->validate();

        $response = $this->api_call('create_withdrawal', [
            'amount'   => $this->wAmount,
            'currency' => $this->wCoin,
            'address'  => $this->wAddress,
            'note'     => $this->wNote,
        ]);

        if (($response['error'] ?? null) !== 'ok') {
            $this->addError('wAmount', $response['error'] ?? 'Withdrawal failed.');

            return;
        }

        $this->showWithdraw = false;

        $id = $response['result']['id'] ?? '';
        session()->flash(
            'coinpayment_notice',
            trim("Withdrawal {$id} created. CoinPayments has e-mailed you a confirmation link — open it and click to approve, or the withdrawal will not be processed.")
        );

        $this->redirectRoute('coinpayment.admin.withdrawals', navigate: true);
    }

    public function render()
    {
        return view('coinpayment::livewire.admin.balances', [
            'locked' => License::locked(),
        ]);
    }
}
