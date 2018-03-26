<?php

namespace Hexters\CoinPayment\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Hexters\CoinPayment\Jobs\webhookProccessJob;
use App\Jobs\coinPaymentCallbackProccedJob;

use Hexters\CoinPayment\Entities\cointpayment_log_trx as logs;
use Carbon\Carbon;
use CoinPayment;

use Route;

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
        $this->info('======================= CHECKING STARTING =======================');
        logs::whereIn('status', [0, 1])->where('expired', '>', Carbon::now())
          ->chunk(100, function($transactions){
            foreach($transactions as $trx){
              $this->async_proccess($trx, function($check) use ($trx){

                if($check['error'] == 'ok'){
                  $data = $check['result'];
                  $this->info('Address: ' . $data['payment_address']);
                  if($data['status'] > 0 || $data['status'] < 0){
                    $trx->update([
                      'status_text' => $data['status_text'],
                      'status' => $data['status'],
                      'confirmation_at' => ((INT) $data['status'] === 100) ? date('Y-m-d H:i:s', $data['time_completed']) : null
                    ]);

                    $this->info('Status : ' . $data['status_text']);

                    // Send hook
                    $this->info('======================= SENDING WEBHOOK =======================');
                    $data['payload'] = (Array) json_decode($trx->payload, true);
                    $data['request_type'] = 'schedule_transaction';
                    if(Route::has('coinpayment.webhook')){
                      dispatch(new webhookProccessJob($data));
                    }
                    dispatch(new coinPaymentCallbackProccedJob($data));
                  }
                }
              });
            }
          });
    }

    private function async_proccess($trx, $callback){
      $check = CoinPayment::api_call('get_tx_info', [
        'txid' => $trx->payment_id
      ]);
      return $callback($check);
    }
}
