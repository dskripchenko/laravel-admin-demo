<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Order;
use App\Models\Product;
use Dskripchenko\LaravelAdmin\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Seeder для демонстрационного стенда.
 *
 * Создаёт:
 *   - admin@example.com / password (suspendsuper-admin login)
 *   - 50 фейковых статей (~80% published)
 *   - 50 товаров в 4 категориях
 *   - 50 заказов с разнообразными статусами
 */
final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedArticles();
        $this->seedProducts();
        $this->seedOrders();
    }

    private function seedAdmin(): void
    {
        AdminUser::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'locale' => 'ru',
                'theme' => 'light',
            ],
        );
    }

    private function seedArticles(): void
    {
        $statuses = ['draft', 'review', 'published', 'archived'];
        for ($i = 1; $i <= 50; $i++) {
            $title = $this->fakeTitle($i);
            Article::query()->create([
                'title' => $title,
                'slug' => Str::slug($title).'-'.$i,
                'excerpt' => $this->fakeExcerpt(),
                'body' => $this->fakeBody(),
                'status' => $statuses[array_rand($statuses)],
                'published_at' => Carbon::now()->subDays(random_int(0, 90))->subHours(random_int(0, 23)),
            ]);
        }
    }

    private function seedProducts(): void
    {
        $categories = ['electronics', 'apparel', 'home', 'books'];
        $names = [
            'electronics' => ['Wireless Headphones', 'Smart Watch', 'USB-C Hub', 'Mechanical Keyboard', '4K Monitor'],
            'apparel' => ['Cotton T-Shirt', 'Denim Jacket', 'Running Shoes', 'Wool Beanie', 'Leather Belt'],
            'home' => ['Ceramic Mug', 'LED Desk Lamp', 'Throw Blanket', 'Cast Iron Pan', 'Wall Clock'],
            'books' => ['Clean Code', 'The Pragmatic Programmer', 'Refactoring', 'Designing Data-Intensive Apps', 'Domain-Driven Design'],
        ];
        for ($i = 1; $i <= 50; $i++) {
            $cat = $categories[array_rand($categories)];
            $base = $names[$cat][array_rand($names[$cat])];
            Product::query()->create([
                'sku' => strtoupper(Str::random(3)).'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'name' => $base.' #'.$i,
                'description' => $this->fakeExcerpt(),
                'price' => round(random_int(500, 100000) / 100, 2),
                'stock' => random_int(0, 250),
                'category' => $cat,
                'is_active' => random_int(1, 100) <= 85,
            ]);
        }
    }

    private function seedOrders(): void
    {
        $statuses = ['new', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        $names = ['Иван Иванов', 'Мария Петрова', 'Сергей Сидоров', 'Ольга Кузнецова', 'Дмитрий Волков'];
        for ($i = 1; $i <= 50; $i++) {
            $name = $names[array_rand($names)];
            Order::query()->create([
                'number' => 'ORD-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'customer_name' => $name,
                'customer_email' => Str::slug(strtolower($name), '.').'@example.com',
                'total' => round(random_int(2000, 500000) / 100, 2),
                'status' => $statuses[array_rand($statuses)],
                'placed_at' => Carbon::now()->subDays(random_int(0, 60))->subHours(random_int(0, 23)),
                'notes' => random_int(0, 1) ? 'Доставка курьером' : null,
            ]);
        }
    }

    private function fakeTitle(int $i): string
    {
        $topics = [
            'Как мы запустили',
            'Почему важно',
            'Архитектура',
            '5 ошибок при',
            'Опыт миграции',
            'Сравнение',
            'Гайд по',
            'Введение в',
        ];
        $tails = [
            'Laravel 12',
            'микросервисов',
            'Vue 3 SPA',
            'TypeScript-проектов',
            'Tailwind v4',
            'PHP 8.5',
            'PostgreSQL',
            'Redis-кеша',
        ];

        return $topics[array_rand($topics)].' '.$tails[array_rand($tails)].' #'.$i;
    }

    private function fakeExcerpt(): string
    {
        return 'Демонстрационный текст для админ-панели. Lorem ipsum dolor sit amet, '
            .'consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore.';
    }

    private function fakeBody(): string
    {
        return '<h2>Подзаголовок</h2><p>Полный текст статьи с поддержкой WYSIWYG. '
            .'Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'
            .'<ul><li>Первый пункт</li><li>Второй</li><li>Третий</li></ul>'
            .'<p>Финальный абзац с полезной мыслью.</p>';
    }
}
