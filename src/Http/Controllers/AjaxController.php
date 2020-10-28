<?php

namespace Hexters\CoinPayment\Http\Controllers;

use App\Jobs\CoinpaymentListener;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;

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
                throw new \Exception($rates['status']);
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
                    'default_coin' => $default_coin
                ]
            ], 200);

        }catch(Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage()
            ], 400);
        }

    }

    /**
     * Create transaction
     *
     * @param Request $request
     * @return Json
     */
    public function create_transaction(Request $request) {
        try{

            if(empty($request->amountTotal)){
                throw new Exception('Amount total not found!');
            }

            if(empty($request->coinAmount)){
                throw new Exception('Coin amount total not found!');
            }

            if(empty($request->coinIso)){
                throw new Exception('Type currency coin not found!');
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
                'payload' => $request->payload
            ]);

            /**
             * Save to database
             */
            $this->model->create($result);

            /**
             * Dispatching job
             */

            dispatch(new CoinpaymentListener(array_merge($result, [
                'transaction_type' => 'new'
            ])));

            return response()->json($result, 200);

        }catch(Exception $e) {
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

}
