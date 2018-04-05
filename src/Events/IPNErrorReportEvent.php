<?php

namespace Hexters\CoinPayment\Events;

use Illuminate\Queue\SerializesModels;

use Hexters\CoinPayment\Emails\IPNErrorReportMail;

use Mail;

class IPNErrorReportEvent {

    use SerializesModels;



    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data) {
      Mail::to($data['email'])
        ->send(new IPNErrorReportMail($data));
    }
}
