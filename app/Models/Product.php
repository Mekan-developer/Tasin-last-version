<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'admin_user_id',
        'name',
        'description',
        'price',
        'currency',
        'order',
        'active_image',
        'images',
        'views',
        'is_new',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'images'       => 'array',
            'is_new'       => 'boolean',
            'is_active'    => 'boolean',
            'views'        => 'integer',
            'order'        => 'integer',
            'active_image' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
