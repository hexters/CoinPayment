<?php

namespace Hexters\CoinPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Hexters\CoinPayment\Entities\cointpayment_log_trx;

use Hexters\CoinPayment\Http\Resources\TransactionResourceCollection;

use Hexters\CoinPayment\Jobs\webhookProccessJob;
use App\Jobs\coinPaymentCallbackProccedJob;
use App\Jobs\IPNHandlerCoinPaymentJob;

use Hexters\CoinPayment\Events\IPNErrorReportEvent as SendEmail;

use CoinPayment;
use Route;

class CoinPaymentController extends Controller {

    public function index($serialize) {
      $data['data'] = CoinPayment::get_payload($serialize);
      $data['params'] = empty($data['data']['params']) ? json_encode([]) : json_encode($data['data']['params']);
      $data['payload'] = empty($data['data']['payload']) ? json_encode([]) : json_encode($data['data']['payload']);
      return view('coinpayment::index', $data);
    }

    public function ajax_rates(Request $req, $usd){
      $coins = [];
      $aliases = [];
      $rates = CoinPayment::api_call('rates', [
        'accepted' => 1
      ])['result'];

      $rateBtc = $rates['BTC']['rate_btc'];
      $rateUsd = $rates[config('coinpayment.default_currency')]['rate_btc'];
      $rateAmount = $rateUsd * $usd;
      $fiat = [];
      $coins_accept = [];
      foreach($rates as $i => $coin){
        if((FLOAT) $rates[$i]['rate_btc'] > 0) {
          if((INT) $coin['is_fiat'] === 0){
            $rate = ($rateAmount / $rates[$i]['rate_btc']);
            $coins[] = [
              'name' => $coin['name'],
              'rate' => number_format($rate,8,'.',''),
              'iso' => $i,
              'icon' => 'https://www.coinpayments.net/images/coins/' . $i . '.png',
              'selected' => $i == 'BTC' ? true : false,
              'accepted' => $coin['accepted']
            ];
  
            $aliases[$i] = $coin['name'];
          }
  
          if((INT) $coin['is_fiat'] === 0 && $coin['accepted'] == 1){
            $rate = ($rateAmount / $rates[$i]['rate_btc']);
            $coins_accept[] = [
              'name' => $coin['name'],
              'rate' => number_format($rate,8,'.',''),
              'iso' => $i,
              'icon' => 'https://www.coinpayments.net/images/coins/' . $i . '.png',
              'selected' => $i == 'BTC' ? true : false,
              'accepted' => $coin['accepted']
            ];
          }
  
  
          if((INT) $coin['is_fiat'] === 1){
            $fiat[$i] = $coin;
          }
        }
      }

      return response()->json([
        'coins' => $coins,
        'coins_accept' => $coins_accept,
        'aliases' => $aliases,
        'fiats' =>$fiat
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
        'currency1' => config('coinpayment.default_currency'),
        'currency2' => $req->payment_method,
      ];

      return CoinPayment::api_call('create_transaction', $params);
    }

    public function trx_info(Request $req){
      $payment = CoinPayment::api_call('get_tx_info', [
        'txid' => $req->result['txn_id']
      ]);
      $user = auth()->user();
      if($payment['error'] == 'ok' && (INT) $user->coinpayment_transactions()->where('payment_id', $req->result['txn_id'])->count('id') === 0){
        $data = $payment['result'];

        $saved = [
          'payment_id' => $req->result['txn_id'],
          'payment_address' => $data['payment_address'],
          'coin' => $data['coin'],
          'fiat' => config('coinpayment.default_currency'),
          'status_text' => $data['status_text'],
          'status' => $data['status'],
          'payment_created_at' => date('Y-m-d H:i:s', $data['time_created']),
          'expired' => date('Y-m-d H:i:s', $data['time_expires']),
          'amount' => $data['amountf'],
          'confirms_needed' => empty($req->result['confirms_needed']) ? 0 : $req->result['confirms_needed'],
          'qrcode_url' => empty($req->result['qrcode_url']) ? '' : $req->result['qrcode_url'],
          'status_url' => empty($req->result['status_url']) ? '' : $req->result['status_url'],
          'payload' => empty($req->payload) ? json_encode([]) : json_encode($req->payload),
        ];

        $user->coinpayment_transactions()->create($saved);
      }

      $send['request_type'] = 'create_transaction';
      $send['params'] = empty($req->params) ? [] : $req->params;
      $send['payload'] = empty($req->payload) ? [] : $req->payload;
      $send['transaction'] = $payment['error'] == 'ok' ? $payment['result'] : [];
      if(Route::has('coinpayment.webhook')){
        dispatch(new webhookProccessJob($send));
      }
      dispatch(new coinPaymentCallbackProccedJob($send));
      return $payment;
    }

    public function transactions_list(){
      return view('coinpayment::list');
    }

    public function transactions_list_any(Request $req){
      $transaction = auth()->user()->coinpayment_transactions()->orderby('updated_at', 'desc');
      if(!empty($req->coin))
        $transaction->where('coin', $req->coin);
      if($req->status !== 'all')
        $transaction->where('status', '=', (INT) $req->status);

      return new TransactionResourceCollection($transaction->paginate($req->limit));
    }

    public function manual_check(Request $req){
      $check = CoinPayment::api_call('get_tx_info', [
        'txid' => $req->payment_id
      ]);
      if($check['error'] == 'ok'){
        $data = $check['result'];
        $trx = auth()->user()->coinpayment_transactions()->where('id', $req->id);
        if($data['status'] > 0 || $data['status'] < 0){
          $trx->update([
            'status_text' => $data['status_text'],
            'status' => $data['status'],
            'confirmation_at' => ((INT) $data['status'] === 100) ? date('Y-m-d H:i:s', $data['time_completed']) : null
          ]);
          $trx = $trx->first();
          $data['request_type'] = 'schedule_transaction';
          $data['payload'] = (Array) json_decode($trx->payload, true);
          if(Route::has('coinpayment.webhook')){
            dispatch(new webhookProccessJob($data));
          }
          dispatch(new coinPaymentCallbackProccedJob($data));
        }

        return response()->json($trx->first());
      }

      return response()->json([
        'message' => 'Look like the something wrong!'
      ], 401);
    }

    public function receive_webhook(Request $req){
      /*
        $txn_id = $_POST['txn_id'];
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $amount1 = floatval($_POST['amount1']);
        $amount2 = floatval($_POST['amount2']);
        $currency1 = $_POST['currency1'];
        $currency2 = $_POST['currency2'];
        $status = intval($_POST['status']);
        $status_text = $_POST['status_text'];
      */
      $cp_merchant_id   = config('coinpayment.coinpayment_merchant_id');
      $cp_ipn_secret    = config('coinpayment.coinpayment_ipn_secret');
      $cp_debug_email   = config('coinpayment.coinpayment_ipn_debug_email');

      /* Filtering */
      if(!empty($req->merchant) && $req->merchant != trim($cp_merchant_id)){
        if(!empty($cp_debug_email))
          event(new SendEmail([
            'email' => $cp_debug_email,
            'message' => 'No or incorrect Merchant ID passed'
          ]));

        return response('No or incorrect Merchant ID passed', 401);
      }

      $request = file_get_contents('php://input');
      if ($request === FALSE || empty($request)) {
        if(!empty($cp_debug_email))
          event(new SendEmail([
            'email' => $cp_debug_email,
            'message' => 'Error reading POST data'
          ]));

          return response('Error reading POST data', 401);
      }

      $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
      if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
        if(!empty($cp_debug_email))
          event(new SendEmail([
            'email' => $cp_debug_email,
            'message' => 'HMAC signature does not match'
          ]));

        return response('HMAC signature does not match', 401);
      }

      $log = cointpayment_log_trx::where('payment_id', $req->txn_id)->first();
      if($log != null){
        $log->update([
          'status' => $req->status,
          'status_text' => $req->status_text,
        ]);

        dispatch(new IPNHandlerCoinPaymentJob([
          'payment_id' => $log->payment_id,
          'payment_address' => $log->payment_address,
          'coin' => $log->coin,
          'fiat' => $log->fiat,
          'status_text' => $log->status_text,
          'status' => $log->status,
          'payment_created_at' => $log->payment_created_at,
          'confirmation_at' => $log->confirmation_at,
          'amount' => $log->amount,
          'confirms_needed' => $log->confirms_needed,
          'payload' => (Array) json_decode($log->payload),
        ]));
      }

    }

}
