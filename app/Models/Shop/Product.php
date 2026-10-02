<?php

namespace App\Models\Shop;

use App\Enums\ProductStatus;
use Dskripchenko\LaravelAdmin\Audit\Concerns\Loggable;
use Dskripchenko\LaravelAdminMedia\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use Loggable, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['category_name', 'cover_url'];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock' => 'integer',
            'is_featured' => 'boolean',
            'gallery' => 'array',
            'rating' => 'float',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->category?->name;
    }

    public function getCoverUrlAttribute(): ?string
    {
        $cover = $this->cover;

        return $cover?->variant('thumb')?->url ?? $cover?->url;
    }
}
