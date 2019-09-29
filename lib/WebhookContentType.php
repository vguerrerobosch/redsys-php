<?php

namespace Vguerrerobosch\Redsys;

interface WebhookContentType
{
    public function getExpectedSignature($payload, $secret): string;
    public function getData($payload): array;
    public function response($order_id, $secret): string;
}
