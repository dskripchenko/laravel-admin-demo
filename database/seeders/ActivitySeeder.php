<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Database\Seeder;

/**
 * A few changes made through Eloquent, so the audit log has entries to show
 * from the first visit.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        Order::query()->where('status', OrderStatus::Pending->value)->orderBy('id')->limit(3)->get()
            ->each(fn (Order $order) => $order->transitionTo(OrderStatus::Paid));

        Product::query()->orderBy('id')->limit(4)->get()
            ->each(fn (Product $product) => $product->update(['stock' => $product->stock + 25]));
    }
}
