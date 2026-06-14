<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица валют: USD и TMT. Курс 1 USD = 19.50 TMT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('USD, TMT');
            $table->string('name', 50);
            $table->decimal('rate_to_usd', 12, 4)->default(1)->comment('Единиц валюты за 1 USD (1 USD = 19.50 TMT)');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        DB::table('currencies')->insert([
            ['code' => 'USD', 'name' => 'US Dollar', 'rate_to_usd' => 1, 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'TMT', 'name' => 'Turkmenistan Manat', 'rate_to_usd' => 19.50, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
