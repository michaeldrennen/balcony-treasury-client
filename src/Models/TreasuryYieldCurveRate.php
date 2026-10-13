<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\TreasuryClient\Models;

use Illuminate\Database\Eloquent\Model;

final class TreasuryYieldCurveRate extends Model
{
    protected $table = 'balcony_treasury_yield_curve_rates';

    protected $guarded = ['id'];

    protected $casts = [
        'record_date' => 'date',
        'rate' => 'decimal:4',
    ];
}
