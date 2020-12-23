<?php

namespace Hexters\CoinPayment\Http\Controllers;

use App\Jobs\CoinpaymentListener;

use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\CoinPayment;


class AjaxController extends CoinPaymentController {

    use ApiCallTrait;

    protected $helper;
    protected $model;

    public function __construct(CoinPaymentHelper $helper, CoinpaymentTransaction $model) {
        parent::__construct();
        $this->helper = $helper;
        $this->model = $model;
    }

    /**
     * Get supported rates from coin payment
     *
     * @return Json
     */
    public function rates($usd) {
        $rates = $this->api_call('rates', [
            'accepted' => 1
        ]);
        
        if(strtolower($rates['error']) == 'ok') {
            return $this->rates_formater($rates['result'], $usd);
        }

        return [
            'result' => false,
            'status' => $rates['error'],
            'error' => 'Fata error, cannot getting support coin from CoinPayments.'
        ];

    }

    /**
     * Make formated response
     *
     * @param [Array] $rates
     * @param [String] $usd
     * @return Array
     */
    protected function rates_formater(Array $rates, $usd) {
            
            if(!is_array($rates)){
                throw new Exception('The data must be an array');
            }

            if(empty($rates['BTC'])){
                throw new Exception('Rate BTC not found!, please activate BTC support coin for default coin rates.');
            }

            if(empty($rates[config('coinpayment.default_currency')])){
                throw new Exception('Is fiat ' . config('coinpayment.default_currency') . ' not supported. please contact CoinPayments support.');
            }

            /**
             * Get custom logo
             */
            $logos = config('coinpayment.logos', []);

            /**
             * Get default coin and fiat 
             */
            $btcRate = (FLOAT) $rates['BTC']['rate_btc'];
            $usdRate = (FLOAT) $rates[config('coinpayment.default_currency')]['rate_btc'];
            $rateAmount = $usdRate * (FLOAT) $usd;

            $fiat = [];
            $coins = [];
            $aliases = [];
            $coins_accept = [];
            foreach($rates as $coin => $value) {
                /**
                 * Get all crypto currencies
                 */
                if((INT) $value['is_fiat'] === 0){
                    $rate = $rates[$coin]['rate_btc'] > 0 ? ($rateAmount / $rates[$coin]['rate_btc']) : 0;

                    if(in_array($coin, ['BTC.LN'])) {
                        $img = 'BTCLN';
                    } else if(in_array($coin, ['USDT.ERC20'])) {
                        $img = 'USDT';
                    } else {
                        $img = $coin;
                    }

                    
                    $icon = $logos[$value['name']] ?? 'https://www.coinpayments.net/images/coins/' . $img . '.png';

                    $coins[] = [
                      'name' => $value['name'],
                      'amount' => $rate > 0 ? number_format($rate,8,'.','') : '-',
                      'iso' => $coin,
                      'icon' => $icon,
                      'selected' => $coin == 'BTC' ? true : false,
                      'accepted' => $value['accepted']
                    ];

                    /**
                     * Set all aliases coin
                     */
                    $aliases[$coin] = $value['name'];
                }

                /**
                 * Get accepted crypto currencies
                 */
                if((INT) $value['is_fiat'] === 0 && $value['accepted'] == 1){
                    $rate = $rates[$coin]['rate_btc'] > 0 ? ($rateAmount / $rates[$coin]['rate_btc']) : 0;

                    if(in_array($coin, ['BTC.LN'])) {
                        $img = 'BTCLN';
                    } else if(in_array($coin, ['USDT.ERC20'])) {
                        $img = 'USDT';
                    } else {
                        $img = $coin;
                    }

                    $icon = $logos[$value['name']] ?? 'https://www.coinpayments.net/images/coins/' . $img . '.png';
                    
                    $coins_accept[] = [
                        'name' => $value['name'],
                        'amount' => $rate > 0 ? number_format($rate,8,'.','') : '-',
                        'iso' => $coin,
                        'icon' => $icon,
                        'selected' => $coin == 'BTC' ? true : false,
                        'accepted' => $value['accepted']
                    ];
                }

                /**
                 * Get currencies
                 */
                if((INT) $value['is_fiat'] === 1){
                    $fiat[$coin] = $coin;
                }
            }

            return [
                'result' => true,
                'coins' => $coins,
                'accepted_coin' => $coins_accept,
                'aliases' => $aliases,
                'fiats' => $fiat
            ];
        
    }
    
