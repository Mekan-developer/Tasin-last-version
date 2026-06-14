<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Создаёт таблицу slides для баннеров/карусели по структуре из db.json.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('bg_color');
            $table->string('image');
            $table->decimal('price', 10, 2);
            $table->string('currency');
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('discount')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
