<?php

namespace Hexters\CoinPayment\Events;

use Illuminate\Queue\SerializesModels;

use Hexters\CoinPayment\Emails\IPNErrorReportMail;

use Email;

class IPNErrorReportEvent {

    use SerializesModels;



    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data) {
      Email::to($data['email'])
        ->subject(date('Y/m/d') . ' CoinPayments IPN Error')
        ->send(new IPNErrorReportMail($data));
    }
}
