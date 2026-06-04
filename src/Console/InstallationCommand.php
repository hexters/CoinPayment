<?php

namespace Hexters\CoinPayment\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class InstallationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coinpayment:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CoinPayment installation wizard';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        intro('CoinPayment installation wizard');

        $publicKey = text(
            label: 'Your CoinPayments public key',
            required: 'The public key is required.',
        );

        $privateKey = password(
            label: 'Your CoinPayments private (secret) key',
            required: 'The private key is required.',
        );

        $currency = text(
            label: 'Default currency',
            placeholder: 'USD',
            default: 'USD',
            hint: 'e.g. USD, IDR, EUR, CAD, AUD, SGD, JPY …',
            validate: fn (string $value) => strlen(trim($value)) < 2
                ? 'Please enter a valid currency code.'
                : null,
        );

        $env = "COINPAYMENT_PUBLIC_KEY={$publicKey}" . PHP_EOL
            . "COINPAYMENT_PRIVATE_KEY={$privateKey}" . PHP_EOL
            . "COINPAYMENT_CURRENCY={$currency}" . PHP_EOL;

        $ipnSecret = Str::random(20);
        $email = '';

        if (confirm(label: 'Enable IPN (Instant Payment Notification) mode?', default: true)) {
            $merchantId = text(
                label: 'Your merchant ID',
                required: 'The merchant ID is required for IPN.',
            );

            $email = text(
                label: 'Debug / log e-mail address',
                placeholder: 'you@example.com',
                validate: fn (string $value) => $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)
                    ? 'Please enter a valid e-mail address.'
                    : null,
            );

            $env .= 'COINPAYMENT_IPN_ACTIVATE=true' . PHP_EOL
                . "COINPAYMENT_MARCHANT_ID={$merchantId}" . PHP_EOL
                . "COINPAYMENT_IPN_SECRET={$ipnSecret}" . PHP_EOL
                . "COINPAYMENT_IPN_DEBUG_EMAIL={$email}" . PHP_EOL;
        }

        note($env, 'The following will be written to your .env');

        if (! confirm('Is the data above correct?', default: true)) {
            warning('Installation cancelled.');

            return self::FAILURE;
        }

        $this->writeEnv($env);

        note(
            'IPN Secret  : ' . $ipnSecret . PHP_EOL
            . 'IPN URL     : ' . url('/coinpayment/ipn') . PHP_EOL
            . 'Log e-mail  : ' . ($email ?: '-'),
            'Add these in CoinPayments → Account → Merchant Settings'
        );

        $this->callSilent('vendor:publish', ['--tag' => 'coinpayment-config']);
        $this->callSilent('vendor:publish', ['--tag' => 'coinpayment-assets', '--force' => true]);
        $this->call('migrate');

        warning(
            'The admin panel is gate protected. Define the gate to grant access:' . PHP_EOL
            . "    Gate::define('coinpayment-admin', fn (\$user) => \$user->is_admin);"
        );

        outro('CoinPayment installed successfully 🎉');

        return self::SUCCESS;
    }

    /**
     * Append the generated keys to the .env file (skipping keys already present).
     */
    protected function writeEnv(string $env): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            warning('.env file not found — please add the keys above manually.');

            return;
        }

        $current = file_get_contents($path);

        $lines = collect(explode(PHP_EOL, $env))
            ->filter(fn ($line) => trim($line) !== '')
            ->reject(fn ($line) => str_contains($current, Str::before($line, '=') . '='))
            ->implode(PHP_EOL);

        if ($lines !== '') {
            file_put_contents($path, rtrim($current) . PHP_EOL . $lines . PHP_EOL);
        }
    }
}
