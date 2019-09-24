<?php

namespace Vguerrerobosch\Redsys;

/**
 * Class Redsys
 *
 * @property $merchant_code
 * @property $terminal
 * @property $merchant_url
 * @property $currency
 * @property $transaction_type
 * @property $order
 * @property $payment_methods
 * @property $product_description
 * @property $titular
 * @property $url_ok
 * @property $url_ko
 * @property $merchant_name
 * @property $consumer_language
 * @property $merchant_data
 * @property $transaction_date
 * @property $identifier
 * @property $group
 * @property $direct_payment
 */
class PaymentRequest
{
    public static function create($params)
    {
        $params = array_merge([
            'terminal' => 1,
            'currency' => 978,
            'transaction_type' => 0,
            'consumer_language' => 0,
        ], $params);

        foreach ($params as $key => $value) {
            $key = 'Ds_Merchant_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $payload[$key] = $value;
        }

        $payload = base64_encode(json_encode($payload));

        $signature = Redsys::computeSignature(
            $payload,
            $params['order'],
            Redsys::getApiKey()
        );

        return [
            'url' => Redsys::baseUrl() . '/sis/realizarPago',
            'params' => $payload,
            'signature' => $signature,
            'signature_version' => Redsys::VERSION,
        ];
    }
}
