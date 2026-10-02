<?php

namespace Tests\Feature;

use Dskripchenko\LaravelAdminHealth\Models\HealthResultRecord;
use Dskripchenko\LaravelAdminJobs\Models\FailedJob;
use Dskripchenko\LaravelAdminJobs\Models\JobBatch;
use Dskripchenko\LaravelAdminJobs\Services\JobOperations;
use Dskripchenko\LaravelAdminPulse\Models\PulseAggregate;
use Dskripchenko\LaravelAdminPulse\Models\PulseSample;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The System pages show the sister packs at work: none of them may be empty
 * after a reset, for anyone allowed to open them.
 */
class SystemPagesTest extends DemoTestCase
{
    /** @return array<string, array{string, int}> resource => the fewest rows it must list */
    public static function systemResources(): array
    {
        return [
            'telemetry samples' => ['system-pulse-samples', 1000],
            'failed jobs' => ['system-failed-jobs', 8],
            'batches' => ['system-job-batches', 3],
            'health history' => ['system-health-results', 500],
            'audit log' => ['system-audit', 30],
        ];
    }

    #[DataProvider('systemResources')]
    public function test_every_system_list_has_rows_for_the_administrator_and_the_viewer(string $resource, int $atLeast): void
    {
        foreach (['admin', 'viewer'] as $role) {
            $this->loginAs($role);
            $total = $this->postJson("/api/admin/{$resource}/search", [])->assertOk()->json('payload.meta.total');
            $this->assertGreaterThanOrEqual($atLeast, $total, "{$resource} as {$role}");
            $this->postJson('/api/admin/auth/logout')->assertOk();
        }
    }

    public function test_the_editor_has_no_system_pages(): void
    {
        $this->loginAs('editor');

        foreach (array_column(self::systemResources(), 0) as $resource) {
            $this->postJson("/api/admin/{$resource}/search", [])->assertForbidden();
        }
    }

    public function test_the_telemetry_dashboard_draws_a_day(): void
    {
        $this->loginAs('viewer');

        $widgets = collect($this->getJson('/api/admin/dashboard/widgets?key=telemetry')->assertOk()->json('payload.widgets'))->keyBy('slug');
        $this->assertNotEmpty($widgets);
        foreach ($widgets as $slug => $widget) {
            $this->assertNotEmpty(array_filter((array) ($widget['data'] ?? [])), "widget {$slug} has no data");
        }

        // Aggregated by the pack's own aggregator, five-minute windows over the day.
        $this->assertGreaterThan(250, PulseAggregate::query()->where('bucket', 'requests')->count());
        foreach (['request', 'query', 'job', 'exception'] as $kind) {
            $this->assertTrue(PulseSample::query()->where('kind', $kind)->exists(), "no {$kind} samples");
        }
        // Rotated: nothing older than the retention.
        $this->assertFalse(PulseSample::query()->where('sampled_at', '<', Carbon::now()->subDay())->exists());
    }

    public function test_the_batches_are_finished_failed_and_in_progress(): void
    {
        $batches = JobBatch::query()->get();

        $this->assertTrue($batches->contains(fn (JobBatch $b) => $b->finished_at !== null && $b->failed_jobs === 0));
        // Laravel leaves a batch with failures unfinished: its failed jobs stay pending.
        $this->assertTrue($batches->contains(fn (JobBatch $b) => $b->failed_jobs > 0 && $b->pending_jobs === $b->failed_jobs));
        $this->assertTrue($batches->contains(fn (JobBatch $b) => $b->finished_at === null && $b->pending_jobs > $b->failed_jobs && $b->pending_jobs < $b->total_jobs));
    }

    public function test_the_failed_jobs_are_the_demo_jobs_with_different_exceptions(): void
    {
        $jobs = FailedJob::query()->get();

        $this->assertCount(8, $jobs);
        $this->assertGreaterThanOrEqual(4, $jobs->map(fn (FailedJob $job) => $job->exception_class)->unique()->count());
        $this->assertTrue($jobs->every(fn (FailedJob $job) => str_starts_with($job->jobName(), 'App\\Jobs\\')));
        $this->assertSame(['default', 'high', 'low'], $jobs->pluck('queue')->unique()->sort()->values()->all());
    }

    public function test_retrying_every_failed_job_is_harmless(): void
    {
        $operations = app(JobOperations::class);
        foreach (FailedJob::query()->pluck('uuid') as $uuid) {
            try {
                $operations->retryFailedJob($uuid);
            } catch (ModelNotFoundException) {
                // queue:retry restores the job to read its retryUntil, and a
                // deleted order cannot be restored: the job stays failed.
            }
        }
        Artisan::call('queue:work', ['connection' => 'database', '--queue' => 'high,default,low', '--stop-when-empty' => true, '--tries' => 1]);

        // What went wrong for good is still failed — the deleted orders and
        // the missing price lists; the rest went through.
        $left = FailedJob::query()->get()->map(fn (FailedJob $job) => class_basename($job->jobName()))->sort()->values()->all();
        $this->assertSame(['ImportSupplierPriceList', 'ImportSupplierPriceList', 'SendOrderConfirmation', 'SendOrderConfirmation'], $left);
    }

    public function test_the_health_history_spans_the_week(): void
    {
        // The queue check's warnings during the blog reindex need the database
        // queue as the default connection, as on the stand; tests run on sync.
        $this->assertTrue(HealthResultRecord::query()->where('ran_at', '<', Carbon::now()->subDays(6))->exists());
        $this->assertSame(4, HealthResultRecord::query()->distinct()->count('check_id'));
        $this->assertGreaterThan(0, DB::table('jobs')->count(), 'the newsletter still has chunks to send');
    }
}
