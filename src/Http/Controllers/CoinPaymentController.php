<?php

namespace Hexters\CoinPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Hexters\CoinPayment\Entities\cointpayment_log_trx;

use Hexters\CoinPayment\Http\Resources\TransactionResourceCollection;

use CoinPayment;

class CoinPaymentController extends Controller {

    public function index($serialize) {
      $data['data'] = CoinPayment::get_payload($serialize);
      return view('coinpayment::index', $data);
    }

    public function ajax_rates(Request $req, $usd){
      $coins = [];
      $aliases = [];
      $rates = CoinPayment::api_call('rates', [
        'accepted' => 1
      ])['result'];

      $rateBtc = $rates['BTC']['rate_btc'];
      $rateUsd = $rates['USD']['rate_btc'];
      $rateAmount = $rateUsd * $usd;

      foreach($rates as $i => $coin){
        if($coin['accepted']){
          $rate = ($rateAmount / $rates[$i]['rate_btc']);
          $coins[] = [
            'name' => $coin['name'],
            'rate' => number_format($rate,8,'.',''),
            'iso' => $i,
            'icon' => 'https://www.coinpayments.net/images/coins/' . $i . '.png',
            'selected' => $i == 'BTC' ? true : false
          ];

          $aliases[$i] = $coin['name'];
        }
      }

      return response()->json([
        'coins' => $coins,
        'aliases' => $aliases
      ]);
    }

    public function make_transaction(Request $req){

      $err = $req->validate([
        'amount' => 'required|numeric',
        'payment_method' => 'required'
      ]);

      if(!empty($err['message']))
        return response()->json($err);

      $params = [
        'amount' => $req->amount,
        'currency1' => 'USD',
        'currency2' => $req->payment_method,
      ];

      return CoinPayment::api_call('create_transaction', $params);
    }

    public function trx_info(Request $req, $txid){
      $payment = CoinPayment::api_call('get_tx_info', [
        'txid' => $txid
      ]);
      $user = auth()->user();
      if($payment['error'] == 'ok' && (INT) $user->coinpayment_transactions()->where('payment_id', $txid)->count('id') === 0){
        $data = $payment['result'];

        $saved = [
          'payment_id' => $txid,
          'payment_address' => $data['payment_address'],
          'coin' => $data['coin'],
          'status_text' => $data['status_text'],
          'status' => $data['status'],
          'payment_created_at' => date('Y-m-d H:i:s', $data['time_created']),
          'expired' => date('Y-m-d H:i:s', $data['time_expires']),
          'amount' => $data['amountf'],
          'confirms_needed' => empty($req->confirms_needed) ? 0 : $req->confirms_needed,
          'qrcode_url' => empty($req->qrcode_url) ? '' : $req->qrcode_url,
          'status_url' => empty($req->status_url) ? '' : $req->status_url
        ];

        $user->coinpayment_transactions()->create($saved);
      }

      return $payment;
    }

    public function transactions_list(){
      return view('coinpayment::list');
    }

    public function transactions_list_any(Request $req){
      $transaction = auth()->user()->coinpayment_transactions()->orderby('updated_at', 'desc')->paginate(5);
      return new TransactionResourceCollection($transaction);
    }
}
