<?php 

namespace Hexters\CoinPayment\Helpers;

use App\Jobs\CoinpaymentListener;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Illuminate\Support\Facades\Crypt;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;

class CoinPaymentHelper {

	use ApiCallTrait;

	/**
	 * Generate link payment
	 *
	 * @param [type] $array
	 * @return void
	 */
	public function generatelink($array) {
		if(!is_array($array)){
			return "Format data is wrong, data format must be an array.";
		}
		return url('/coinpayment/make/' . $this->transaction_encrypt($array));
	}
	
	/**
	 * Get raw transaction
	 *
	 * @param [type] $string
	 * @return void
	 */
	public function getrawtransaction($string) {
		return $this->transaction_dencrypt($string);
	}

	/**
	 * Encrypted transaction data
	 *
	 * @param Array $array
	 * @return void
	 */
	protected function transaction_encrypt(Array $array) {
		return Crypt::encryptString(serialize($array));
	}
	
	/**
	 * Decripted transaction data
	 *
	 * @param String $string
	 * @return void
	 */
	protected function transaction_dencrypt(String $string) {
		return unserialize(Crypt::decryptString($string));
	}

	/**
	 * Get status by txn ID
	 *
	 * @param [type] $txn_id
	 * @return void
	 */
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

	/**
	 * Get balances
	 *
	 * @return void
	 */
	public function getBalances() {
		return $this->api_call('balances');
	}

	/**
	 * Return to eloquent model transactions
	 *
	 * @return void
	 */
	public function gettransactions() {
		return new CoinpaymentTransaction;
	}

	/**
	 * Geherated top up address
	 *
	 * @param [type] $currency
	 * @return void
	 */
	public function getDepositAddress($currency) {
		return $this->api_call('get_deposit_address', [
			'currency' => $currency
		]);
	}

	/**
	 * Create new withdrawal
	 *
	 * @param Array $body
	 * @return void
	 */
	public function createWithdrawal(Array $body) {
		return $this->api_call('create_withdrawal', $body);
	}

	/**
	 * get withdrawal info
	 *
	 * @param String $body
	 * @return void
	 */
	public function getWithdrawalInfo($id) {
		return $this->api_call('get_withdrawal_info', $id);
	}

}