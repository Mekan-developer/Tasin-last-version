<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Односторонняя лента сообщений: админ отправляет текст и/или вложение (image/file), клиенты только просматривают.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->text('body')->nullable();
            $table->string('attachment')->nullable();
            $table->string('attachment_type')->nullable(); // 'image' | 'file'
            $table->string('attachment_original_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
    }
};
