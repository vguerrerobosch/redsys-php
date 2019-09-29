<?php

namespace Vguerrerobosch\Redsys;

class CardBrands
{
    public static $brands = [
        1 => 'visa',
        2 => 'mastercard',
        6 => 'diners',
        7 => 'privada',
        8 => 'amex',
        9 => 'jcb',
        22 => 'upi',
    ];

    public static function find($code)
    {
        return static::$brands[$code] ?? $code;
    }
}
