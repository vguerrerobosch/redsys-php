<?php

namespace Vguerrerobosch\Redsys;

class WebhookUrlEncoded implements WebhookContentType
{
    public function getSignature($payload): string
    {
        return $payload['Ds_Signature'];;
    }

    public function getExpectedSignature($payload, $secret): string
    {

        $params = $payload['Ds_MerchantParameters'];

        $data = $this->getData($payload);

        $order = $data['Ds_Order'];

        $expectedSignature = Redsys::computeSignature($params, $order, $secret);

        return strtr($expectedSignature, '+/', '-_');
    }

    public function getData($payload): array
    {
        $data = $payload['Ds_MerchantParameters'];

        $data = strtr($data, '-_', '+/');
        $data = base64_decode($data);
        $data = urldecode($data);

        return json_decode($data, true);
    }

    public function response($order_id, $secret): string
    {
        return 'Webook handled';
    }
}
