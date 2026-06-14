<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'name',
        'order',
        'show_price',
        'image_icon',
        'views',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'show_price' => 'boolean',
            'is_active'  => 'boolean',
            'views'      => 'integer',
            'order'      => 'integer',
        ];
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }
}
