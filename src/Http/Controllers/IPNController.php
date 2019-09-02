<?php

namespace Hexters\CoinPayment\Http\Controllers;

use App\Jobs\CoinpaymentListener;
use Hexters\CoinPayment\Emails\IPNErrorMail as SendEmail;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Traits\ApiCallTrait;

class IPNController extends Controller {
    
    use ApiCallTrait;

    public function __invoke(Request $req){
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
    $cp_merchant_id   = config('coinpayment.ipn.config.coinpayment_merchant_id');
    $cp_ipn_secret    = config('coinpayment.ipn.config.coinpayment_ipn_secret');
    $cp_debug_email   = config('coinpayment.ipn.config.coinpayment_ipn_debug_email');
    
    /* Filtering */
    if(!empty($req->merchant) && $req->merchant != trim($cp_merchant_id)){
        if(!empty($cp_debug_email)) {
            \Mail::to($cp_debug_email)->send(new SendEmail([
                
                'message' => 'No or incorrect Merchant ID passed'
            ]));
        }
        return response('No or incorrect Merchant ID passed', 401);
    }
    $request = file_get_contents('php://input');
    if ($request === FALSE || empty($request)) {
        if(!empty($cp_debug_email)) {
            \Mail::to($cp_debug_email)->send(new SendEmail([
                
                'message' => 'Error reading POST data'
            ]));
        }
        return response('Error reading POST data', 401);
    }
    $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
    if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
        if(!empty($cp_debug_email)) {
            \Mail::to($cp_debug_email)->send(new SendEmail([
                'message' => 'HMAC signature does not match'
            ]));
        }
        return response('HMAC signature does not match', 401);
    }

    $transactions = CoinpaymentTransaction::where('txn_id', $req->txn_id)->first();

        if($transactions){

            $info = $this->api_call('get_tx_info', ['txid' => $req->txn_id]);

            if($info['error'] != 'ok'){
                \Mail::to($cp_debug_email)->send(new SendEmail([
                    'message' => date('Y-m-d H:i:s ') . $info['error']
                ]));
            }

            try {
                $transactions->update($info['result']);
            } catch (\Exception $e) {
                \Mail::to($cp_debug_email)->send(new SendEmail([
                    'message' => date('Y-m-d H:i:s ') . $e->getMessage()
                ]));
            }
            
            dispatch(new CoinpaymentListener(array_merge($transactions->toArray(), [
                'transaction_type' => 'old'
            ])));

        } else {
            if(!empty($cp_debug_email)) {
                \Mail::to($cp_debug_email)->send(new SendEmail([
                    'message' => 'Txn ID ' . $req->txn_id . ' not found from database ?'
                ]));
            }
        }
    }
}
