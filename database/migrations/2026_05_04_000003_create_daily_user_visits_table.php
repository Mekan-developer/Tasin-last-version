<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Дневные входы пользователей приложения — основа аналитики дашборда. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_user_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date')->index();
            $table->unsignedInteger('hits')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_user_visits');
    }
};
