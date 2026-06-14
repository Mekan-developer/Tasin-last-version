<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'amount',
        'type',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
