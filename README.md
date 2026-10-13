# balcony-treasury-client

PHP 8.1+ client for the U.S. Treasury Fiscal Data `avg_interest_rates` endpoint, part of The Balcony macro flow system.

## Usage (framework-agnostic)

```php
use MichaelDrennen\Balcony\TreasuryClient\Client;
use MichaelDrennen\Balcony\TreasuryClient\Config;

$client = new Client(new Config(startDate: '2026-01-01'));

foreach ($client->fetchRates() as $dto) {
    // $dto->recordDate, $dto->securityType, $dto->securityDesc, $dto->rate (float)
}
```

Rows with a missing or non-numeric rate are skipped.

## Laravel persistence

```php
use MichaelDrennen\Balcony\TreasuryClient\Models\TreasuryYieldCurveRate;

foreach ($client->fetchRates() as $dto) {
    TreasuryYieldCurveRate::updateOrCreate(
        ['record_date' => $dto->recordDate->format('Y-m-d'), 'security_desc' => $dto->securityDesc],
        $dto->asModelAttributes(),
    );
}
```

Apply `database/schema.sql` or run the migration in `database/migrations`.

## Errors

Failures throw `MichaelDrennen\Balcony\TreasuryClient\Exceptions\BalconyClientException`. HTTP 429 sets `isRateLimited()` and exposes `retryAfterSeconds`.

## Tests

```
composer install
vendor/bin/phpunit
```
