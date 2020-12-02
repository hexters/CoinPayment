<?php

namespace Hexters\CoinPayment\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewTransactionToBuyerMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $data;

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

        return $this->subject(config('app.name') . ' - Payment #' . $this->data['order_id'])
            ->markdown(config('coinpayment.mail.layout.transaction_to_buyer'), [ 'data' => $this->data ]);
    }
}
