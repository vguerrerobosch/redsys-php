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
    public $url;
    public $params;
    public $signature;
    public $signature_version;

    protected function __construct($params)
    {
        $params = array_merge([
            'terminal' => 1,
            'currency' => 978,
            'transaction_type' => 0,
            'consumer_language' => 0,
        ], $params);

        foreach ($params as $key => $value) {
            unset($params[$key]);
            $key = 'Ds_Merchant_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $params[$key] = $value;
        }

        $this->params = base64_encode(json_encode($params));

        $this->signature = Redsys::computeSignature(
            $this->params,
            $params['Ds_Merchant_Order'],
            Redsys::getApiKey()
        );

        $this->signature_version = Redsys::VERSION;

        $this->url = Redsys::baseUrl() . '/sis/realizarPago';
    }

    public function form($submit = null)
    {
        $sumbit = isset($submit) ? $submit : true;

        $script = "<script>window.onload = function(){document.forms['redsys_payment_request_form'].submit();}</script>";

        return '<form action="' . $this->url . '" method="POST" name="redsys_payment_request_form"><input type="hidden" name="Ds_MerchantParameters" value="' . $this->params . '"/><input type="hidden" name="Ds_Signature" value="' . $this->signature . '"/><input type="hidden" name="Ds_SignatureVersion" value="' . $this->signature_version . '"/><input type="submit"></form>' . ($sumbit ? $script : '');
    }

    public static function create($params)
    {
        $obj = new static($params);

        return $obj;
    }
}
