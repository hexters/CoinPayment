<?php

namespace Hexters\CoinPayment\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class IPNErrorReportMail extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this
          ->subject(date('Y/m/d') . ' CoinPayments IPN Error')
          ->view('coinpayment::emails.error_reporting', [
            'data' => $this->data
          ]);
    }
}
