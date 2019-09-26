**THIS PACKAGE IS STILL IN DEVELOPMENT, DO NOT USE YET**

# Redsys PHP bindings
![Packagist Version](https://img.shields.io/packagist/v/vguerrerobosch/redsys-php?style=flat-square)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
![Travis (.org)](https://img.shields.io/travis/vguerrerobosch/redsys-php)

This unofficial Redsys PHP library provides convenient access to the Redsys API from applications written in the PHP language.

## Requirements

PHP 5.6.0 and later.

## Composer

You can install the library via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require vguerrerobosch/redsys-php
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/Vguerrerobosch/redsys-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/redsys-php/init.php');
```

## Getting Started

### Payment Requests

You can create a payment request 

```php
use Vguerrerobosch\Redsys\Redsys;
use Vguerrerobosch\Redsys\PaymentRequest;

Redsys::setApiKey('sq7HjrUOBfKmC576ILgskD5srU870gJ7');

$payment_request = PaymentRequest::create([
    'amount' => 2000,
    'order' => microtime(),
    'merchant_code' => 999008881,
    'merchant_url' => 'https://941a2b9e.ngrok.io/webhook',
    'url_ok' => 'http://redsys-php.test/ok',
    'url_ko' => 'http://redsys-php.test/ko',
]);
```

then you can build the form like:

```php
$sumbit_onload = false; // default true

$payment_request->form($submit_onload);
```

or you may access the properties directly:

```php
$payment_request->url; // the Redsys endpoint
$payment_request->params; // the encoded parameters
$payment_request->signature; // the calculated signature
$payment_request->signature_version // currently HMAC_SHA256_V1
```

### Handling webhooks

The very first thing should be verifing the signature of the request:

```php
use Vguerrerobosch\Redsys\Webook as Webhook;
use Vguerrerobosch\Redsys\Exception\SignatureVerificationException;

$content_type = $_SERVER['CONTENT_TYPE'];

Webhook::setContentType($content_type); // defaults to application/x-www-form-urlencoded

$payload = $content_type == 'application/x-www-form-urlencoded' ?
    $_POST :
    @file_get_contents('php://input');

$secret_key = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

try {
    Webhook::verifySignature($payload, $secret_key);
} catch (SignatureVerificationException $exception) {
    http_response_code(403);
    die;
}
```

then get the data from the response payload and update the order status on your database or whatever needs to be done.

```php
$data = Webhook::getData($payload);
```

and finally return the response (required for SOAP)

```php
echo Webhook::response($order_id, $secret_key);
die;
```

## Testing
### Test card numbers
Genuine card information cannot be used in test mode. Instead, use the following test card numbers, a valid expiration date in the future, and any random CVC number, to create a successful payment.

Card number | Description
------------|------------
`4548 8120 4940 0004` | Visa charge succeeded.
`5576 4400 2278 8500` | MasterCard charge is declined with `9551` response code.