    /**
     * Encrypted the payload string data
     *
     * @param Request $request
     * @return Json
     */
    public function encrypt_payload(Request $request) {

        try{

            if(empty($request->payload)) {
                throw new Exception("Payload data string cannot be null!");
            }

            /**
             * Get payload data
             */
            $payload = $this->helper->getrawtransaction($request->payload);
            
            /**
             * Get support currencies data
             */
            $rates = $this->rates($payload['amountTotal']);
            
            if(!$rates['result']) {
                throw new Exception($rates['status']);
            }
            /**
             * Default coin
             */
            $default_coin = $this->default_coin($rates);

            /**
             * Get config file
             */
            $config = config('coinpayment.header');

            /**
             * Get default currency
             */
            $default_currency = config('coinpayment.default_currency');
            
            return response()->json([
                'result' => true,
                'data' => [
                    'payload' => $payload,
                    'rates' => $rates,
                    'config' => $config,
                    'default_currency' => $default_currency,
                    'default_coin' => $default_coin,
                    'transaction' => $this->transaction_exists($payload['order_id'])
                ]
            ], 200);

        }catch(Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ], 400);
        }

    }

    private function transaction_exists($order_id) {
        $transaction = $this->model->whereNotNull('txn_id')->where('order_id', $order_id)->first();
        if($transaction) {
            return [
                'address' => $transaction->address,
                'amount' => $transaction->amount,
                'amountf' => $transaction->amountf,
                'coin' => $transaction->coin,
                'confirms_needed' => $transaction->confirms_needed,
                'payment_address' => $transaction->payment_address,
                'qrcode_url' => $transaction->qrcode_url,
                'received' => $transaction->received,
                'receivedf' => $transaction->receivedf,
                'recv_confirms' => $transaction->recv_confirms,
                'status' => $transaction->status,
                'status_text' => $transaction->status_text,
                'status_url' => $transaction->status_url,
                'timeout' => $transaction->timeout,
                'time_expires' => $transaction->time_expires,
                'txn_id' => $transaction->txn_id,
                'type' => $transaction->type,
                'payload' => $transaction->payload,
            ];
        }

        return null;

    }

    /**
     * Create transaction
     *
     * @param Request $request
     * @return Json
     */
    public function create_transaction(Request $request) {
        try{
            DB::beginTransaction();


            if(empty($request->amountTotal)){
                throw new Exception('Amount total not found!');
            }

            if(empty($request->coinAmount)){
                throw new Exception('Coin amount total not found!');
            }

            if(empty($request->coinIso)){
                throw new Exception('Type currency coin not found!');
            }

            if(empty($request->order_id)){
                throw new Exception('Order ID cannot be null, please fill it with invoice number or other');
            }

            $check_transaction = $this->model->where('order_id', $request->order_id)->whereNotNull('txn_id')->first();

            if($check_transaction) {
                throw new Exception('Order ID: ' . $check_transaction->order_id . ' already exists, and the current status is ' . $check_transaction->status_text);
            }

            $total = 0;
            foreach($request->items as $item) {
                $total += $item['itemSubtotalAmount'];
            }
            if($total != $request->amountTotal) {
                throw new Exception('the calculation of amountTotal and item total is different!');
            }


            $data = [
                'amount' => (FLOAT) $request->amountTotal,
                'currency1' => config('coinpayment.default_currency'),
                'currency2' => $request->coinIso,
                'buyer_email' => $request->buyer_email
            ];
            
            $create = $this->api_call('create_transaction', $data);
            if($create['error'] != 'ok'){
                throw new Exception($create['error']);
            }

            $info = $this->api_call('get_tx_info', ['txid' => $create['result']['txn_id']]);
            if($info['error'] != 'ok'){
                throw new Exception($info['error']);
            }
            $result = array_merge($create['result'], $info['result'], [
                'order_id' => $request->order_id,
                'amount_total_fiat' => $request->amountTotal,
                'payload' => $request->payload,
                'buyer_name' => $request->buyer_name ?? '-',
                'buyer_email' => $request->buyer_email ?? '-',
                'currency_code' => config('coinpayment.default_currency'),
                'redirect_url' => $request->redirect_url,
                'cancel_url' => $request->cancel_url,
                'checkout_url' => $request->checkout_url,
            ]);

            
            /**
             * Save to database
             */
            $transaction = $this->model->whereNull('txn_id')->where('order_id', $request->order_id)->first();
            if($transaction) {
                /**
                 * Update existing transaction
                 */
                $transaction->update($result);
            } else {

                /**
                 * Create new transaction
                 */
                $transaction = $this->model->create($result);

                /**
                 * Create item transaction
                 */
                
                foreach($request->items as $item) {
                    $transaction->items()->create([
                        'description' => is_object($item) ? $item->itemDescription : $item['itemDescription'],
                        'price' => is_object($item) ? $item->itemPrice : $item['itemPrice'],
                        'qty' => is_object($item) ? $item->itemQty : $item['itemQty'],
                        'subtotal' => is_object($item) ? $item->itemSubtotalAmount : $item['itemSubtotalAmount'],
                        'currency_code' => config('coinpayment.default_currency'),
                    ]);
                }
            }


            /**
             * Dispatching job
             */

            dispatch(new CoinpaymentListener(array_merge($result, [
                'transaction_type' => 'new'
            ])));

            DB::commit();
            return response()->json($result, 200);

        }catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    protected function default_coin($rates) {
        
        foreach($rates['coins'] as $rate) {
            if($rate['selected']) {
                return $rate;
            }
        }
    }

    /**
     * Get balance
     *
     * @return void
     */
    public function get_balance() {
        $response = CoinPayment::getBalances();
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
        $response = CoinPayment::getDepositAddress($request->currency);
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

        $response = CoinPayment::createWithdrawal($request->all());
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

        $response = CoinPayment::getWithdrawalInfo($id);

        if($response['error'] == 'ok') {
            return response()->json($result['result']);
        }

        return response()->json([
            'message' => $response['error'] ?? 'Request balance failed!'
        ], 400);
    }

}
