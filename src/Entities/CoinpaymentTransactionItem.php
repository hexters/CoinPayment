<?php

namespace Hexters\CoinPayment\Entities;

use Illuminate\Database\Eloquent\Model;

class CoinpaymentTransactionItem extends Model {
    
    protected $fillable = [
        'coinpayment_transaction_id',
        'description',
        'price',
        'qty',
        'subtotal',
        'currency_code',
        'type',
        'state',
    ];
    
}
