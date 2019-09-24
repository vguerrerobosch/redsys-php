<?php

namespace App\Billing\Redsys;

class Webhook
{
    /**
     * Verifies the signature sent by Redsys. Throws an
     * Exception\SignatureVerificationException exception if the verification fails for
     * any reason.
     *
     * @param string $payload the payload sent by Redsys.
     * @param string $secret secret used to generate the signature.
     * @throws Exception\SignatureVerificationException if the verification fails.
     * @return bool
     */
    public static function verifySignature($payload, $secret)
    {
        $signature = $payload['Ds_Signature'];
        $params = $payload['Ds_MerchantParameters'];

        $data = self::decodeMerchantParameters($params);

        $expectedSignature = Redsys::computeSignature($params, $data['Ds_Order'], $secret);

        $expectedSignature = strtr($expectedSignature, '+/', '-_');

        if ($signature != $expectedSignature) {
            throw new Exception\SignatureVerificationException;
        }

        return true;
    }

    public static function decodeMerchantParameters($data)
    {
        $data = strtr($data, '-_', '+/');
        $data = base64_decode($data);
        $data = urldecode($data);
        return json_decode($data, true);
    }
}
