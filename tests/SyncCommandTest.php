<?php

namespace Hexters\CoinPayment\Tests;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Illuminate\Support\Facades\Http;

class SyncCommandTest extends TestCase
{
    public function test_it_marks_unpaid_expired_transactions(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'ok', 'result' => [
                'status'      => 0,
                'status_text' => 'Waiting for buyer funds',
                'receivedf'   => '0.00000000',
                'recv_confirms' => 0,
            ]]),
        ]);

        CoinpaymentTransaction::create([
            'order_id'     => 'INV-EXP',
            'txn_id'       => 'TXN-EXP',
            'status'       => '0',
            'status_text'  => 'Waiting',
            'receivedf'    => 0,
            'time_expires' => (string) (now()->getTimestamp() - 60),
        ]);

        $this->artisan('coinpayment:sync')->assertExitCode(0);

        $trx = CoinpaymentTransaction::where('txn_id', 'TXN-EXP')->first();
        $this->assertSame(-1, (int) $trx->status);
        $this->assertSame('Expired / Timed out', $trx->status_text);
    }

    public function test_it_keeps_unexpired_pending_transactions(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'ok', 'result' => [
                'status'      => 0,
                'status_text' => 'Waiting for buyer funds',
                'receivedf'   => '0.00000000',
                'recv_confirms' => 0,
            ]]),
        ]);

        CoinpaymentTransaction::create([
            'order_id'     => 'INV-LIVE',
            'txn_id'       => 'TXN-LIVE',
            'status'       => '0',
            'status_text'  => 'Waiting',
            'receivedf'    => 0,
            'time_expires' => (string) (now()->getTimestamp() + 3600),
        ]);

        $this->artisan('coinpayment:sync')->assertExitCode(0);

        $trx = CoinpaymentTransaction::where('txn_id', 'TXN-LIVE')->first();
        $this->assertSame(0, (int) $trx->status);
    }
}
