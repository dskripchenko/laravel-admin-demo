<?php

namespace Database\Seeders;

use App\Enums\CustomerSegment;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use Dskripchenko\LaravelAdminMedia\Models\Media;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The shop: a three-level category tree, ~250 products, 400 customers and
 * ~1500 orders spread over the last twelve months with a gentle growth trend,
 * so the charts have a shape. Rows are inserted in bulk: a reset takes
 * seconds, not minutes.
 */
class ShopSeeder extends Seeder
{
    /**
     * Category tree: name => children (a list of leaf names, or a nested map).
     * Each leaf lists [nouns, price range].
     */
    private const TREE = [
        'Electronics' => [
            'Phones' => [['Smartphone', 'Phone', 'Flip Phone'], [199, 1299]],
            'Laptops' => [['Laptop', 'Ultrabook', 'Notebook'], [549, 2899]],
            'Audio' => [
                'Headphones' => [['Headphones', 'Earbuds', 'Headset'], [29, 449]],
                'Speakers' => [['Speaker', 'Soundbar', 'Smart Speaker'], [39, 899]],
            ],
            'Accessories' => [['Charger', 'Cable', 'Power Bank', 'Case', 'Stand'], [9, 129]],
        ],
        'Home & Kitchen' => [
            'Cookware' => [['Pan', 'Pot', 'Wok', 'Dutch Oven', 'Knife Set'], [19, 349]],
            'Furniture' => [['Chair', 'Desk', 'Bookshelf', 'Side Table', 'Stool'], [49, 999]],
            'Lighting' => [['Desk Lamp', 'Floor Lamp', 'Pendant Light', 'LED Strip'], [15, 399]],
        ],
        'Sports & Outdoors' => [
            'Fitness' => [['Yoga Mat', 'Kettlebell', 'Dumbbell Set', 'Jump Rope', 'Foam Roller'], [9, 299]],
            'Camping' => [['Tent', 'Sleeping Bag', 'Lantern', 'Camp Stove', 'Backpack'], [19, 599]],
            'Cycling' => [['Helmet', 'Bike Light', 'Bike Lock', 'Saddle', 'Pump'], [12, 249]],
        ],
        'Books' => [
            'Fiction' => [['Novel', 'Short Stories', 'Thriller', 'Fantasy Saga'], [8, 39]],
            'Non-fiction' => [['Field Guide', 'Biography', 'Cookbook', 'Handbook'], [10, 59]],
            'Kids' => [['Picture Book', 'Activity Book', 'Bedtime Stories'], [5, 29]],
        ],
        'Fashion' => [
            'Men' => [['Jacket', 'Shirt', 'Hoodie', 'Chinos', 'Sweater'], [19, 349]],
            'Women' => [['Dress', 'Blouse', 'Coat', 'Cardigan', 'Skirt'], [19, 399]],
            'Shoes' => [['Sneakers', 'Boots', 'Loafers', 'Sandals', 'Running Shoes'], [29, 299]],
        ],
    ];

    private const ADJECTIVES = [
        'Nova', 'Aero', 'Lumen', 'Orbit', 'Vertex', 'Pulse', 'Arc', 'Echo', 'Terra', 'Zen',
        'Summit', 'Coral', 'Atlas', 'Drift', 'Ember', 'Fjord', 'Halo', 'Iris', 'Juniper', 'Kite',
    ];

    private const VARIANTS = ['', ' Mini', ' Pro', ' Max', ' Lite', ' Plus', ' 2', ' 3', ' Classic', ' Air'];

    private Generator $faker;

    public function run(): void
    {
        $this->faker = Factory::create('en_US');
        $this->faker->seed(2026);

        $leaves = [];
        $position = 0;
        foreach (self::TREE as $root => $children) {
            $rootId = $this->category($root, null, $position++);
            $this->children($rootId, $children, $leaves);
        }

        $products = $this->products($leaves);
        $customers = $this->customers();
        $this->orders($products, $customers);
    }

    /**
     * @param  array<string, mixed>  $children
     * @param  list<array{id: int, nouns: list<string>, range: array{int, int}}>  $leaves
     */
    private function children(int $parentId, array $children, array &$leaves): void
    {
        $position = 0;
        foreach ($children as $name => $spec) {
            $id = $this->category($name, $parentId, $position++);
            if (array_is_list($spec)) {
                $leaves[] = ['id' => $id, 'nouns' => $spec[0], 'range' => $spec[1]];
            } else {
                $this->children($id, $spec, $leaves);
            }
        }
    }

