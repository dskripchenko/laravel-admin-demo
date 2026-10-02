<?php

namespace App\Models\Shop;

use App\Enums\CustomerSegment;
use Dskripchenko\LaravelAdmin\Audit\Concerns\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use Loggable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'segment' => CustomerSegment::class,
            'accepts_marketing' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
