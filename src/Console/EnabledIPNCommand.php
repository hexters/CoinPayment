<?php

namespace Hexters\CoinPayment\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class EnabledIPNCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'coinpayment:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installation of coinpayment';

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

      $ipn = '';
      $path = base_path('.env');
      $ipn_secret = str_random(20);
      $email = '';
      $marchatid = '';

      $this->line(PHP_EOL);
      $this->line('1. Configuration .env file.');
      $public_key = $this->ask('Please insert Public Key ?');
      $secret_key = $this->ask('Please insert Secret Key ?');

      $this->line('2. Activated IPN for webhook handle proccess.');
      $confirm = $this->confirm('Do you want to enable IPN mode ?');
      
      if($confirm){
        $marchatid = $this->ask('Please insert merchant ID ?');
        $email = $this->ask('Please insert email address for debuging ?');

        $ipn  = 'COIN_PAYMENT_MARCHANT_ID=' . $marchatid . PHP_EOL
              . 'COIN_PAYMENT_IPN_SECRET=' . $ipn_secret . PHP_EOL
              . 'COIN_PAYMENT_IPN_DEBUG_EMAIL=' . $email;
      }

      $env  = 'COIN_PAYMENT_PUBLIC_KEY=' . $public_key . PHP_EOL
            . 'COIN_PAYMENT_PRIVATE_KEY=' . $secret_key . PHP_EOL
            . $ipn;

      $this->line($env);
      if($this->confirm('Is your data correct?')){
        if (file_exists($path)) {
            $file = file_get_contents($path);
            file_put_contents($path, $file . PHP_EOL . $env);
        }
        $this->line(PHP_EOL);
        $this->line('================================================================================================================================================');
        $this->info('Please visit CoinPayment site, or open this url https://www.coinpayments.net/acct-settings got to > Merchant Settings tab And paste data below!');
        $this->line(PHP_EOL);
        $this->line('IPN Secret         : ' . $ipn_secret);
        $this->line('IPN URL            : ' . url('/coinpayment/ipn'));
        $this->line('Status/Log Email   : ' . $email);
        $this->line(PHP_EOL);
        $this->info('Instalation Finish');
        $this->line('================================================================================================================================================');

      }else{
        $this->error('Instalation Canceled!');
      }
    }
}
