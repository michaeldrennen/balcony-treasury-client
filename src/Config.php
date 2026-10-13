<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient;

use DateTimeImmutable;
use InvalidArgumentException;

final class Config
{
    public const DEFAULT_BASE_URL = 'https://api.fiscaldata.treasury.gov/services/api/fiscal_service/v2/accounting/od/avg_interest_rates';

    public function __construct(
        public readonly string $startDate = '2026-01-01',
        public readonly int $pageSize = 100,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly float $timeoutSeconds = 15.0,
    ) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);

        if ($date === false || $date->format('Y-m-d') !== $startDate) {
            throw new InvalidArgumentException('Treasury startDate must be formatted as YYYY-MM-DD.');
        }

        if ($pageSize < 1 || $pageSize > 10000) {
            throw new InvalidArgumentException('Treasury pageSize must be between 1 and 10000.');
        }
    }
}
