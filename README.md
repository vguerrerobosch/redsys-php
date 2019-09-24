# Redsys PHP bindings

This unofficial Redsys PHP library provides convenient access to the Redsys API from applications written in the PHP language.

## Requirements

PHP 5.6.0 and later.

## Composer

You can install the library via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require vguerrerobosch/redsys-php
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/vguerrerobosch/redsys-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/stripe-php/init.php');
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
use Vguerrerobosch\Redsys\Webook as RedsysWebhook;
use Vguerrerobosch\Redsys\Exception\SignatureVerificationException;

$content_type = $_SERVER['CONTENT_TYPE'];

$payload = $content_type == 'application/x-www-form-urlencoded' ?
    $_POST :
    @file_get_contents('php://input');

$secret_key = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

try {
    RedsysWebhook::verifySignature($payload, $content_type, $secret_key);
} catch (SignatureVerificationException $exception) {
    http_response_code(403);
    die;
}
```

then use get the data of the response:

```php
$data = RedsysWebhook::getData($payload);
```
