<?php

namespace Vguerrerobosch\Redsys;

class WebhookSoap
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
        if (!preg_match("/<Request.*>(.*)<\/Request>/", $payload, $request) ||
            !preg_match("/<Ds_Order>(.*)<\/Ds_Order>/", $payload, $order) ||
            !preg_match("/<Signature>(.*)<\/Signature>/", $payload, $signature)) {
            throw new \Exception('Invalid payload');
        }

        $request = $request[0];
        $order = $order[1];
        $signature = $signature[1];

        $expectedSignature = Redsys::computeSignature($request, $order, $secret);

        if ($signature != $expectedSignature) {
            throw new Exception\SignatureVerificationException;
        }

        return true;
    }

    public static function getData($payload)
    {
        if (!preg_match('/<!\[CDATA\[(.*)\]\]>/', $payload, $matches)) {
            throw new \Exception('Invalid payload');
        }

        $xml = simplexml_load_string($matches[1]);

        return json_decode(json_encode($xml->Request), true);
    }

    public static function createSoapResponse($secret, $order_id)
    {
        $response = '<Response Ds_Version="0.0"><Ds_Response_Merchant>OK</Ds_Response_Merchant></Response>';

        $signature = Redsys::computeSignature($response, $order_id, $secret);

        $message = "<Message>$response<Signature>$signature</Signature></Message>";

        $message = htmlentities($message, ENT_NOQUOTES);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"/WebServiceSIS\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"><SOAP-ENV:Body><ns1:procesaNotificacionSISResponse><return xsi:type=\"xsd:string\">$message</return></ns1:procesaNotificacionSISResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>'\n";
    }
}
