<?php
  namespace Hexters\CoinPayment\Classes;

  class CoinPaymentClass {

    use coinPaymentTrait;

    public function url_payload($payload = []){
      $data['note'] = empty($payload['note']) ? '' : $payload['note'];
      $data['amountTotal'] = empty($payload['amountTotal']) ? 1 : $payload['amountTotal'];
      $data['items'] = empty($payload['items']) ? [] : $payload['items'];
      $data['csrf'] = session()->token();
      $data['params'] = empty($payload['params']) ? [] : $payload['params'];
      $data['payload'] = empty($payload['payload']) ? [] : $payload['payload'];

      $params = base64_encode(serialize($data));
      return route('coinpayment.create.transaction', $params);
    }

    public function get_payload($serialize){
      $data = unserialize(base64_decode($serialize));
      if(empty($data['csrf']) || $data['csrf'] !== session()->token())
        return abort(404);
      unset($data['csrf']);

      return $data;
    }

    public function link_transaction_list(){
      return route('coinpayment.transaction.list');
    }

  }
