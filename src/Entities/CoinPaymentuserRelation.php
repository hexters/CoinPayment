<?php

  namespace Hexters\CoinPayment\Entities;

  trait CoinPaymentuserRelation {

    public function coinpayment_transactions(){
      return $this->hasMany(cointpayment_log_trx::class, 'user_id');
    }

  }
