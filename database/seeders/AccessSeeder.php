<?php

namespace Database\Seeders;

use Dskripchenko\LaravelAdmin\Permission\Models\Role;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

/**
 * Roles and administrators: the demo accounts from config/demo.php, plus a
 * handful of staff accounts so the users list has something to show. The
 * accounts date from a month ago, and so do their audit entries.
 */
class AccessSeeder extends Seeder
{
    /** @var array<string, array{name: string, description: string, permissions: list<string>}> */
    public const ROLES = [
        'editor' => [
            'name' => 'Editor',
            'description' => 'Manages the catalog and the blog. No orders, customers or system sections.',
            'permissions' => [
                'admin.products.*',
                'admin.product-categories.*',
                'admin.posts.*',
                'admin.blog-categories.*',
                'admin.tags.*',
                'admin.authors.*',
                'admin.media.*',
            ],
        ],
        'viewer' => [
            'name' => 'Viewer',
            'description' => 'Sees every section, changes nothing.',
            'permissions' => ['admin.*.view'],
        ],
        'support' => [
            'name' => 'Support',
            'description' => 'Works with orders and customers.',
            'permissions' => ['admin.orders.*', 'admin.customers.*', 'admin.products.view'],
        ],
    ];

    public function run(): void
    {
        Carbon::setTestNow(Carbon::now()->subDays(30)->setTime(10, 0));
        try {
            $this->accounts();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function accounts(): void
    {
        foreach (self::ROLES as $slug => $role) {
            Role::query()->updateOrCreate(['slug' => $slug], $role);
        }

        /** @var class-string<Model> $model */
        $model = config('admin.auth.model');

        foreach (config('demo.accounts') as $account) {
            if ($account['role'] === 'super-admin') {
                Artisan::call('admin:user', [
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'password' => $account['password'],
                    '--super' => true,
                ]);

                continue;
            }

            $user = $model::query()->create([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => $account['password'],
                'is_active' => true,
            ]);
            $user->assignRole($account['role']);
        }

        $faker = Factory::create('en_US');
        $faker->seed(1001);
        $staffRoles = ['support', 'support', 'editor', 'viewer'];
        for ($i = 0; $i < 12; $i++) {
            $name = $faker->unique()->name();
            $user = $model::query()->create([
                'name' => $name,
                'email' => strtolower(str_replace([' ', '.', "'"], ['.', '', ''], $name)).'@staff.demo.test',
                'password' => $faker->password(16, 24),
                'is_active' => $i % 5 !== 4,
                'locale' => $i % 3 === 0 ? 'ru' : 'en',
            ]);
            $user->assignRole($staffRoles[$i % count($staffRoles)]);
        }
    }
}
