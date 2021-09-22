<?php

namespace Vguerrerobosch\Redsys;

use Alcohol\ISO4217;
use League\ISO3166\Exception\InvalidArgumentException;
use League\ISO3166\Exception\OutOfBoundsException;
use League\ISO3166\ISO3166;
use Vguerrerobosch\Redsys\WebhookSoap;
use Vguerrerobosch\Redsys\WebhookUrlEncoded;

class Webhook
{
    public static $content_type;

    public static function setContentType($content_type)
    {
        switch ($content_type) {
            case 'application/x-www-form-urlencoded':
                self::$content_type = new WebhookUrlEncoded;
                break;
            case 'text/xml; charset=utf-8':
                self::$content_type = new WebhookSoap;
                break;
            default:
                throw new \Exception('Not supported content type');
        }
    }

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
        $signature = self::$content_type->getSignature($payload);

        $expectedSignature = self::$content_type->getExpectedSignature($payload, $secret);

        if ($signature != $expectedSignature) {
            throw new Exception\SignatureVerificationException;
        }

        return true;
    }

    public static function getData($payload)
    {
        $data = self::$content_type->getData($payload);

        foreach ($data as $key => $value) {
            unset($data[$key]);
            $key = str_replace('Ds_', '', $key);
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $key = str_replace('__', '_', $key);

            $value = is_array($value) ? array_filter(array_map('trim', $value)) : $value;
            $value = is_array($value) && empty($value) ? null : $value;
            $value = is_string($value) ? trim($value) : $value;
            $value = $value === '' ? null : $value;

            $data[$key] = $value;
        }

        $created_at = \DateTime::createFromFormat('d/m/Y H:i', "{$data['date']} {$data['hour']}");
        $data['created_at'] = $created_at->format('Y-m-d H:i:s');
        unset($data['date']);
        unset($data['hour']);

        $data['amount'] = (int) $data['amount'];

        $currency = (new ISO4217())->getByNumeric($data['currency']);
        $data['currency'] = strtolower($currency['alpha3']);

        $data['card_country'] = str_pad($data['card_country'] ?? null, 3, '0', STR_PAD_LEFT);

        try {
            $country = (new ISO3166)->numeric($data['card_country']);
            $data['card_country'] = strtolower($country['alpha2']);
        } catch (OutOfBoundsException | InvalidArgumentException $e) {
            $data['card_country'] = null;
        }

        $data['card_brand'] = CardBrands::find($data['card_brand'] ?? null);
        $data['card_number'] = $data['card_number'] ?? null;

        $data['secure_payment'] = (bool) $data['secure_payment'];
        $data['response'] = (int) $data['response'];
        $data['merchant_code'] = (int) $data['merchant_code'];
        $data['terminal'] = (int) $data['terminal'];

        $data['error_code'] = $data['error_code'] ?? null;

        if ($data['response'] >= 0 && $data['response'] <= 99) {
            $data['status'] = 'succeeded';
        } else {
            $data['status'] = 'failed';
        }

        ksort($data);

        return $data;
    }

    public static function response($order_id = null, $secret = null)
    {
        return self::$content_type->response($order_id, $secret);
    }
}
