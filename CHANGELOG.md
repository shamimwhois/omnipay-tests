# Changelog

## 5.0.0

- Require PHP 8.4, 8.5, or 8.6 (`^8.4|^8.5|^8.6`)
- Require PHPUnit 11.5, 12.5, or 13.4
- Require Guzzle PSR-7 2.7 or 3.2
- Add `declare(strict_types=1)`, typed properties, first-class callables, `array_find()`, and `??=` (PHP 8.4 / 8.5 / 8.6)
- Generate sample gateway parameter values from the default type instead of always using `uniqid()`
- Handle `currency` getters that uppercase the stored value
- Add `fetchTransaction` and `acceptNotification` coverage to `GatewayTestCase`
- Register the HTTP mock discovery strategy once per process
- Throw a clear error when a mock HTTP response file is missing
- Add `billingAddress3`, `shippingAddress3`, and `email` to `getValidCard()`
- Add GitHub Actions CI and a PHPUnit test suite for this package
