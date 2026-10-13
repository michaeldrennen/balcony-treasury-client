<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient\Tests\Integration;

use DateTimeImmutable;
use MichaelDrennen\Balcony\TreasuryClient\Client;
use MichaelDrennen\Balcony\TreasuryClient\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Hits the real remote source. Excluded from the default run; use `vendor/bin/phpunit --group integration`.
 */
#[Group('integration')]
final class LiveFetchTest extends TestCase
{
    private const MAX_AGE_DAYS = 75;

    public function testFetchesRecentDataFromLiveSource(): void
    {
        $config = new Config(startDate: (new DateTimeImmutable('-6 months'))->format('Y-m-d'));
        $dtos = (new Client($config))->fetchRates();

        $this->assertNotEmpty($dtos, 'Expected average interest rates from Treasury Fiscal Data.');

        $newest = max(array_map(static fn ($dto): DateTimeImmutable => $dto->recordDate, $dtos));
        $ageDays = (int) $newest->diff(new DateTimeImmutable())->format('%a');

        $this->assertLessThanOrEqual(
            self::MAX_AGE_DAYS,
            $ageDays,
            sprintf('Newest record is from %s; the source may have stopped updating.', $newest->format('Y-m-d')),
        );
    }
}
