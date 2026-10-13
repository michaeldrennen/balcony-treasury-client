<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('balcony_treasury_yield_curve_rates', function (Blueprint $table): void {
            $table->id();
            $table->date('record_date');
            $table->string('security_type', 64);
            $table->string('security_desc', 64);
            $table->decimal('rate', 8, 4);
            $table->timestamps();

            $table->unique(['record_date', 'security_desc'], 'balcony_treasury_record_security_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balcony_treasury_yield_curve_rates');
    }
};
