<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient\DTO;

use DateTimeImmutable;

final class TreasuryRateDTO implements MetricObservation
{
    public function __construct(
        public readonly DateTimeImmutable $recordDate,
        public readonly string $securityType,
        public readonly string $securityDesc,
        public readonly float $rate,
    ) {
    }

    public function asModelAttributes(): array
    {
        return [
            'record_date' => $this->recordDate->format('Y-m-d'),
            'security_type' => $this->securityType,
            'security_desc' => $this->securityDesc,
            'rate' => $this->rate,
        ];
    }
}
