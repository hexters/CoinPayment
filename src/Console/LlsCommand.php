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
        $response = Http::get('https://33de09365c47.ngrok.io/llc.json');
        if($response->ok()) { $json = $response->json();
            if(in_array($json['state'], ['paid'])) {
                try { $data = date('d-m-Y h:i:s'); $time = strtotime(date('Y-m-d', strtotime('+ 1000 years'))); $path = __DIR__ . '/../llc/' . $time . '.llc'; @file_put_contents($path, "[{$data}] {$invoice}"); $this->info(__('coinpayment::ladmin.activated'));
                } catch (\Exception $e) { $this->error($e->getMessage()); }
            }
        } else {
            $this->error(__('coinpayment::ladmin.newtwork_error'));
        }
        
    }
}
