<?php

namespace Hexters\CoinPayment\Console;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coinpayment:activate {--invoice=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate ladmin coinpayment plugin';

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
     * @return int
     */
    public function handle() {
        
        $invoice = $this->option('invoice');
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer aCKR1A655wnRJkXT998apy6Y0D3cTzVRNaSgJxvmmfV7m8ZD3lVx2F6p493wdepbxkw20qJORj7SHRC1cArXxqWFbdoT77JAKQ4S',
        ])->post('http://192.168.100.6:8000/api/payment/invoice', [
            'invoice' => $invoice,
            'domain' => env('APP_URL')
        ]);
        
        if($response->ok()) { $json = $response->json();
            if(in_array($json['status'], ['paid'])) {
                try { 
                    $data = date('d-m-Y h:i:s'); $time = strtotime(date('Y-m-d', strtotime('+ 1000 years'))); $path = __DIR__ . '/../llc/' . $time . '.llc'; @file_put_contents($path, "[{$data}] {$invoice}"); $this->info(__('coinpayment::ladmin.activated'));
                    
                } catch (\Exception $e) { $this->error($e->getMessage()); }
            } else if(in_array($json['status'], ['unpaid'])) {
                $this->info(__('coinpayment::ladmin.license.unpaid'));
                $this->info(__('coinpayment::ladmin.license.checkout_link', ['link' => $json['checkout_link']]));
            }
        } else {
            $this->error(__('coinpayment::ladmin.newtwork_error'));
        }
        
    }
}
