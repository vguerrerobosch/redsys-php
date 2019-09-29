<?php

namespace Vguerrerobosch\Redsys;

interface WebhookContentType
{
    public function getData(string $payload): array;

    public function response($order_id, $secret): string;
}
