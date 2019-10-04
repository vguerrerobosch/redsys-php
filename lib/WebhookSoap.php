<?php

namespace Vguerrerobosch\Redsys;

class WebhookSoap implements WebhookContentType
{
    public function getSignature($payload): string
    {
        if (!preg_match("/<Signature>(.*)<\/Signature>/", $payload, $signature)) {
            throw new \Exception('Invalid payload');
        }

        return $signature[1];
    }

    public function getExpectedSignature($payload, $secret): string
    {
        if (!preg_match("/<Request.*>(.*)<\/Request>/", $payload, $params) ||
            !preg_match("/<Ds_Order>(.*)<\/Ds_Order>/", $payload, $order)) {
            throw new \Exception('Invalid payload');
        }

        $params = $params[0];
        $order = $order[1];

        return Redsys::computeSignature($params, $order, $secret);
    }

    public function getData($payload): array
    {
        if (!preg_match('/<!\[CDATA\[(.*)\]\]>/', $payload, $matches)) {
            throw new \Exception('Invalid payload');
        }

        $xml = simplexml_load_string($matches[1]);

        $data = json_decode(json_encode($xml->Request), true);

        $data['date'] = $data['Fecha'];
        $data['hour'] = $data['Hora'];

        unset($data['Fecha']);
        unset($data['Hora']);
        unset($data['@attributes']);

        return $data;
    }

    public function response($order_id, $secret): string
    {
        $response = '<Response Ds_Version="0.0"><Ds_Response_Merchant>OK</Ds_Response_Merchant></Response>';

        $signature = Redsys::computeSignature($response, $order_id, $secret);

        $message = "<Message>$response<Signature>$signature</Signature></Message>";

        $message = htmlentities($message, ENT_NOQUOTES);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"/WebServiceSIS\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"><SOAP-ENV:Body><ns1:procesaNotificacionSISResponse><return xsi:type=\"xsd:string\">$message</return></ns1:procesaNotificacionSISResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>'\n";
    }
}
