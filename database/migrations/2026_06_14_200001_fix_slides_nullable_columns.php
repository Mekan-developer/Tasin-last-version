<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->string('image')->nullable()->default(null)->change();
            $table->decimal('price', 10, 2)->nullable()->default(null)->change();
            $table->string('currency')->nullable()->default('USD')->change();
            $table->string('bg_color')->nullable()->default('#1e40af')->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('images')->default('[]')->change();
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->string('image')->nullable(false)->default(null)->change();
            $table->decimal('price', 10, 2)->nullable(false)->default(null)->change();
            $table->string('currency')->nullable(false)->default(null)->change();
            $table->string('bg_color')->nullable(false)->default(null)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('images')->default(null)->change();
        });
    }
};
