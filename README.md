# Omnipay Tests

Testing components for the [Omnipay](https://omnipay.thephpleague.com/) payment processing library.

Requires **PHP 8.4, 8.5 or 8.6**.

## Installation

```bash
composer require --dev omnipay/tests
```

## Usage

Gateway drivers should extend `Omnipay\Tests\GatewayTestCase` and assign `$this->gateway` in `setUp()`:

```php
<?php

declare(strict_types=1);

namespace Omnipay\Stripe;

use Omnipay\Tests\GatewayTestCase;
use Override;

final class GatewayTest extends GatewayTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }
}
```

HTTP mocks live in a `Mock/` directory next to the test class:

```php
$this->setMockHttpResponse('PurchaseSuccess.txt');
$response = $this->gateway->purchase($this->getValidCard())->send();
```

## Default parameter values

`GatewayTestCase` generates sample values from each default parameter type instead of always using `uniqid()`:

| Default type | Sample value |
| --- | --- |
| `bool` | toggled boolean |
| `int` | random integer |
| `float` | random float |
| `array` of options | one of the listed options |
| `currency` | a valid uppercase ISO code |
| other | unique string |

This avoids false failures when getters cast or validate values (for example `getCurrency()` uppercases the code).

## Development

```bash
composer install
composer test
```
