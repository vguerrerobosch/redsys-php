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
use Vguerrerobosch\Redsys\PaymentRequest as RedsysPaymentRequest;

Redsys::setApiKey('sq7HjrUOBfKmC576ILgskD5srU870gJ7');

$payment_request = RedsysPaymentRequest::create([
    'amount' => 2000,
    'order' => microtime(),
    'merchant_code' => 999008881,
    'merchant_url' => 'https://941a2b9e.ngrok.io/webhook',
    'url_ok' => 'http://redsys-php.test/ok',
    'url_ko' => 'http://redsys-php.test/ko',
]);
```

then you can build the form like:

```html
<form action="<?php $payment_request['url'] ?>" method="POST" name="payment_form">
    <input type="hidden" name="Ds_MerchantParameters" value="<?php $payment_request['params'] ?>"/>
    <input type="hidden" name="Ds_Signature" value="<?php $payment_request['signature'] ?>"/>
    <input type="hidden" name="Ds_SignatureVersion" value="<?php $payment_request['version'] ?>"/>
    <input type="submit" value="Submit">
</form>
<script>
window.onload = function(){
  document.forms['payment_form'].submit();
}
</script>
```

### Handling webhooks

#### Verify Signature
The very first thing should be verifing the signature of the request:

```php
use Vguerrerobosch\Redsys\Webook as RedsysWebhook;
use Vguerrerobosch\Redsys\Exception\SignatureVerificationException;

$payload = file_get_contents('php://input');
$secret_key = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

try {
    RedsysWebhook::verifySignature($payload, $secret_key);
} catch (SignatureVerificationException $exception) {
    header('HTTP/1.0 403 Forbidden');
    echo $exception->getMessage();
    die;
}
```

then use get the data of the response:

```php
$data = RedsysWebhook::getData($payload);
```
