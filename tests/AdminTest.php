<?php

namespace Hexters\CoinPayment\Tests;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Livewire\Admin\Balances;
use Hexters\CoinPayment\Livewire\Admin\Transactions;
use Hexters\CoinPayment\Livewire\Admin\Withdrawals;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

class AdminTest extends TestCase
{
    public function test_admin_is_denied_when_gate_is_undefined(): void
    {
        // No gate defined -> fail closed even for authenticated users.
        $this->actingAs(new AuthUser)
            ->get('/coinpayment/admin')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_configured_target(): void
    {
        config()->set('coinpayment.admin.redirect', '/my-login');

        $this->get('/coinpayment/admin')->assertRedirect('/my-login');
    }

    public function test_admin_is_accessible_when_gate_allows(): void
    {
        Http::fake(['*' => Http::response(['error' => 'ok', 'result' => []])]);
        Gate::define('coinpayment-admin', fn ($user) => true);

        $this->actingAs(new AuthUser)
            ->get('/coinpayment/admin')
            ->assertOk()
            ->assertSeeLivewire(Balances::class);
    }

    public function test_balances_are_loaded_and_formatted(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'ok', 'result' => [
                'BTC' => ['balance' => 100000000, 'balancef' => 1.0, 'coin_status' => 'online', 'status' => 'available'],
            ]]),
        ]);

        Livewire::test(Balances::class)
            ->assertSet('error', null)
            ->assertSet('balances.0.coin', 'BTC')
            ->assertSet('balances.0.balancef', '1.00000000');
    }

    public function test_balances_show_fiat_conversion(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->body(), 'cmd=balances')) {
                return Http::response(['error' => 'ok', 'result' => [
                    'BTC' => ['balance' => 200000000, 'balancef' => 2.0, 'coin_status' => 'online', 'status' => 'available'],
                ]]);
            }

            return Http::response(['error' => 'ok', 'result' => [
                'BTC' => ['rate_btc' => '1', 'is_fiat' => 0],
                'USD' => ['rate_btc' => '0.00002', 'is_fiat' => 1],
            ]]);
        });

        Livewire::test(Balances::class)
            ->assertSet('balances.0.fiat', '100,000.00')
            ->assertSet('totalFiat', '100,000.00');
    }

    public function test_testnet_coins_are_excluded_from_fiat(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->body(), 'cmd=balances')) {
                return Http::response(['error' => 'ok', 'result' => [
                    'BTC'  => ['balance' => 100000000, 'balancef' => 1.0, 'coin_status' => 'online', 'status' => 'available'],
                    'LTCT' => ['balance' => 200000000, 'balancef' => 2.0, 'coin_status' => 'online', 'status' => 'available'],
                ]]);
            }

            return Http::response(['error' => 'ok', 'result' => [
                'BTC'  => ['rate_btc' => '1', 'is_fiat' => 0],
                'LTCT' => ['rate_btc' => '0.005', 'is_fiat' => 0],
                'USD'  => ['rate_btc' => '0.00002', 'is_fiat' => 1],
            ]]);
        });

        Livewire::test(Balances::class)
            ->assertSet('balances.0.fiat', '50,000.00')   // BTC converted
            ->assertSet('balances.1.fiat', null)          // LTCT excluded
            ->assertSet('balances.1.testnet', true)
            ->assertSet('totalFiat', '50,000.00');        // total excludes LTCT
    }

    public function test_withdrawal_validates_required_fields(): void
    {
        Http::fake(['*' => Http::response(['error' => 'ok', 'result' => []])]);

        Livewire::test(Balances::class)
            ->call('openWithdraw', 'BTC')
            ->call('withdraw')
            ->assertHasErrors(['wAmount', 'wAddress']);
    }

    public function test_transactions_list_renders_and_searches(): void
    {
        CoinpaymentTransaction::create(['order_id' => 'INV-AAA', 'txn_id' => 'T1', 'status_text' => 'Complete']);
        CoinpaymentTransaction::create(['order_id' => 'INV-BBB', 'txn_id' => 'T2', 'status_text' => 'Waiting']);

        Livewire::test(Transactions::class)
            ->assertSee('INV-AAA')
            ->assertSee('INV-BBB')
            ->set('search', 'AAA')
            ->assertSee('INV-AAA')
            ->assertDontSee('INV-BBB');
    }

    public function test_transactions_filter_by_status(): void
    {
        CoinpaymentTransaction::create(['order_id' => 'INV-DONE', 'txn_id' => 'D1', 'status' => '100', 'status_text' => 'Complete']);
        CoinpaymentTransaction::create(['order_id' => 'INV-WAIT', 'txn_id' => 'W1', 'status' => '0', 'status_text' => 'Waiting']);

        Livewire::test(Transactions::class)
            ->set('status', 'complete')
            ->assertSee('INV-DONE')
            ->assertDontSee('INV-WAIT');
    }

    public function test_transactions_sorting_toggles_and_sorts_numeric_columns(): void
    {
        CoinpaymentTransaction::create(['order_id' => 'INV-1', 'txn_id' => 'S1', 'status' => '0', 'amountf' => '0.5']);
        CoinpaymentTransaction::create(['order_id' => 'INV-2', 'txn_id' => 'S2', 'status' => '100', 'amountf' => '0.05']);

        Livewire::test(Transactions::class)
            ->assertSet('sort', 'created_at')
            ->assertSet('dir', 'desc')
            ->call('sortBy', 'order_id')
            ->assertSet('sort', 'order_id')
            ->assertSet('dir', 'asc')
            ->call('sortBy', 'order_id')
            ->assertSet('dir', 'desc')
            ->call('sortBy', 'amountf')   // exercises numeric orderByRaw
            ->assertOk()
            ->call('sortBy', 'status')    // exercises numeric orderByRaw
            ->assertOk();
    }

    public function test_transactions_show_partial_badge(): void
    {
        CoinpaymentTransaction::create([
            'order_id' => 'INV-PART', 'txn_id' => 'P1', 'status' => '0',
            'amountf' => '1', 'receivedf' => '0.5', 'coin' => 'LTCT',
        ]);

        Livewire::test(Transactions::class)->assertSee('Partial');
    }

    public function test_withdrawal_history_lists_records(): void
    {
        Http::fake(['*' => Http::response(['error' => 'ok', 'result' => [
            ['id' => 'CW1', 'time_created' => 1700000000, 'coin' => 'LTCT', 'amountf' => '0.02', 'status' => 0, 'status_text' => 'Awaiting email confirmation', 'note' => ''],
        ]])]);

        Livewire::test(Withdrawals::class)
            ->assertSet('error', null)
            ->assertSee('CW1')
            ->assertSee('Awaiting confirmation');
    }

    public function test_withdrawal_can_be_cancelled(): void
    {
        Http::fake(['*' => Http::response(['error' => 'ok', 'result' => []])]);

        Livewire::test(Withdrawals::class)
            ->call('cancel', 'CW1')
            ->assertSet('notice', fn ($v) => str_contains((string) $v, 'cancelled'));
    }

    public function test_withdrawals_filter_and_sort(): void
    {
        Http::fake(['*' => Http::response(['error' => 'ok', 'result' => [
            ['id' => 'CW1', 'time_created' => 1700000000, 'coin' => 'LTCT', 'amountf' => '0.05', 'status' => 2, 'status_text' => 'Complete', 'note' => ''],
            ['id' => 'CW2', 'time_created' => 1700001000, 'coin' => 'BTC', 'amountf' => '0.01', 'status' => 0, 'status_text' => 'Awaiting email confirmation', 'note' => ''],
        ]])]);

        Livewire::test(Withdrawals::class)
            ->assertSee('CW1')
            ->assertSee('CW2')
            ->set('status', '2')->assertSee('CW1')->assertDontSee('CW2')
            ->set('status', '')->set('coin', 'BTC')->assertSee('CW2')->assertDontSee('CW1')
            ->set('coin', '')
            ->call('sortBy', 'amountf')->assertSet('sort', 'amountf')->assertOk();
    }

    public function test_withdrawal_detail_modal_and_single_refresh(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->body(), 'cmd=get_withdrawal_history')) {
                return Http::response(['error' => 'ok', 'result' => [
                    ['id' => 'CW1', 'time_created' => 1700000000, 'coin' => 'LTCT', 'amountf' => '0.02', 'status' => 0, 'status_text' => 'Awaiting email confirmation', 'note' => 'my-note'],
                ]]);
            }

            // get_withdrawal_info -> now complete
            return Http::response(['error' => 'ok', 'result' => [
                'status' => 2, 'status_text' => 'Complete', 'coin' => 'LTCT', 'amountf' => '0.02', 'send_txid' => 'abc123',
            ]]);
        });

        Livewire::test(Withdrawals::class)
            ->call('show', 'CW1')
            ->assertSet('selectedId', 'CW1')
            ->assertSee('my-note')
            ->call('refreshOne', 'CW1')
            ->assertSee('Complete');
    }
}
