<?php

namespace Vguerrerobosch\Redsys;

interface WebhookContentType
{
    public function getSignature($payload): string;
    public function getExpectedSignature($payload, $secret): string;
    public function getData($payload): array;
    public function response($order_id, $secret): string;
}
