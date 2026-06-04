<?php

namespace Hexters\CoinPayment\Livewire;

use Hexters\CoinPayment\Licensing\License;
use Livewire\Component;

class LicenseGate extends Component
{
    public string $serial = '';

    public ?string $error = null;

    public function activate(): void
    {
        $this->error = null;

        if (License::activate(trim($this->serial))) {
            $this->js('window.location.reload()');

            return;
        }

        $this->error = 'Invalid serial number. Please check it and try again.';
    }

    public function render()
    {
        return view('coinpayment::livewire.license-gate', [
            'locked'      => License::locked(),
            'purchaseUrl' => License::PURCHASE_URL,
        ]);
    }
}
