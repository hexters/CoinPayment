<?php

namespace Hexters\CoinPayment\Tests;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Livewire\FormTransaction;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

class FormTransactionTest extends TestCase
{
    protected function payload(): string
    {
        return Crypt::encryptString(json_encode([
            'order_id'     => 'INV-001',
            'amountTotal'  => 100.0,
            'note'         => 'Test order',
            'buyer_name'   => 'John Doe',
            'buyer_email'  => 'john@example.com',
            'redirect_url' => 'https://shop.test/done',
            'cancel_url'   => 'https://shop.test/cancel',
            'items'        => [
                ['itemDescription' => 'Item A', 'itemPrice' => 100.0, 'itemQty' => 1, 'itemSubtotalAmount' => 100.0],
            ],
        ]));
    }

    protected function fakeApi(): void
    {
        Http::fake(function ($request) {
            $body = $request->body();

            if (str_contains($body, 'cmd=rates')) {
                return Http::response(['error' => 'ok', 'result' => [
                    'BTC' => ['is_fiat' => 0, 'rate_btc' => 1, 'accepted' => 1, 'name' => 'Bitcoin'],
                    'LTC' => ['is_fiat' => 0, 'rate_btc' => 0.005, 'accepted' => 1, 'name' => 'Litecoin'],
                    'USD' => ['is_fiat' => 1, 'rate_btc' => 0.00002, 'accepted' => 0, 'name' => 'US Dollar'],
                ]]);
            }

            if (str_contains($body, 'cmd=create_transaction')) {
                return Http::response(['error' => 'ok', 'result' => [
                    'txn_id'      => 'TXN-123',
                    'address'     => 'bc1qexampleaddress',
                    'amount'      => 0.002,
                    'status_url'  => 'https://coinpayments.net/status',
                    'qrcode_url'  => 'https://coinpayments.net/qr.png',
                ]]);
            }

            if (str_contains($body, 'cmd=get_tx_info')) {
                return Http::response(['error' => 'ok', 'result' => [
                    'status'          => 0,
                    'status_text'     => 'Waiting for funds',
                    'amountf'         => '0.00200000',
                    'coin'            => 'BTC',
                    'receivedf'       => '0.00000000',
                    'recv_confirms'   => 0,
                    'confirms_needed' => 2,
                    'time_expires'    => time() + 3600,
                ]]);
            }

            return Http::response(['error' => 'unknown command'], 200);
        });
    }

    public function test_checkout_route_renders_the_livewire_component(): void
    {
        $this->fakeApi();

        $this->get('/coinpayment/make/' . $this->payload())
            ->assertOk()
            ->assertSeeLivewire(FormTransaction::class);
    }

    public function test_it_loads_accepted_coins_on_mount(): void
    {
        $this->fakeApi();

        Livewire::test(FormTransaction::class, ['payload' => $this->payload()])
            ->assertSet('defaultCurrency', 'USD')
            ->assertSet('defaultCoin.iso', 'BTC')
            ->assertCount('acceptedCoins', 2);
    }

    public function test_it_creates_a_transaction_and_persists_items(): void
    {
        $this->fakeApi();

        Livewire::test(FormTransaction::class, ['payload' => $this->payload()])
            ->call('createTransaction')
            ->assertSet('errorMessage', null)
            ->assertSet('transaction.txn_id', 'TXN-123');

        $this->assertDatabaseHas('coinpayment_transactions', [
            'order_id' => 'INV-001',
            'txn_id'   => 'TXN-123',
        ]);
        $this->assertDatabaseHas('coinpayment_transaction_items', [
            'description' => 'Item A',
            'qty'         => 1,
        ]);
    }

    public function test_it_rejects_duplicate_order(): void
    {
        $this->fakeApi();

        CoinpaymentTransaction::create([
            'order_id'    => 'INV-001',
            'txn_id'      => 'EXISTING',
            'status_text' => 'Complete',
        ]);

        Livewire::test(FormTransaction::class, ['payload' => $this->payload()])
            ->call('createTransaction')
            ->assertSet('errorMessage', fn ($v) => str_contains((string) $v, 'already exists'));
    }

    public function test_items_are_optional(): void
    {
        $this->fakeApi();

        $payload = Crypt::encryptString(json_encode([
            'order_id'    => 'INV-NOITEMS',
            'amountTotal' => 100.0,
            'note'        => 'No items provided',
        ]));

        Livewire::test(FormTransaction::class, ['payload' => $payload])
            ->assertSet('errorMessage', null)
            ->assertSet('defaultCoin.iso', 'BTC');
    }

    public function test_poll_status_refreshes_until_complete(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->body(), 'cmd=rates')) {
                return Http::response(['error' => 'ok', 'result' => [
                    'BTC' => ['is_fiat' => 0, 'rate_btc' => 1, 'accepted' => 1, 'name' => 'Bitcoin'],
                    'USD' => ['is_fiat' => 1, 'rate_btc' => 0.00002, 'accepted' => 0, 'name' => 'US Dollar'],
                ]]);
            }

            return Http::response(['error' => 'ok', 'result' => [
                'status' => 100, 'status_text' => 'Complete', 'receivedf' => '1.00000000', 'recv_confirms' => 2,
            ]]);
        });

        CoinpaymentTransaction::create([
            'order_id' => 'INV-001', 'txn_id' => 'TXN-POLL', 'status' => '0',
            'amountf' => '1', 'receivedf' => '0', 'coin' => 'BTC',
        ]);

        Livewire::test(FormTransaction::class, ['payload' => $this->payload()])
            ->assertSet('transaction.status', fn ($v) => (int) $v === 0)
            ->call('pollStatus')
            ->assertSet('transaction.status', fn ($v) => (int) $v === 100);
    }

    public function test_partial_payment_shows_remaining_amount(): void
    {
        $this->fakeApi();

        CoinpaymentTransaction::create([
            'order_id'  => 'INV-001',
            'txn_id'    => 'TXN-PART',
            'status'    => '0',
            'amountf'   => '1',
            'receivedf' => '0.4',
            'coin'      => 'BTC',
        ]);

        Livewire::test(FormTransaction::class, ['payload' => $this->payload()])
            ->assertSee('Remaining to send');
    }

    public function test_mismatched_item_total_is_rejected(): void
    {
        $this->fakeApi();

        $payload = Crypt::encryptString(json_encode([
            'order_id'    => 'INV-BAD',
            'amountTotal' => 100.0,
            'note'        => 'Mismatched items',
            'items'       => [
                ['itemDescription' => 'X', 'itemPrice' => 50, 'itemQty' => 1, 'itemSubtotalAmount' => 50],
            ],
        ]));

        Livewire::test(FormTransaction::class, ['payload' => $payload])
            ->assertSet('errorMessage', fn ($v) => str_contains((string) $v, 'does not match amountTotal'));
    }
}
