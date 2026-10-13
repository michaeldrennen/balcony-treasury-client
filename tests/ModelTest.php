<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient\Tests;

use DateTimeImmutable;
use MichaelDrennen\Balcony\TreasuryClient\DTO\TreasuryRateDTO;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testDtoMapsToModelAttributes(): void
    {
        $dto = new TreasuryRateDTO(new DateTimeImmutable('2026-09-30'), 'Marketable', 'Treasury Bills', 4.821);

        $this->assertSame([
            'record_date' => '2026-09-30',
            'security_type' => 'Marketable',
            'security_desc' => 'Treasury Bills',
            'rate' => 4.821,
        ], $dto->asModelAttributes());
    }

    public function testEloquentModelAcceptsDtoAttributes(): void
    {
        if (!class_exists('Illuminate\Database\Eloquent\Model')) {
            $this->markTestSkipped('illuminate/database is not installed.');
        }

        // Eloquent's guarded-attribute check reads the column listing, so the model needs a connection.
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $dto = new TreasuryRateDTO(new DateTimeImmutable('2026-09-30'), 'Marketable', 'Treasury Notes', 4.152);
        $model = new \MichaelDrennen\Balcony\TreasuryClient\Models\TreasuryYieldCurveRate($dto->asModelAttributes());

        $this->assertSame('Treasury Notes', $model->security_desc);
        $this->assertSame('balcony_treasury_yield_curve_rates', $model->getTable());
        $this->assertSame(['id'], $model->getGuarded());
    }
}
