<?php

namespace Hexters\CoinPayment\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Hexters\CoinPayment\Entities\cointpayment_log_trx as logs;
use Carbon\Carbon;
use CoinPayment;

class chekcingTransactionCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'coinpayment:transaction-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking transaction refund';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        // Parameter hook
        logs::where('status', 0)->where('expired', '>', Carbon::now())
          ->chunk(100, function($transactions){
            foreach($transactions as $trx){
              $check = CoinPayment::api_call('get_tx_info', [
                'txid' => $trx->payment_id
              ]);
              if($check['error'] == 'ok'){
                $data = $check['result'];
                if($data['status'] > 0){
                  $trx->update([
                    'status_text' => $data['status_text'],
                    'status' => $data['status']
                  ]);

                  // Send hook
                  $query = http_build_query($data);
                  $client = new \GuzzleHttp\Client();
                  $client->request('GET', route('coinpayment.web.hook') . '?' . $query);
                  /* Response Hook
                    {
                      "time_created": "1521771495",
                      "time_expires": "1521868695",
                      "status": "0",
                      "status_text": "Waiting for buyer funds...",
                      "type": "coins",
                      "coin": "BTC",
                      "amount": "581190",
                      "amountf": "0.00581190",
                      "received": "0",
                      "receivedf": "0.00000000",
                      "recv_confirms": "0",
                      "payment_address": "3HAkz2LBdN6W3GzuoY22PuTMtcp6Z5QKCg"
                    }
                  */
                }
              }
            }
          });
    }
}
