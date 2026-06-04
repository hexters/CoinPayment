<?php

namespace Hexters\CoinPayment\Tests;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Illuminate\Support\Facades\Http;

class IpnTest extends TestCase
{
    public function test_it_rejects_invalid_hmac(): void
    {
        $this->call('POST', '/coinpayment/ipn', [], [], [], ['HTTP_HMAC' => 'wrong'], 'txn_id=ABC')
            ->assertStatus(401);
    }

    public function test_it_updates_transaction_on_valid_ipn(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'ok', 'result' => [
                'status'      => 100,
                'status_text' => 'Complete',
            ]]),
        ]);

        CoinpaymentTransaction::create([
            'order_id'    => 'INV-001',
            'txn_id'      => 'TXN-123',
            'status'      => '0',
            'status_text' => 'Waiting',
        ]);

        $body = 'txn_id=TXN-123';
        $hmac = hash_hmac('sha512', $body, 'ipn-secret');

        $this->call('POST', '/coinpayment/ipn', ['txn_id' => 'TXN-123'], [], [], ['HTTP_HMAC' => $hmac], $body)
            ->assertOk()
            ->assertSee('IPN OK');

        $this->assertDatabaseHas('coinpayment_transactions', [
            'txn_id'      => 'TXN-123',
            'status_text' => 'Complete',
        ]);
    }
}
