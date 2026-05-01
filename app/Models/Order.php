<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'customer_name',
        'customer_email',
        'total',
        'status',
        'placed_at',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'placed_at' => 'datetime',
    ];
}
