<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->char('session', 8);  // анонимный hex-id посетителя витрины
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->enum('region', ['Aşgabat', 'Ahal', 'Mary', 'Lebap', 'Daşoguz', 'Balkan']);
            $table->enum('device', ['mobile', 'tablet', 'desktop']);
            $table->string('brand')->nullable(); // устройство/браузер
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index('session');
            $table->index('region');
            $table->index('device');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
