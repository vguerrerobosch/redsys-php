<?php

namespace Vguerrerobosch\Redsys;

class WebhookUrlEncoded implements WebookContentType
{
    public function getExpectedSignature($payload, $secret)
    {
        $signature = $payload['Ds_Signature'];
        $params = $payload['Ds_MerchantParameters'];

        $data = $this->getData($payload);

        $order = $data['Ds_Order'];

        $expectedSignature = Redsys::computeSignature($params, $order, $secret);

        return strtr($expectedSignature, '+/', '-_');
    }

    public function getData(string $payload)
    {
        $data = $payload['Ds_MerchantParameters'];

        $data = strtr($data, '-_', '+/');
        $data = base64_decode($data);
        $data = urldecode($data);

        return json_decode($data, true);
    }

    public function reponse($order_id, $secret)
    {
        return 'Webook handled';
    }
}
