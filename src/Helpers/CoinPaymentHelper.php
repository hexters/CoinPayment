<?php 

namespace Hexters\CoinPayment\Helpers;

use App\Jobs\CoinpaymentListener;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Illuminate\Support\Facades\Crypt;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;

class CoinPaymentHelper {

	use ApiCallTrait;

	public function generatelink($array) {
		if(!is_array($array)){
			return "Format data is wrong, data format must be an array.";
		}
		return url('/coinpayment/make/' . $this->transaction_encrypt($array));
	}
	
	public function getrawtransaction($string) {
		return $this->transaction_dencrypt($string);
	}

	protected function transaction_encrypt(Array $array) {
		return Crypt::encryptString(serialize($array));
	}
	
	protected function transaction_dencrypt(String $string) {
		return unserialize(Crypt::decryptString($string));
	}

	public function getstatusbytxnid($txn_id) {
		try {
			$status = $this->api_call('get_tx_info', ['txid' => $txn_id]);
			if($status['error'] != 'ok') {
				throw new \Exception($status['error']);
			}

			$transactions = CoinpaymentTransaction::where('txn_id', $txn_id)->first();
			if(is_null($transactions)) {
				throw new \Exception('Ilegal! Transaction not found from database');
			}

			$transactions->update($status['result']);
				
			dispatch(new CoinpaymentListener(array_merge($transactions->toArray(), [
				'transaction_type' => 'old'
			])));

			return [
				'status_text' => $status['result']['status_text'],
				'status' => $status['result']['status']
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function gettransactions() {
		return new CoinpaymentTransaction;
	}

}