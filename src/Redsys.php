<?php

namespace App\Billing\Redsys;

class Redsys
{
    public static $apiKey;
    public static $live_url = 'https://sis.redsys.es';
    public static $test_url = 'https://sis-t.redsys.es:25443';

    const VERSION = 'HMAC_SHA256_V1';
    const TEST_SECRET_KEY = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

    public static function isTest()
    {
        return self::$apiKey == self::TEST_SECRET_KEY;
    }

    public static function baseUrl()
    {
        return self::isTest() ? self::$test_url : self::$live_url;
    }

    public static function getApiKey()
    {
        return self::$apiKey;
    }

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    /**
     * Computes the signature for a given payload and secret.
     *
     * @param string $payload the payload to sign.
     * @param string $order_id the Ds_Order paramater used to generate the key
     * @param string $secret the secret used to generate the signature.
     * @return string the signature as a string.
     */
    public static function computeSignature($payload, $order_id, $secret)
    {
        $key = self::generateKey($order_id, $secret);

        $signature = hash_hmac('sha256', $payload, $key, true);

        return base64_encode($signature);
    }

    public static function generateKey($order_id, $secret)
    {
        $key = base64_decode($secret);

        $l = ceil(strlen($order_id) / 8) * 8;

        return substr(openssl_encrypt($order_id . str_repeat("\0", $l - strlen($order_id)), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"), 0, $l);
    }
}
