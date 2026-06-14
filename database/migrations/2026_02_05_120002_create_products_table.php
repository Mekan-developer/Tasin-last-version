<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Создаёт таблицу products по структуре из db.json (связь с categories).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->unsignedTinyInteger('active_image')->default(0);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency');
            $table->json('images');
            $table->unsignedInteger('views')->default(0);
            $table->boolean('is_new')->default(false);
            $table->json('variants')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
