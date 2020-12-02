<?php

namespace Hexters\CoinPayment\Http\Controllers\Ladmin;

use App\Jobs\CoinpaymentListener;

use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Hexters\CoinPayment\Helpers\CoinPaymentFacade;


class AjaxController extends Controller {

    use ApiCallTrait;
  
    /**
     * Get balance
     *
     * @return void
     */
    public function get_balance() {
        $response = CoinPaymentFacade::getBalances();
        if($response['error'] == 'ok') {
            $blances = $response['result'];
            $coins = [];
            foreach($blances as $coin => $data) {
                $coins[] = [
                    'coin' => $coin,
                    'balance' => number_format($data['balance'], 2),
                    'balancef' => number_format($data['balancef'], 8),
                    'coin_status' => $data['coin_status'],
                    'status' => $data['status'],
                    'address' => '',
                    'withdrawal' => [
                        'amount' => $data['balancef'],
                        'add_tx_fee' => 0,
                        'currency' => $coin,
                        'currency2' => config('coinpayment.default_currency'),
                        'address' => '',
                        'pbntag' => '',
                        'note' => ''
                    ],
                    'loading' => false,
                    'icon' => 'https://www.coinpayments.net/images/coins/' . $coin . '.png'
                ];
            }

            return response()->json($coins, 200);
        }

        return response()->json([
            'message' => $response['error'] ?? 'Request balance failed!'
        ], 400);
    }

    public function top_up(Request $request) {

        if(config('ladmin')) {
            ladmin()->allow(['administrator.coinpayment.balances.topup']);
        }

        $request->validate([
            'currency' => ['required']
        ]);
        $response = CoinPaymentFacade::getDepositAddress($request->currency);
        if($response['error'] == 'ok') {
            return response()->json([
                'coin' => $request->currency,
                'address' => $response['result']['address']
            ]);
        }
        return response()->json([
            'message' => $response['error'] ?? 'Request balance failed!'
        ], 400);
    }

    public function create_withdrawal(Request $request) {

        if(config('ladmin')) {
            ladmin()->allow(['administrator.coinpayment.balances.withdrawal']);
        }

        $request->validate([
            'amount' => ['required', 'numeric'],
            'add_tx_fee' => ['required', 'numeric'],
            'address' => ['required'],
            'note' => ['nullable', 'max:60']
        ]);

        $response = CoinPaymentFacade::createWithdrawal($request->all());
        if($response['error'] == 'ok') {
            return response()->json($result['result']);
        }

        return response()->json([
            'message' => $response['error'] ?? 'Request balance failed!'
        ], 400);

    }

    public function get_withdrawal_info($id) {

        if(config('ladmin')) {
            ladmin()->allow(['administrator.coinpayment.withdrawal.show']);
        }

        $response = CoinPaymentFacade::getWithdrawalInfo($id);

        if($response['error'] == 'ok') {
            return response()->json($result['result']);
        }

        return response()->json([
            'message' => $response['error'] ?? 'Request balance failed!'
        ], 400);
    }

}
