<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('currency_from', 3)->default('USD');
            $table->string('currency_to', 3)->default('PYG');
            $table->decimal('rate', 16, 4);
            $table->string('source', 50)->default('manual'); // manual, bcp
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
