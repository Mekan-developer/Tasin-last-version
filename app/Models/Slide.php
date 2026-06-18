<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'badge',
        'title',
        'description',
        'bg_color',
        'image',
        'price',
        'currency',
        'old_price',
        'discount',
        'order',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'old_price' => 'decimal:2',
            'discount'  => 'integer',
            'order'     => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }
}
