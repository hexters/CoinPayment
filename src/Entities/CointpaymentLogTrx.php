<?php

namespace Hexters\CoinPayment\Entities;

use Illuminate\Database\Eloquent\Model;

class cointpayment_log_trx extends Model
{
    protected $guarded = ['id'];
    protected $dates = [
      'payment_created_at',
      'expired',
      'confirmation_at'
    ];
    protected $hidden = [
      'created_at', 'updated_at'
    ];
}
