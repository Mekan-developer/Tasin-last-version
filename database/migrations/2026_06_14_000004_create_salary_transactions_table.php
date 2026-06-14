<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            // salary: выплата зарплаты (paid↑)
            // advance: аванс — долг работника↑
            // debt_pay: возврат долга работником — debt↓
            $table->enum('type', ['salary', 'advance', 'debt_pay']);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_transactions');
    }
};
