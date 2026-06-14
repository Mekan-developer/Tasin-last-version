<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Пользователи приложения (не админ-панели). Вход через Google / телефон. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('role', 32)->default('user')->index();
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
