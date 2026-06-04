<?php

namespace Hexters\CoinPayment\Tests;

use Hexters\CoinPayment\Licensing\License;
use Hexters\CoinPayment\Livewire\LicenseGate;
use Livewire\Livewire;

class LicenseTest extends TestCase
{
    protected function tearDown(): void
    {
        // Restore env before Testbench rolls back migrations (production guard).
        $this->app['env'] = 'testing';
        @unlink(storage_path('app/coinpayment/.licensed'));

        parent::tearDown();
    }

    protected function gatedEnv(): void
    {
        $this->app['env'] = 'production';
        config()->set('app.debug', false);
        @unlink(storage_path('app/coinpayment/.licensed'));
    }

    public function test_not_enforced_in_local_or_testing(): void
    {
        // Testbench runs in the "testing" environment → gate is off.
        $this->assertFalse(License::enforced());
        $this->assertFalse(License::locked());
        $this->assertTrue(License::active());
    }

    public function test_locked_in_production_without_license(): void
    {
        $this->gatedEnv();

        $this->assertTrue(License::enforced());
        $this->assertTrue(License::locked());
    }

    public function test_invalid_serial_is_rejected(): void
    {
        $this->gatedEnv();

        $this->assertFalse(License::activate('NOT-A-REAL-SERIAL'));
        $this->assertTrue(License::locked());
    }

    public function test_valid_serial_activates_and_unlocks(): void
    {
        $this->gatedEnv();

        $this->assertTrue(License::activate('HEXCP-PRO-7F3A-9K2D'));
        $this->assertFalse(License::locked());
        $this->assertTrue(License::active());
    }

    public function test_license_gate_component_activates(): void
    {
        $this->gatedEnv();

        Livewire::test(LicenseGate::class)
            ->set('serial', 'HEXCP-PRO-2M8B-4Q6R')
            ->call('activate')
            ->assertSet('error', null);

        $this->assertFalse(License::locked());
    }

    public function test_license_gate_rejects_bad_serial(): void
    {
        $this->gatedEnv();

        Livewire::test(LicenseGate::class)
            ->set('serial', 'BAD')
            ->call('activate')
            ->assertSet('error', fn ($v) => str_contains((string) $v, 'Invalid'));

        $this->assertTrue(License::locked());
    }
}
