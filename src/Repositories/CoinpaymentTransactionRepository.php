<?php 

namespace Hexters\CoinPayment\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Emails\NewTransactionToBuyerMail;
use Hexters\CoinPayment\Helpers\CoinPaymentFacade as CoinPayment;

class CoinpaymentTransactionRepository {

  protected $model;

  public function __construct(CoinpaymentTransaction $model) {
    $this->model = $model;
  }

  public function create_transaction(Array $request) {
    
    try {
      DB::beginTransaction();

      $checkout_url = $this->generate_link($request);
      $product = $this->itemAcumulation($request);

      $transaction = $this->model->create([
        'order_id' => $request['order_id'],
        'buyer_name' => $request['buyer_name'],
        'buyer_email' => $request['buyer_email'],
        'amount_total_fiat' => $product['amountTotal'],
        'currency_code' => config('coinpayment.default_currency'),
        'status' => -1000,
        'status_text' => 'Waiting for buyers pay',
        'checkout_url' => $checkout_url,
        'redirect_url' => $request['redirect_url'],
        'cancel_url' => $request['cancel_url'],
      ]);

        foreach($product['items'] as $item) {

          $transaction->items()->create([
            'description' => $item['itemDescription'],
            'price' => $item['itemPrice'],
            'qty' => $item['itemQty'],
            'subtotal' => $item['itemSubtotalAmount'],
            'currency_code' => config('coinpayment.default_currency'),
          ]);

        }

      DB::commit();

      /**
       * Send email to buyer
       */
      if(isset($request['send_email'])) {
        try {
          Mail::to($request['buyer_email'])->send(new NewTransactionToBuyerMail(array_merge($request, [
            'checkout_url' => $checkout_url
          ])));
        } catch (\Exception $e) {}
      }

      session()->flash('success', [
        'Transaction has been created'
      ]);

      return redirect()->route('administrator.coinpayment.transaction.show', [
        $transaction->uuid,
        'back' => route('administrator.coinpayment.transaction.index')
      ]);

    } catch (\Exception $e) {
      DB::rollback();
      
      session()->flash('danger', [
        $e->getMessage()
      ]);

      return redirect()->back();
    }

    

  }

  private function itemAcumulation(Array $data) {

    $amountTotal = 0;
    $items = [];
    foreach($data['itemDescription'] as $i => $description) {
      $total = (FLOAT) $data['itemPrice'][$i] * (INT) $data['itemQty'][$i];
      $items[] = [
        'itemDescription' => $description,
        'itemPrice' => (FLOAT) $data['itemPrice'][$i],
        'itemQty' => (INT) $data['itemQty'][$i],
        'itemSubtotalAmount' => $total
      ];

      $amountTotal += (FLOAT) $total;
    }

    return [
      'amountTotal' => $amountTotal,
      'items' => $items
    ];

  }

  private function generate_link(Array $data) { 
    /*
    *   @required true
    */
    $transaction['order_id'] = $data['order_id'];
    
    $transaction['note'] = $data['note'];
    $transaction['buyer_name'] = $data['buyer_name'];
    $transaction['buyer_email'] = $data['buyer_email'];
    $transaction['redirect_url'] = $data['redirect_url'];

    /*
    *   @required true
    *   @example first item
    */
    $product = $this->itemAcumulation($data);
    $transaction['items'] = $product['items'];
    $transaction['amountTotal'] = (FLOAT) $product['amountTotal'];
    
    return CoinPayment::generatelink($transaction);
  }

}