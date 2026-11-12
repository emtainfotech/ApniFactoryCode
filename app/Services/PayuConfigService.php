<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayuConfigService
{
    /**
     * Known PayU sandbox/UAT merchant keys or placeholders that must never be treated as Production.
     */
    private const KNOWN_UAT_OR_PLACEHOLDER_KEYS = [
        'gtkffx',
        'jbzalc',
        'rjqupktu',
        'ks0wgv',
        'payu_key_placeholder',
        'payu_uat_key',
    ];

    private const KNOWN_PLACEHOLDER_SALTS = [
        'payu_salt_placeholder',
        '4r38iviwv57fwvpsgovtxbdle4thuxfw',
        '4r38ivwiv57fwvpsgovtxbdle4thuxfw',
        'ewiwqby5qj',
    ];

    /**
     * Default PayU UAT/Sandbox test credentials (used only until production credentials are added).
     */
    private const DEFAULT_UAT_KEY = 'gtKFFx';
    private const DEFAULT_UAT_SALT = '4R38IvwiV57FwVpsgOvTXBdLE4tHUXFW';

    /**
     * Resolve active PayU credentials & environment.
     * Automatically switches from UAT ('1') to Production ('0') as soon as valid
     * production credentials are configured in .env or Admin Profiles.
     */
    public function resolve(): array
    {
        $dbSettings = $this->loadDbProfileSettings();

        $explicitMode = strtolower(trim((string)(
            $dbSettings['payu_mode']
            ?? config('services.payu.mode')
            ?: env('PAYU_MODE', 'auto')
        )));

        $prodKey = trim((string)(
            $dbSettings['payu_prod_key']
            ?? config('services.payu.prod_key')
            ?: env('PAYU_PROD_KEY', '')
        ));
        $prodSalt = trim((string)(
            $dbSettings['payu_prod_salt']
            ?? config('services.payu.prod_salt')
            ?: env('PAYU_PROD_SALT', '')
        ));

        $genericKey = trim((string)(
            $dbSettings['payu_key']
            ?? $dbSettings['key']
            ?? config('services.payu.key')
            ?: env('PAYU_KEY', '')
        ));
        $genericSalt = trim((string)(
            $dbSettings['payu_salt']
            ?? $dbSettings['salt']
            ?? config('services.payu.salt')
            ?: env('PAYU_SALT', '')
        ));

        if (($prodKey === '' || $prodSalt === '') && $this->isLiveProductionPair($genericKey, $genericSalt)) {
            $prodKey = $genericKey;
            $prodSalt = $genericSalt;
        }

        $uatKey = trim((string)(
            $dbSettings['payu_uat_key']
            ?? config('services.payu.uat_key')
            ?: env('PAYU_UAT_KEY', '')
        ));
        $uatSalt = trim((string)(
            $dbSettings['payu_uat_salt']
            ?? config('services.payu.uat_salt')
            ?: env('PAYU_UAT_SALT', '')
        ));

        if ($uatKey === '' || $uatSalt === '') {
            if ($genericKey !== '' && $genericSalt !== '' && !$this->isLiveProductionPair($genericKey, $genericSalt)) {
                $uatKey = $genericKey;
                $uatSalt = $genericSalt;
            } else {
                $uatKey = self::DEFAULT_UAT_KEY;
                $uatSalt = self::DEFAULT_UAT_SALT;
            }
        }

        $hasProdCredentials = $this->isLiveProductionPair($prodKey, $prodSalt);

        $useProduction = match ($explicitMode) {
            'prod', 'production', 'live' => $hasProdCredentials || ($genericKey !== '' && $genericSalt !== ''),
            'uat', 'test', 'sandbox', 'staging' => false,
            default => $hasProdCredentials,
        };

        if ($useProduction) {
            $activeKey = $prodKey !== '' ? $prodKey : $genericKey;
            $activeSalt = $prodSalt !== '' ? $prodSalt : $genericSalt;
            return [
                'mode'         => 'production',
                'is_uat'       => false,
                'environment'  => '0', // PayU CheckoutPro '0' = Production
                'key'          => $activeKey,
                'salt'         => $activeSalt,
                'checkout_url' => 'https://secure.payu.in/_payment',
            ];
        }

        return [
            'mode'         => 'uat',
            'is_uat'       => true,
            'environment'  => '1', // PayU CheckoutPro '1' = Test / UAT
            'key'          => $uatKey,
            'salt'         => $uatSalt,
            'checkout_url' => 'https://test.payu.in/_payment',
        ];
    }

    /**
     * Compute PayU hash for SDK callbacks or standard payment initiation using active salt.
     */
    public function computeSdkHash(array $input): array
    {
        $cfg = $this->resolve();
        $salt = $cfg['salt'];
        $key = $cfg['key'];

        $hashName = (string)($input['hashName'] ?? $input['hash_name'] ?? 'payment_hash');
        $hashString = (string)($input['hashString'] ?? $input['hash_string'] ?? '');
        $hashType = (string)($input['hashType'] ?? $input['hash_type'] ?? '');
        $postSalt = (string)($input['postSalt'] ?? $input['post_salt'] ?? '');

        if ($hashString !== '') {
            if ($hashType === 'V2' || strcasecmp($hashType, 'hashVersionV2') === 0) {
                $hash = base64_encode(hash_hmac('sha256', $hashString, $salt, true));
            } elseif ($hashName === 'mcpLookup') {
                $hash = hash_hmac('sha1', $hashString, $key);
            } else {
                $hash = hash('sha512', $hashString . $salt . $postSalt);
            }

            return [
                'hash'             => $hash,
                'hash_name'        => $hashName,
                'mode'             => $cfg['mode'],
                'payu_environment' => $cfg['environment'],
                'is_uat'           => $cfg['is_uat'],
            ];
        }

        // Standard checkout hash parameters fallback
        $txnid = (string)($input['txnid'] ?? '');
        $amount = (string)($input['amount'] ?? '0');
        $productinfo = (string)($input['productinfo'] ?? '');
        $firstname = (string)($input['firstname'] ?? '');
        $email = (string)($input['email'] ?? '');
        $udf1 = (string)($input['udf1'] ?? '');
        $udf2 = (string)($input['udf2'] ?? '');
        $udf3 = (string)($input['udf3'] ?? '');
        $udf4 = (string)($input['udf4'] ?? '');
        $udf5 = (string)($input['udf5'] ?? '');

        $raw = "{$key}|{$txnid}|{$amount}|{$productinfo}|{$firstname}|{$email}|{$udf1}|{$udf2}|{$udf3}|{$udf4}|{$udf5}||||||{$salt}";
        $hash = hash('sha512', $raw);

        return [
            'hash'             => $hash,
            'hash_name'        => $hashName,
            'mode'             => $cfg['mode'],
            'payu_environment' => $cfg['environment'],
            'is_uat'           => $cfg['is_uat'],
        ];
    }

    private function isLiveProductionPair(string $key, string $salt): bool
    {
        if ($key === '' || $salt === '') {
            return false;
        }
        if (in_array(strtolower($key), self::KNOWN_UAT_OR_PLACEHOLDER_KEYS, true)) {
            return false;
        }
        if (in_array(strtolower($salt), self::KNOWN_PLACEHOLDER_SALTS, true)) {
            return false;
        }
        return !str_contains(strtolower($key), 'placeholder')
            && !str_contains(strtolower($salt), 'placeholder');
    }

    private function loadDbProfileSettings(): array
    {
        try {
            if (!Schema::hasTable('profiles')) {
                return [];
            }
            return DB::table('profiles')
                ->whereIn('attribute', [
                    'payu_mode',
                    'payu_prod_key',
                    'payu_prod_salt',
                    'payu_uat_key',
                    'payu_uat_salt',
                    'payu_key',
                    'payu_salt',
                    'key',
                    'salt',
                ])
                ->pluck('value', 'attribute')
                ->mapWithKeys(fn ($val, $attr) => [strtolower((string)$attr) => $val])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
