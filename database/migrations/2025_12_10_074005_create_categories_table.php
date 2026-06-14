<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(1);
            $table->boolean('show_price')->default(true);
            $table->string('image_icon')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
