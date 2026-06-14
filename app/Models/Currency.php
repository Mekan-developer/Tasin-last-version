<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'rate_to_usd',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'rate_to_usd' => 'decimal:4',
            'is_default'  => 'boolean',
        ];
    }

    public static function tmtRate(): float
    {
        return (float) static::where('code', 'TMT')->value('rate_to_usd') ?: 19.50;
    }

    // Конвертирует сумму из одной валюты в другую
    public static function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = static::tmtRate();

        if ($from === 'USD' && $to === 'TMT') {
            return $amount * $rate;
        }

        if ($from === 'TMT' && $to === 'USD') {
            return $amount / $rate;
        }

        return $amount;
    }
}