    private function category(string $name, ?int $parentId, int $position): int
    {
        return (int) DB::table('product_categories')->insertGetId([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(12),
            'position' => $position,
            'is_visible' => true,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
    }

    /**
     * @param  list<array{id: int, nouns: list<string>, range: array{int, int}}>  $leaves
     * @return list<array{id: int, name: string, sku: string, price: float}>
     */
    private function products(array $leaves): array
    {
        $images = Media::query()->where('collection', 'products')->orderBy('id')->pluck('id')->all();
        $rows = [];
        $products = [];
        $names = [];
        $id = 0;

        foreach ($leaves as $leafIndex => $leaf) {
            $count = $this->faker->numberBetween(12, 18);
            for ($i = 0; $i < $count; $i++) {
                do {
                    $name = $this->faker->randomElement(self::ADJECTIVES).' '
                        .$this->faker->randomElement($leaf['nouns'])
                        .$this->faker->randomElement(self::VARIANTS);
                } while (isset($names[$name]));
                $names[$name] = true;
                $id++;

                [$min, $max] = $leaf['range'];
                $price = round($this->faker->numberBetween($min * 100, $max * 100) / 100, 0) - 0.01;
                $status = $this->faker->randomElement([
                    ProductStatus::Active, ProductStatus::Active, ProductStatus::Active, ProductStatus::Active,
                    ProductStatus::Active, ProductStatus::Active, ProductStatus::Draft, ProductStatus::Archived,
                ]);
                $created = Carbon::now()->subDays($this->faker->numberBetween(30, 400));
                $cover = $images === [] ? null : $images[($leafIndex * 2 + $i) % count($images)];
                $gallery = $images === [] ? [] : array_values(array_unique([
                    $cover,
                    $images[($leafIndex * 2 + $i + 7) % count($images)],
                    $images[($leafIndex * 2 + $i + 13) % count($images)],
                ]));

                $sku = strtoupper(Str::substr(Str::slug($leaf['nouns'][0], ''), 0, 3)).'-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT);
                $rows[] = [
                    'category_id' => $leaf['id'],
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.$id,
                    'sku' => $sku,
                    'description' => $this->faker->paragraphs(2, true),
                    'price' => $price,
                    'compare_at_price' => $this->faker->boolean(25) ? round($price * 1.2, 0) - 0.01 : null,
                    'stock' => $this->faker->boolean(10) ? 0 : $this->faker->numberBetween(1, 250),
                    'status' => $status->value,
                    'is_featured' => $this->faker->boolean(12),
                    'cover_id' => $cover,
                    'gallery' => json_encode($gallery),
                    'rating' => $this->faker->boolean(85) ? $this->faker->randomFloat(1, 3.2, 5.0) : null,
                    'published_at' => $status === ProductStatus::Draft ? null : $created->copy()->addDays(2),
                    'created_at' => $created,
                    'updated_at' => $created->copy()->addDays($this->faker->numberBetween(0, 25)),
                ];
                if ($status === ProductStatus::Active) {
                    $products[] = ['id' => $id, 'name' => $name, 'sku' => $sku, 'price' => $price];
                }
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('products')->insert($chunk);
        }

        return $products;
    }

    /**
     * @return list<array{id: int, city: string, created: Carbon}>
     */
    private function customers(): array
    {
        $countries = ['US' => 40, 'GB' => 12, 'DE' => 14, 'FR' => 8, 'CA' => 8, 'NL' => 6, 'ES' => 6, 'PL' => 6];
        $rows = [];
        $customers = [];
        for ($id = 1; $id <= 400; $id++) {
            $name = $this->faker->name();
            $country = $this->weighted($countries);
            $created = Carbon::now()->subDays($this->faker->numberBetween(0, 420))->setTime(9, 0);
            $city = $this->faker->city();
            $rows[] = [
                'name' => $name,
                'email' => Str::slug($name, '.').$id.'@'.$this->faker->freeEmailDomain(),
                'phone' => $this->faker->e164PhoneNumber(),
                'city' => $city,
                'country' => $country,
                'segment' => CustomerSegment::New->value,
                'accepts_marketing' => $this->faker->boolean(55),
                'notes' => $this->faker->boolean(10) ? $this->faker->sentence() : null,
                'created_at' => $created,
                'updated_at' => $created,
            ];
            $customers[] = ['id' => $id, 'city' => $city, 'created' => $created];
        }
        DB::table('customers')->insert(array_slice($rows, 0, 200));
        DB::table('customers')->insert(array_slice($rows, 200));

        return $customers;
    }

    /**
     * @param  list<array{id: int, name: string, sku: string, price: float}>  $products
     * @param  list<array{id: int, city: string, created: Carbon}>  $customers
     */
    private function orders(array $products, array $customers): void
    {
        $now = Carbon::now();
        // Oldest first, so the customers who existed on a given day are a prefix.
        usort($customers, fn (array $a, array $b) => $a['created'] <=> $b['created']);
        $orders = [];
        $items = [];
        $orderCount = [];
        $number = 10000;

        // Days back from today; more orders recently (a growing shop).
        for ($day = 365; $day >= 0; $day--) {
            $perDay = (int) round(2 + 4 * (1 - $day / 365) + $this->faker->numberBetween(-1, 2));
            for ($n = 0; $n < $perDay; $n++) {
                $placed = $now->copy()->subDays($day)->setTime($this->faker->numberBetween(7, 23), $this->faker->numberBetween(0, 59));
                if ($placed->greaterThan($now)) {
                    // Today's orders were placed before now, never later in the day.
                    $since = max(1, (int) $now->copy()->startOfDay()->diffInMinutes($now));
                    $placed = $now->copy()->subMinutes($this->faker->numberBetween(1, $since));
                }
                $existing = $this->countCreatedBefore($customers, $placed);
                if ($existing === 0) {
                    continue;
                }
                $customer = $customers[$this->faker->numberBetween(0, $existing - 1)];
                $number++;
                $orderId = count($orders) + 1;
                $status = $this->statusFor($day);

                $subtotal = 0.0;
                $lines = $this->faker->numberBetween(1, 4);
                foreach ($this->faker->randomElements($products, $lines) as $product) {
                    $qty = $this->faker->randomElement([1, 1, 1, 1, 2, 2, 3]);
                    $total = round($product['price'] * $qty, 2);
                    $subtotal += $total;
                    $items[] = [
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'product_name' => $product['name'],
                        'sku' => $product['sku'],
                        'unit_price' => $product['price'],
                        'quantity' => $qty,
                        'total' => $total,
                        'created_at' => $placed,
                        'updated_at' => $placed,
                    ];
                }
                $shipping = $subtotal >= 100 ? 0 : 9.90;
                $discount = $this->faker->boolean(15) ? round($subtotal * 0.1, 2) : 0;
                $orders[] = [
                    'number' => 'ORD-'.$number,
                    'customer_id' => $customer['id'],
                    'status' => $status->value,
                    'payment_method' => $this->faker->randomElement(['card', 'card', 'card', 'paypal', 'invoice']),
                    'subtotal' => round($subtotal, 2),
                    'shipping' => $shipping,
                    'discount' => $discount,
                    'total' => round($subtotal + $shipping - $discount, 2),
                    'currency' => 'USD',
                    'shipping_city' => $customer['city'],
                    'shipping_address' => $this->faker->streetAddress(),
                    'notes' => $this->faker->boolean(8) ? $this->faker->sentence() : null,
                    'placed_at' => $placed,
                    'paid_at' => in_array($status, [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Refunded], true) ? $placed->copy()->addMinutes(5) : null,
                    'shipped_at' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true) ? $placed->copy()->addDay() : null,
                    'delivered_at' => $status === OrderStatus::Delivered ? $placed->copy()->addDays(3) : null,
                    'created_at' => $placed,
                    'updated_at' => $placed,
                ];
                $orderCount[$customer['id']] = ($orderCount[$customer['id']] ?? 0) + 1;
            }
        }

        foreach (array_chunk($orders, 200) as $chunk) {
            DB::table('orders')->insert($chunk);
        }
        foreach (array_chunk($items, 300) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }

        // Segments follow the order history.
        foreach ($orderCount as $customerId => $count) {
            $segment = $count >= 8 ? CustomerSegment::Vip : ($count >= 3 ? CustomerSegment::Regular : CustomerSegment::New);
            if ($segment !== CustomerSegment::New) {
                DB::table('customers')->where('id', $customerId)->update(['segment' => $segment->value]);
            }
        }
    }

    /**
     * @param  list<array{id: int, city: string, created: Carbon}>  $customers  sorted by `created`
     */
    private function countCreatedBefore(array $customers, Carbon $moment): int
    {
        [$low, $high] = [0, count($customers)];
        while ($low < $high) {
            $mid = intdiv($low + $high, 2);
            if ($customers[$mid]['created']->lte($moment)) {
                $low = $mid + 1;
            } else {
                $high = $mid;
            }
        }

        return $low;
    }

    /** Older orders are settled; the last few days still move through the flow. */
    private function statusFor(int $daysAgo): OrderStatus
    {
        if ($daysAgo <= 1) {
            return $this->faker->randomElement([OrderStatus::Pending, OrderStatus::Pending, OrderStatus::Paid]);
        }
        if ($daysAgo <= 5) {
            return $this->faker->randomElement([OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Shipped, OrderStatus::Pending, OrderStatus::Cancelled]);
        }

        return $this->weighted([
            OrderStatus::Delivered->value => 82,
            OrderStatus::Cancelled->value => 9,
            OrderStatus::Refunded->value => 5,
            OrderStatus::Shipped->value => 4,
        ], OrderStatus::class);
    }

    /**
     * @template T
     *
     * @param  array<string, int>  $weights
     * @param  class-string<T>|null  $enum
     */
    private function weighted(array $weights, ?string $enum = null): mixed
    {
        $roll = $this->faker->numberBetween(1, array_sum($weights));
        foreach ($weights as $value => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $enum ? $enum::from($value) : $value;
            }
        }

        return $enum ? $enum::from(array_key_first($weights)) : array_key_first($weights);
    }
}
