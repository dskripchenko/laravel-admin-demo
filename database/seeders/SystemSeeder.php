<?php

namespace Database\Seeders;

use App\Jobs\ImportSupplierPriceList;
use App\Jobs\IndexBlogPost;
use App\Jobs\SendNewsletterChunk;
use App\Jobs\SendOrderConfirmation;
use App\Jobs\SyncWarehouseStock;
use App\Models\Blog\Post;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Support\PulseRecorders;
use Closure;
use Dskripchenko\LaravelAdminHealth\HealthRunner;
use Dskripchenko\LaravelAdminJobs\Models\JobBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * The last week of the stand's back office, replayed in order: the queue with
 * its batches and failures, and the health checks that watched it.
 *
 * Nothing is inserted by hand. Every step runs at its moment of the week
 * (Carbon's test clock) through the code that runs in production: jobs are
 * pushed to the database queue, popped and fired the way the worker does, a
 * failure goes through Job::fail() and the failed-job provider, batches move
 * through Laravel's batch repository, and the checks are run by the health
 * pack's runner. So the health history reacts to the queue for real — the
 * blog reindex three days ago floods the default queue and the queue check
 * reports it until the backlog drains.
 *
 * What is left at the end: eight failed jobs to retry or forget, finished
 * batches, a batch with failures and a newsletter still being sent (its
 * chunks are released a few minutes apart, so a running worker finishes it).
 */
class SystemSeeder extends Seeder
{
    private const CONNECTION = 'database';

    /** @var list<array{int, int, Closure(): void}> [timestamp, order, step] */
    private array $timeline = [];

    /** @var array<string, array{created: int, finished: int|null}> by batch id */
    private array $batches = [];

    private bool $replaying = false;

    public function run(HealthRunner $health): void
    {
        $now = Carbon::now()->startOfMinute();

        $this->healthRuns($health, $now);
        $this->supplierImports($now);
        $this->orderConfirmations($now);
        $this->warehouseSyncs($now);
        $this->blogReindex($now);
        $this->newsletter($now);

        usort($this->timeline, fn (array $a, array $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        // What `queue:work` does on JobFailed: the failed job goes to the
        // failed_jobs table.
        Event::listen(JobFailed::class, function (JobFailed $event): void {
            if ($this->replaying) {
                app('queue.failer')->log($event->connectionName, $event->job->getQueue(), $event->job->getRawBody(), $event->exception);
            }
        });

        $this->replaying = true;
        try {
            DB::transaction(function (): void {
                foreach ($this->timeline as [$at, , $step]) {
                    Carbon::setTestNow(Carbon::createFromTimestamp($at));
                    $step();
                }
            });
        } finally {
            Carbon::setTestNow();
            $this->replaying = false;
        }

        // The batch repository stamps rows with time(), which the test clock
        // does not move.
        foreach ($this->batches as $id => $times) {
            JobBatch::query()->whereKey($id)->update(['created_at' => $times['created'], 'finished_at' => $times['finished']]);
        }
    }

    /**
     * The scheduler runs the checks every minute; the history keeps one run in
     * fifteen for the week and one in five for the last two hours.
     */
    private function healthRuns(HealthRunner $health, Carbon $now): void
    {
        $dense = $now->copy()->subHours(2);
        $at = $now->copy()->subDays(7)->addHour()->startOfHour();
        while ($at < $now) {
            $this->at($at, fn () => $health->runAll());
            $at->addMinutes($at < $dense ? 15 : 5);
        }
    }

    /** A supplier's price list that never made it to the disk — twice. */
    private function supplierImports(Carbon $now): void
    {
        $imports = [
            [$now->copy()->subDays(6)->setTimeFromTimeString('10:12'), 'nordic-textiles'],
            [$now->copy()->subMinutes(530), 'acme-outdoor'],
        ];
        foreach ($imports as [$at, $supplier]) {
            $path = "suppliers/{$supplier}-{$at->format('Y-m')}.csv";

            $this->at($at, fn () => $this->push(new ImportSupplierPriceList($path), 'low'));
            $this->at($at->copy()->addSeconds(3), fn () => $this->work('low'));
        }
    }

    /**
     * Two confirmations for orders deleted before the worker got to them (the
     * model is gone, so the job fails without running), and one that kept
     * timing out until the worker gave up on it — that one goes through on a
     * retry.
     */
    private function orderConfirmations(Carbon $now): void
    {
        $lastId = (int) Order::query()->max('id');

        foreach ([[$now->copy()->subDays(5)->setTimeFromTimeString('16:40'), 3], [$now->copy()->subMinutes(317), 11]] as [$at, $offset]) {
            $deleted = (new Order)->forceFill(['id' => $lastId + $offset]);
            $this->at($at, fn () => $this->push(new SendOrderConfirmation($deleted), 'default'));
            $this->at($at->copy()->addSeconds(2), fn () => $this->work('default'));
        }

        $at = $now->copy()->subDays(2)->setTimeFromTimeString('21:05');
        $order = Order::query()->where('placed_at', '<=', $at)->orderByDesc('placed_at')->firstOrFail();
        $this->at($at, fn () => $this->push(new SendOrderConfirmation($order), 'high'));
        $this->at($at->copy()->addMinutes(3), fn () => $this->work('high', fn (Job $job) => MaxAttemptsExceededException::forJob($job)));
    }

    /**
     * The nightly stock sync, a batch of chunks at three every morning. Four
     * nights ago the warehouse was down for a few minutes: three chunks timed
     * out, and they are still waiting for someone to retry them.
     */
    private function warehouseSyncs(Carbon $now): void
    {
        $chunks = Product::query()->orderBy('id')->pluck('id')->chunk(10)->map(fn ($ids) => $ids->values()->all())->values();

        for ($days = 6; $days >= 0; $days--) {
            $at = $now->copy()->subDays($days)->setTimeFromTimeString('03:00');
            if ($at->copy()->addMinutes(10) > $now) {
                continue;
            }
            $failing = $days === 4 ? [8, 9, 15] : [];

            $this->at($at, fn () => $this->batch(
                Bus::batch($chunks->map(fn (array $ids) => new SyncWarehouseStock($ids))->all())
                    ->name('Warehouse stock sync')->allowFailures()->onQueue('default'),
            ));

            foreach ($chunks->keys() as $i) {
                $timeout = in_array($i, $failing, true)
                    ? fn () => new ConnectionException('cURL error 28: Operation timed out after 10001 milliseconds with 0 bytes received (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://warehouse.example.com/api/v2/stock')
                    : null;
                $this->at($at->copy()->addSeconds(20 * ($i + 1)), fn () => $this->work('default', $timeout));
            }
        }
    }

    /**
     * A full reindex of the blog dropped on the default queue: one job per
     * post, more than the queue check tolerates, drained at a job a minute.
     */
    private function blogReindex(Carbon $now): void
    {
        $at = $now->copy()->subDays(3)->setTimeFromTimeString('07:50');
        $posts = Post::withTrashed()->orderBy('id')->pluck('id');

        $this->at($at, fn () => $this->batch(
            Bus::batch($posts->map(fn (int $id) => new IndexBlogPost($id))->all())
                ->name('Reindex blog posts')->onQueue('default'),
        ));

        foreach ($posts->keys() as $i) {
            $this->at($at->copy()->addMinutes($i + 1), fn () => $this->work('default'));
        }
    }

    /** This month's issue, released a chunk every three minutes; half of it is out. */
    private function newsletter(Carbon $now): void
    {
        $at = $now->copy()->subMinutes(32);
        $issue = 'The '.$at->format('F').' newsletter';
        $chunks = Customer::query()->where('accepts_marketing', true)->orderBy('id')->pluck('id')
            ->chunk(10)->map(fn ($ids) => $ids->values()->all())->values();

        $this->at($at, fn () => $this->batch(
            Bus::batch($chunks->map(fn (array $ids, int $i) => (new SendNewsletterChunk($issue, $ids))->delay($at->copy()->addMinutes(3 * $i)))->all())
                ->name($issue)->onQueue('default'),
        ));

        foreach ($chunks->keys() as $i) {
            $release = $at->copy()->addMinutes(3 * $i)->addSeconds(4);
            if ($release < $now) {
                $this->at($release, fn () => $this->work('default'));
            }
        }
    }

    /** @param  Closure(): void  $step */
    private function at(Carbon $at, Closure $step): void
    {
        $this->timeline[] = [$at->getTimestamp(), count($this->timeline), $step];
    }

    private function queue(): DatabaseQueue
    {
        /** @var DatabaseQueue */
        return Queue::connection(self::CONNECTION);
    }

    private function push(object $job, string $queue): void
    {
        $this->queue()->pushOn($queue, $job);
    }

    private function batch(PendingBatch $batch): void
    {
        $dispatched = $batch->onConnection(self::CONNECTION)->dispatch();
        $this->batches[$dispatched->id] = ['created' => Carbon::now()->getTimestamp(), 'finished' => null];
    }

    /**
     * One turn of the worker: pop the next job, fire it, record the outcome
     * the way Illuminate\Queue\Worker does. `$failure` stands in for what the
     * job would have run into — an outage, a worker that gave up on it.
     *
     * @param  (Closure(Job): Throwable)|null  $failure
     */
    private function work(string $queue, ?Closure $failure = null): void
    {
        $job = $this->queue()->pop($queue);
        if ($job === null) {
            return;
        }

        Event::dispatch(new JobProcessing(self::CONNECTION, $job));
        try {
            if ($failure !== null) {
                throw $failure($job);
            }
            $job->fire();
        } catch (Throwable $e) {
            PulseRecorders::recordException($e);
            $job->fail($e);
        }
        if (! $job->hasFailed()) {
            Event::dispatch(new JobProcessed(self::CONNECTION, $job));
        }

        $this->noteFinishedBatches();
    }

    private function noteFinishedBatches(): void
    {
        $open = array_keys(array_filter($this->batches, fn (array $times) => $times['finished'] === null));
        if ($open === []) {
            return;
        }

        foreach (JobBatch::query()->whereKey($open)->whereNotNull('finished_at')->pluck('id') as $id) {
            $this->batches[$id]['finished'] = Carbon::now()->getTimestamp();
        }
    }
}
