<?php

namespace Vguerrerobosch\Redsys\Test;

use PHPUnit\Framework\TestCase;
use Vguerrerobosch\Redsys\Redsys;
use Vguerrerobosch\Redsys\PaymentRequest;
use Vguerrerobosch\Redsys\Exception\InvalidPaymentRequestException;


class RedsysTest extends TestCase
{
    /** @test */
    public function amount_is_required()
    {
        $this->expectException(InvalidPaymentRequestException::class);

        PaymentRequest::create([]);
    }

    /** @test */
    public function amount_is_positive_integer()
    {
        $this->expectException(InvalidPaymentRequestException::class);

        PaymentRequest::create([
            'amount' => '0',
        ]);
    } 
}

