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
    protected $signature = 'coinpayment:activate {--key=}';

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
        
        $key = $this->option('key');
        $response = Http::get('https://0bdd6e158dd0.ngrok.io/llc.json');
        if($response->ok()) {
            $json = $response->json();
            if(in_array($json['state'], ['paid'])) {
                $lls = time() . '.lls';
                $path = __DIR__ . '/../' . $lls;
                @file_put_contents($path, date());
            }
        }
        
    }
}
