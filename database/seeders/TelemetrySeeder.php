<?php

namespace Database\Seeders;

use App\Support\PulseRecorders;
use Dskripchenko\LaravelAdminPulse\Services\Aggregator;
use Dskripchenko\LaravelAdminPulse\Services\Sampler;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDOException;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * A day of telemetry for the Telemetry dashboard and the samples list.
 *
 * The samples are written through the pulse pack's Sampler at their moment of
 * the day, with the keys the stand writes itself: the middleware's
 * "METHOD uri" per request (one sample in ten, the default rate), query
 * fingerprints, and the jobs and exceptions of App\Support\PulseRecorders.
 * The traffic follows the working day; seven hours ago the SQLite file was
 * locked for a few minutes, which shows as a spike of 5xx, slow responses and
 * QueryExceptions. Then every five-minute window of the day is aggregated by
 * the pack's Aggregator, as `admin:pulse:aggregate` does on schedule, and
 * `admin:pulse:rotate` drops what is past the retention — among it the job
 * samples SystemSeeder recorded for the earlier days of the week.
 *
 * The randomness has a fixed seed: a reset at the same hour draws the same day.
 */
class TelemetrySeeder extends Seeder
{
    /** route => [average ms, share of the traffic] */
    private const ROUTES = [
        'GET admin/{any?}' => [38, 14],
        'GET api/admin/system/bootstrap' => [64, 8],
        'GET api/admin/system/manifest' => [142, 8],
        'GET api/admin/system/menu' => [31, 6],
        'GET api/admin/system/status' => [24, 12],
        'GET api/admin/notifications/unread' => [14, 12],
        'GET api/admin/dashboard/get' => [45, 4],
        'GET api/admin/dashboard/widgets' => [236, 4],
        'POST api/admin/orders/search' => [98, 7],
        'POST api/admin/orders/show' => [57, 3],
        'POST api/admin/products/search' => [84, 6],
        'POST api/admin/products/show' => [49, 2],
        'POST api/admin/customers/search' => [72, 3],
        'POST api/admin/posts/search' => [66, 3],
        'POST api/admin/media-library/search' => [61, 1],
        'GET api/admin/audit/list' => [53, 1],
        'POST api/admin/auth/login' => [182, 2],
        'GET /' => [17, 4],
    ];

    /** SQL the panel runs, before fingerprinting => average ms */
    private const QUERIES = [
        'select * from "orders" order by "placed_at" desc limit 25 offset 0' => 4,
        'select count(*) as aggregate from "orders" where "status" = \'paid\'' => 3,
        'select * from "products" where ("name" like \'%lamp%\' or "sku" like \'%lamp%\') order by "id" desc limit 25 offset 0' => 6,
        'select strftime(\'%w\', "placed_at") as dow, strftime(\'%H\', "placed_at") as hour, count(*) as orders from "orders" where "placed_at" >= \'2026-09-02 00:00:00\' group by dow, hour' => 41,
        'select "products"."id", "products"."name", sum("order_items"."quantity") as sold from "order_items" inner join "products" on "products"."id" = "order_items"."product_id" group by "products"."id" order by sold desc limit 5' => 27,
        'select sum("total") as revenue, strftime(\'%Y-%m\', "placed_at") as month from "orders" group by month order by month' => 33,
        'select * from "customers" where "customers"."id" in (12, 48, 301)' => 1,
        'select * from "posts" where "posts"."deleted_at" is null order by "published_at" desc limit 25 offset 0' => 3,
        'select * from "users" where "id" = 1 limit 1' => 1,
        'select * from "sessions" where "id" = \'q2X0vZ9mA4\' limit 1' => 1,
        'update "sessions" set "payload" = \'YToz\', "last_activity" = 1790000000 where "id" = \'q2X0vZ9mA4\'' => 2,
        'select count(*) as aggregate from "notifications" where "notifiable_id" = 3 and "read_at" is null' => 1,
    ];

    /**
     * Jobs queued by the panel's own traffic => [average ms, share]. The
     * nightly stock sync, the batches and the failures are SystemSeeder's: it
     * runs those jobs, and PulseRecorders samples them.
     */
    private const JOBS = [
        'App\Jobs\SendOrderConfirmation' => [180, 3],
        'App\Jobs\IndexBlogPost' => [9, 2],
    ];

    private Randomizer $random;

