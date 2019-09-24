<?php

namespace Vguerrerobosch\Redsys;

class Webhook
{
    public static $content_type = 'application/x-www-form-urlencoded';
    
    public static function setContentType($content_type)
    {
        self::$content_type = $content_type;
    }

    public static function getContentType()
    {
        return self::$content_type;
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
        if (self::$content_type == 'application/x-www-form-urlencoded') {
            $signature = $payload['Ds_Signature'];
            $params = $payload['Ds_MerchantParameters'];
    
            $data = self::decodeMerchantParameters($params);

            $order = $data['Ds_Order'];
        } elseif (self::$content_type == 'text/xml; charset=utf-8') {
            if (!preg_match("/<Request.*>(.*)<\/Request>/", $payload, $params) ||
                !preg_match("/<Ds_Order>(.*)<\/Ds_Order>/", $payload, $order) ||
                !preg_match("/<Signature>(.*)<\/Signature>/", $payload, $signature)) {
                throw new \Exception('Invalid payload');
            }

            $params = $params[0];
            $order = $order[1];
            $signature = $signature[1];
        } else {
            throw new Exception\SignatureVerificationException;
        }

        $expectedSignature = Redsys::computeSignature($params, $order, $secret);

        if (self::$content_type == 'application/x-www-form-urlencoded') {
            $expectedSignature = strtr($expectedSignature, '+/', '-_');
        }

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

    public static function xmlToArray($payload)
    {
        if (!preg_match('/<!\[CDATA\[(.*)\]\]>/', $payload, $matches)) {
            throw new \Exception('Invalid payload');
        }

        $xml = simplexml_load_string($matches[1]);

        return json_decode(json_encode($xml->Request), true);
    }

    public function getData($payload)
    {
        if (self::$content_type == 'application/x-www-form-urlencoded') {
            return self::decodeMerchantParameters($payload['Ds_MerchantParameters']);
        } elseif (self::$content_type == 'text/xml; charset=utf-8') {
            return self::xmlToArray($payload);
        } else {
            throw new \Exception('Not supported Content Type');
        }
    }

    public static function response($order_id = null, $secret = null)
    {
        if (self::$content_type == 'text/xml; charset=utf-8') {
            $response = self::createSoapResponse($order_id, $secret);

            header('Content-type', 'text/xml; charset="utf-8"');
            header('Cache-Control', 'no-cache, must-revalidate');
            header('Content-length', strlen($response));

            return $response;
        }

        return 'Webook handled';
    }

    public static function createSoapResponse($order_id, $secret)
    {
        $response = '<Response Ds_Version="0.0"><Ds_Response_Merchant>OK</Ds_Response_Merchant></Response>';

        $signature = Redsys::computeSignature($response, $order_id, $secret);

        $message = "<Message>$response<Signature>$signature</Signature></Message>";

        $message = htmlentities($message, ENT_NOQUOTES);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"/WebServiceSIS\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"><SOAP-ENV:Body><ns1:procesaNotificacionSISResponse><return xsi:type=\"xsd:string\">$message</return></ns1:procesaNotificacionSISResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>'\n";
    }
}
