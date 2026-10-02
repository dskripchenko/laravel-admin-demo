<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use Dskripchenko\LaravelAdminMedia\Models\Media;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The blog: authors, categories, tags and ~140 markdown posts with headings,
 * lists, quotes and code — enough to make the editor and the list worth a look.
 */
class BlogSeeder extends Seeder
{
    private const CATEGORIES = [
        'Engineering' => '#6366f1',
        'Product' => '#10b981',
        'Design' => '#f59e0b',
        'Company' => '#ef4444',
        'Tutorials' => '#0ea5e9',
        'Releases' => '#8b5cf6',
    ];

    private const TAGS = [
        'laravel', 'php', 'vue', 'typescript', 'performance', 'testing', 'security', 'accessibility',
        'design-systems', 'dx', 'api', 'databases', 'postgres', 'sqlite', 'queues', 'caching',
        'deployment', 'docker', 'i18n', 'ux', 'dashboards', 'forms', 'tables', 'charts',
        'open-source', 'release-notes', 'roadmap', 'hiring', 'culture', 'case-study',
    ];

    private const TITLE_PATTERNS = [
        'How we cut %s time in half',
        'A practical guide to %s',
        '%s: lessons from a year in production',
        'Why %s matters more than you think',
        'Ten things we learned about %s',
        'The quiet power of %s',
        'Rethinking %s for small teams',
        '%s without the ceremony',
        'What nobody tells you about %s',
        'Shipping %s on a Friday (and living to tell)',
    ];

    private const SUBJECTS = [
        'admin panels', 'form validation', 'data grids', 'dashboards', 'role-based access',
        'background jobs', 'translations', 'dark mode', 'search', 'audit logs', 'file uploads',
        'onboarding', 'release planning', 'code review', 'API design', 'caching', 'inline editing',
        'bulk actions', 'saved filters', 'error budgets', 'feature flags', 'tree views',
    ];

    private Generator $faker;

    public function run(): void
    {
        $this->faker = Factory::create('en_US');
        $this->faker->seed(4242);

        $avatars = Media::query()->where('collection', 'avatars')->orderBy('id')->pluck('id')->all();
        $authorIds = [];
        $titles = ['Staff Engineer', 'Product Designer', 'Engineering Manager', 'Developer Advocate', 'Founder', 'Technical Writer', 'Frontend Engineer', 'Product Manager'];
        for ($i = 0; $i < 8; $i++) {
            $name = $this->faker->name();
            $authorIds[] = DB::table('authors')->insertGetId([
                'name' => $name,
                'email' => Str::slug($name, '.').'@blog.demo.test',
                'title' => $titles[$i],
                'bio' => $this->faker->paragraph(3),
                'avatar_id' => $avatars[$i % max(1, count($avatars))] ?? null,
                'website' => $this->faker->boolean(60) ? 'https://'.$this->faker->domainName() : null,
                'is_active' => $i !== 7,
                'created_at' => now()->subYears(2),
                'updated_at' => now()->subYears(2),
            ]);
        }

        $categoryIds = [];
        $position = 0;
        foreach (self::CATEGORIES as $name => $color) {
            $categoryIds[] = DB::table('blog_categories')->insertGetId([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $this->faker->sentence(10),
                'color' => $color,
                'position' => $position++,
                'created_at' => now()->subYears(2),
                'updated_at' => now()->subYears(2),
            ]);
        }

        $tagIds = [];
        foreach (self::TAGS as $tag) {
            $tagIds[] = DB::table('tags')->insertGetId([
                'name' => Str::headline($tag),
                'slug' => $tag,
                'created_at' => now()->subYears(2),
                'updated_at' => now()->subYears(2),
            ]);
        }

        $posts = [];
        $pivot = [];
        $used = [];
        for ($id = 1; $id <= 140; $id++) {
            do {
                $title = Str::ucfirst(sprintf(
                    $this->faker->randomElement(self::TITLE_PATTERNS),
                    $this->faker->randomElement(self::SUBJECTS),
                ));
            } while (isset($used[$title]) && count($used) < 200);
            $used[$title] = true;

            $status = $id > 128
                ? $this->faker->randomElement([PostStatus::Draft, PostStatus::Review])
                : ($this->faker->boolean(92) ? PostStatus::Published : PostStatus::Draft);
            $created = Carbon::now()->subDays((int) round((140 - $id) * 2.6 + $this->faker->numberBetween(0, 2)));
            $body = $this->body();
            $published = $status === PostStatus::Published ? $created->copy()->addDays($this->faker->numberBetween(0, 3)) : null;

            $posts[] = [
                'author_id' => $this->faker->randomElement($authorIds),
                'category_id' => $this->faker->randomElement($categoryIds),
                'title' => $title,
                'slug' => Str::slug($title).'-'.$id,
                'excerpt' => $this->faker->sentence(22),
                'body' => $body,
                'status' => $status->value,
                'is_featured' => $this->faker->boolean(10),
                'reading_minutes' => max(1, (int) ceil(str_word_count($body) / 200)),
                'views' => $published ? (int) ($this->faker->numberBetween(80, 4000) * (1 + $published->diffInDays(now()) / 120)) : 0,
                'published_at' => $published && $published->isFuture() ? now()->subHour() : $published,
                'created_at' => $created,
                'updated_at' => $created->copy()->addDays($this->faker->numberBetween(0, 4)),
            ];
            foreach ($this->faker->randomElements($tagIds, $this->faker->numberBetween(1, 4)) as $tagId) {
                $pivot[] = ['post_id' => $id, 'tag_id' => $tagId];
            }
        }

        foreach (array_chunk($posts, 50) as $chunk) {
            DB::table('posts')->insert($chunk);
        }
        DB::table('post_tag')->insert($pivot);
    }

    /** A markdown article: intro, sections, a list, sometimes a quote or code. */
    private function body(): string
    {
        $parts = [$this->faker->paragraph(5)];
        $sections = $this->faker->numberBetween(2, 4);
        for ($s = 0; $s < $sections; $s++) {
            $parts[] = '## '.Str::ucfirst($this->faker->words($this->faker->numberBetween(2, 5), true));
            $parts[] = $this->faker->paragraph(6);
            if ($this->faker->boolean(50)) {
                $parts[] = implode("\n", array_map(
                    fn () => '- '.Str::ucfirst($this->faker->words($this->faker->numberBetween(3, 8), true)),
                    range(1, $this->faker->numberBetween(3, 5)),
                ));
            }
            if ($this->faker->boolean(25)) {
                $parts[] = '> '.$this->faker->sentence(16);
            }
            if ($this->faker->boolean(30)) {
                $parts[] = "```php\n".$this->faker->randomElement([
                    "Input::make('title')->required(),\nSelect::make('status')->options(\$statuses),",
                    "TableColumn::make('total')->asMoney('USD')->sort(),",
                    "Schedule::command('demo:reset')->hourly();",
                    '$order->transitionTo(OrderStatus::Shipped);',
                ])."\n```";
            }
            $parts[] = $this->faker->paragraph(4);
        }

        return implode("\n\n", $parts);
    }
}
