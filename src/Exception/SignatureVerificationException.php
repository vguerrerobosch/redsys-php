<?php

namespace App\Billing\Redsys\Exception;

class SignatureVerificationException extends \Exception
{
    protected $message = 'Failed to verify signature';
}
