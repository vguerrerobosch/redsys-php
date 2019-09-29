<?php

namespace Vguerrerobosch\Redsys;

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
        $expectedSignature = self::$content_type->getExpectedSignature($payload, $secret);

        if ($signature != $expectedSignature) {
            throw new Exception\SignatureVerificationException;
        }

        return true;
    }

    public static function getData($payload)
    {
        return self::$content_type->getData($payload);
    }

    public static function response($order_id = null, $secret = null)
    {
        return self::$content_type->getData($payload);
    }
}
