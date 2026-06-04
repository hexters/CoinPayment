<?php

namespace Hexters\CoinPayment\Licensing;

/**
 * Offline activation gate for the package's premium features.
 *
 * Locked only on live environments (production/staging with APP_DEBUG=false);
 * local/dev always runs unlocked so development is never blocked. Activation
 * happens on that server — a valid serial writes a token (bound to the app's
 * APP_KEY) to storage so it survives deploys and cannot be copied to another
 * installation.
 */
final class License
{
    /** Environments where the gate is enforced. Intentionally hardcoded. */
    private const ENVIRONMENTS = ['production', 'staging'];

    /** Salt mixed into the serial before hashing. */
    private const S = 'Hx\\CoinPayment::lic::v4::s@lt::9f3';

    /** sha256(S . UPPER(serial)) of the valid serials. */
    private const K = [
        'e9f4852190266154983f9f453b97adb88227382e0324dccdcd799288c1115c09',
        '1b11b1c203dc79cf15169b38ece1cb43956d63bb5ce54b2ab878fa80f4f8df06',
        '5035db7d7a7edac1ef2a82171dbd90596cd32d676a550e3f26baea19c1bd13cb',
    ];

    /** Where buyers obtain a serial. */
    public const PURCHASE_URL = 'https://buymeacoffee.com/hexters/e/545129';

    /** Coins still allowed on the checkout while unlicensed. */
    public const FREE_COINS = ['BTC', 'LTCT'];

    /** Is the gate enforced for the current environment? */
    public static function enforced(): bool
    {
        return in_array(app()->environment(), self::ENVIRONMENTS, true)
            && config('app.debug') === false;
    }

    /** True when premium features must be blocked. */
    public static function locked(): bool
    {
        return self::enforced() && ! self::valid();
    }

    public static function active(): bool
    {
        return ! self::locked();
    }

    /** Attempt activation with a serial; writes the token on success. */
    public static function activate(string $serial): bool
    {
        $hash = self::fingerprint($serial);

        if (! in_array($hash, self::K, true)) {
            return false;
        }

        $path = self::path();
        @mkdir(dirname($path), 0775, true);

        return (bool) @file_put_contents($path, self::token($hash));
    }

    /** Does a valid, app-bound license token exist on disk? */
    private static function valid(): bool
    {
        $path = self::path();

        if (! is_file($path)) {
            return false;
        }

        $stored = trim((string) @file_get_contents($path));

        foreach (self::K as $hash) {
            if (hash_equals(self::token($hash), $stored)) {
                return true;
            }
        }

        return false;
    }

    private static function fingerprint(string $serial): string
    {
        return hash('sha256', self::S . strtoupper(trim($serial)));
    }

    private static function token(string $hash): string
    {
        return hash_hmac('sha256', $hash, (string) config('app.key'));
    }

    private static function path(): string
    {
        return storage_path('app/coinpayment/.licensed');
    }
}