    public function run(Sampler $sampler, Aggregator $aggregator): void
    {
        $this->random = new Randomizer(new Mt19937(20261002));
        $now = Carbon::now();
        $from = $now->copy()->subDay();
        $incident = [$now->copy()->subMinutes(424), $now->copy()->subMinutes(409)];

        try {
            DB::transaction(function () use ($sampler, $aggregator, $now, $from, $incident): void {
                for ($minute = $from->copy()->startOfMinute()->addMinute(); $minute < $now; $minute->addMinute()) {
                    $locked = $minute >= $incident[0] && $minute < $incident[1];
                    $this->minute($sampler, $minute, $this->traffic($minute), $locked);
                }

                for ($start = $from->copy()->ceil('5minutes'); $start->copy()->addMinutes(5) <= $now; $start->addMinutes(5)) {
                    Carbon::setTestNow($start->copy()->addMinutes(5)->addSeconds(4));
                    $aggregator->aggregate($start->copy(), $start->copy()->addMinutes(5));
                }
            });
        } finally {
            Carbon::setTestNow();
        }

        Artisan::call('admin:pulse:rotate');
    }

    /** Requests a minute, sampled: quiet at night, busiest in the afternoon. */
    private function traffic(Carbon $minute): float
    {
        $hour = $minute->hour + $minute->minute / 60;

        return 0.35 + 2.6 * exp(-(($hour - 14.5) ** 2) / 18);
    }

    private function minute(Sampler $sampler, Carbon $minute, float $rate, bool $locked): void
    {
        $requests = $this->poisson($rate * ($locked ? 1.6 : 1.0));
        for ($i = 0; $i < $requests; $i++) {
            Carbon::setTestNow($minute->copy()->addSeconds($this->random->getInt(0, 59)));
            $route = $this->pick(array_map(fn (array $r) => $r[1], self::ROUTES));
            $method = strstr($route, ' ', true);
            $ms = $this->duration(self::ROUTES[$route][0]);
            $status = 200;

            if ($locked && ! str_starts_with($route, 'GET admin') && $this->chance(0.35)) {
                $ms += $this->random->getInt(4000, 5100);
                $status = 500;
                PulseRecorders::recordException($this->lockedDatabase($method === 'GET' ? 'select' : 'update'));
            } elseif ($route === 'POST api/admin/auth/login' && $this->chance(0.18)) {
                $status = 422;
            } elseif ($method === 'POST' && $this->chance(0.01)) {
                $status = 404;
            } elseif ($locked) {
                $ms *= 3;
            }

            $sampler->record('request', $route, $ms, $method, $status, ['memory_peak_mb' => round(22 + $this->random->getFloat(0, 18), 1)]);
        }

        // Queries: about as many samples as requests, mostly sub-millisecond.
        for ($i = $this->poisson($rate * 0.9); $i > 0; $i--) {
            Carbon::setTestNow($minute->copy()->addSeconds($this->random->getInt(0, 59)));
            $sql = $this->pick(array_map(fn () => 1, self::QUERIES));
            $sampler->record('query', mb_substr($sampler->fingerprintSql($sql), 0, 255), $this->duration(self::QUERIES[$sql]) * ($locked ? 4 : 1));
        }

        // Jobs: a few a minute in the daytime, slow while the file is locked.
        for ($i = $this->poisson($rate * 0.3); $i > 0; $i--) {
            Carbon::setTestNow($minute->copy()->addSeconds($this->random->getInt(0, 59)));
            $job = $this->pick(array_map(fn (array $j) => $j[1], self::JOBS));
            PulseRecorders::recordJob($job, false, $this->duration(self::JOBS[$job][0]) * ($locked ? 5 : 1));
        }

        if ($this->chance(0.02)) {
            Carbon::setTestNow($minute->copy()->addSeconds($this->random->getInt(0, 59)));
            $sampler->record('cache', 'admin-pulse:telemetry:summary', $this->random->getInt(0, 2), 'hit');
        }
    }

    private function lockedDatabase(string $statement): QueryException
    {
        $sql = $statement === 'select'
            ? 'select * from "orders" order by "placed_at" desc limit 25 offset 0'
            : 'update "sessions" set "last_activity" = ? where "id" = ?';

        return new QueryException('sqlite', $sql, [], new PDOException('SQLSTATE[HY000]: General error: 5 database is locked'));
    }

    /** A response time around the average: log-normal, with the odd slow one. */
    private function duration(int $average): int
    {
        $u1 = max(1e-9, $this->random->getFloat(0, 1));
        $u2 = $this->random->getFloat(0, 1);
        $normal = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
        $ms = $average * exp(0.45 * $normal - 0.1);
        if ($this->chance(0.01)) {
            $ms *= $this->random->getFloat(4, 9);
        }

        return max(0, (int) round($ms));
    }

    /**
     * @param  array<string, int>  $weights
     */
    private function pick(array $weights): string
    {
        $roll = $this->random->getInt(1, array_sum($weights));
        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    private function chance(float $p): bool
    {
        return $this->random->getFloat(0, 1) < $p;
    }

    private function poisson(float $lambda): int
    {
        $limit = exp(-$lambda);
        $k = 0;
        $p = $this->random->getFloat(0, 1);
        while ($p > $limit) {
            $k++;
            $p *= $this->random->getFloat(0, 1);
        }

        return $k;
    }
}
