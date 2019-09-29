<?php

namespace Vguerrerobosch\Redsys;

class CardBrands
{
    public static $brands = [
        1 => 'Visa',
        2 => 'MasterCard',
        6 => 'Diners',
        7 => 'Privada',
        8 => 'Amex',
        9 => 'JCB',
        22 => 'UPI',
    ];

    public static function find($code)
    {
        return static::$brands[$code] ?? $code;
    }
}
