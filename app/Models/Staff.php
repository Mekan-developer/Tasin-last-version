<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'position',
        'department',
        'salary',
        'paid',
        'debt',
        'hired_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'salary'    => 'decimal:2',
            'paid'      => 'decimal:2',
            'debt'      => 'decimal:2',
            'hired_at'  => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SalaryTransaction::class);
    }

    // Остаток к выплате в текущем месяце
    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->salary - (float) $this->paid);
    }
}
