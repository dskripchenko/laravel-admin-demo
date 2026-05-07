<?php

declare(strict_types=1);

namespace App\Models;

use Dskripchenko\LaravelAdmin\Audit\Concerns\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    use HasFactory;
    use Loggable;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'stock',
        'is_active',
        'category',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];
}
