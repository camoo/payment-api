Below is an example **README.md** you could include in your project. It provides an **overview**, **installation** instructions, **usage examples**, **endpoint references**, and **credits**. Feel free to customize this to your specific needs (e.g., project naming, author info, licensing).

---

# Camoo Payment API – PHP Library

A simple, **PSR-4** compliant PHP library for accessing the Camoo Payment API. This library provides convenient classes for working with **Account** and **Payment** endpoints, while allowing you to inject your own HTTP client if desired.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Getting Started](#getting-started)
    - [Configuration & Client](#configuration--client)
    - [Working with Account API](#working-with-account-api)
    - [Working with Payment API](#working-with-payment-api)
- [Models](#models)
- [Error Handling](#error-handling)
- [Development & Contributing](#development--contributing)
- [License](#license)

---

## Requirements

- **PHP 8.1+**
- Composer (for dependency management)
- [camoo/curl-http-client](https://github.com/camoo/curl-http-client) (already required in `composer.json`)

---
# Developer Notes (Self-Implementation)
- OpenAPI Documentation 
  - For full endpoint definitions, schemas, and examples, see the 
      Camoo Payment API OpenAPI documentation [Camoo Payment API documentation](https://redocly.github.io/redoc/?url=https://raw.githubusercontent.com/camoo/payment-api/main/openapi.yaml).
- 📦 Postman Collection 
  - Download the ready-to-use Postman collection:
  [Postman Collection](docs/postman/Camoo-Payment-API.postman_collection.json)

## Installation

Install the package via [Composer](https://getcomposer.org/):

```bash
composer require camoo/payment-api
```

If you are installing from a local copy or private repository, ensure your `composer.json` and `repositories` settings are correctly configured, then run `composer install`.

---

## Getting Started

### Configuration & Client

1. **Instantiate** the main HTTP `Client`, providing your **API key** and **API secret**.
2. **Optionally** set `debug` to `true` if you want to see more verbose debugging info.
3. The **API version** defaults to `v1`.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Camoo\Payment\Http\Client as PaymentClient;

// Your credentials
$apiKey = 'YOUR_API_KEY';
$apiSecret = 'YOUR_API_SECRET';

$client = new PaymentClient(
    apiKey: $apiKey,
    apiSecret: $apiSecret,
    httpClient: null,  // optionally pass a custom ClientInterface
    debug: false,      // set to true for verbose logging
    apiVersion: 'v1'
);
```

### Working with Account API

Use the `Camoo\Payment\Api\Account` class to fetch account info (e.g. balance, etc.):

```php
use Camoo\Payment\Api\AccountApi;

$accountApi = new AccountApi($client);

try {
    $accountInfo = $accountApi->get();
    // $accountInfo is an instance of \Camoo\Payment\Models\Account

    echo "Balance: " . $accountInfo->balance->amount . " " . $accountInfo->balance->currency->value . PHP_EOL;
    echo "Viewed At: " . $accountInfo->viewedAt->format('Y-m-d H:i:s') . PHP_EOL;
} catch (\Camoo\Payment\Exception\ApiException $e) {
    // Handle error
    echo "Error fetching account: " . $e->getMessage();
}
```

### Working with Payment API

Use the `Camoo\Payment\Api\Payment` class to **cash out** or **verify** a payment.

```php
use Camoo\Payment\Api\PaymentApi;

$paymentApi = new PaymentApi($client);

// 1) Cash Out (example usage)
try {
    $response = $paymentApi->cashout([
        'phone_number' => '+237612345678',
        'amount'      => 5000,
        'notification_url'    => 'https://example.com/notify-me',
    ]);

    // $response is an instance of \Camoo\Payment\Models\Payment
    echo "Cashout ID: " . $response->id . PHP_EOL;
    echo "Status: " . $response->status . PHP_EOL;
} catch (\Camoo\Payment\Exception\ApiException $e) {
    // Handle error
    echo "Cashout error: " . $e->getMessage();
}

// 2) Verify a payment (example usage)
try {
    $paymentId = '12345';
    $verifiedPayment = $paymentApi->verify($paymentId);

    echo "Payment " . $verifiedPayment->id . " status: " . $verifiedPayment->status . PHP_EOL;
} catch (\Camoo\Payment\Exception\ApiException $e) {
    // Handle error
    echo "Verification error: " . $e->getMessage();
}
```

---

## Models

This library returns response data as **Model** classes. Notable models include:

- **`Camoo\Payment\Models\Account`**
    - Properties: `balance` (a `Money` object) and `viewedAt` (`DateTimeInterface`).
    - Use `Account::fromArray($data)` to create from raw response data.
    - Use `toArray()` or JSON-encode it for debugging or storage.

- **`Camoo\Payment\Models\Payment`**
    - Holds payment data (`id`, `amount`, `network`, `status`, timestamps, etc.).
    - Use `Payment::fromArray($data)` to create from raw response data.
    - Use `toArray()` to convert back to a standard PHP array.

**`Money` Class**
- Found in `Camoo\Payment\ValueObject\Money`, which pairs an **`amount`** (float) with a **`currency`** (an enum from `Camoo\Payment\Enum\Currency`).

---

## Error Handling

Most client or API errors throw a `Camoo\Payment\Exception\ApiException`. This exception will include the **HTTP status code** (if available) and a **message** from the API response (or a fallback).

Example:
```php
try {
    // Some Payment API call
} catch (\Camoo\Payment\Exception\ApiException $e) {
    echo 'API Error (code ' . $e->getCode() . '): ' . $e->getMessage();
}
```

---

## Development & Contributing

1. **Clone** the repository.
2. **Install** dependencies:
   ```bash
   composer install
   ```
3. **Run tests** (if available):
   ```bash
   composer test
   ```
4. **Pull Requests**: Please submit PRs that follow **PSR-12** coding style and include tests.

We welcome issues and pull requests to make this library more robust, add additional endpoints, or improve performance.

---

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).

---

**Enjoy building with Camoo Payment API!** If you have any feedback or questions, feel free to open an issue or reach out to us.