<?php

namespace App\Support;

use Dskripchenko\LaravelAdminPulse\Services\Sampler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Throwable;

/**
 * The telemetry the pulse middleware cannot see: queued jobs and reported
 * exceptions, recorded the way the laravel-admin-pulse usage guide describes
 * (a job is keyed by its class, 500 marks a failure; an exception by its
 * fingerprint, labelled "Class: message").
 *
 * The demo seeder goes through the same methods, so the seeded samples are
 * the ones the stand writes by itself.
 */
final class PulseRecorders
{
    /** @var array<string, float> when each running job started, by job id */
    private static array $started = [];

    public static function register(): void
    {
        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            self::$started[(string) $event->job->getJobId()] = microtime(true);
        });
        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            self::recordJob($event->job->resolveName(), false, self::elapsed((string) $event->job->getJobId()));
        });
        Event::listen(JobFailed::class, function (JobFailed $event): void {
            self::recordJob($event->job->resolveName(), true, self::elapsed((string) $event->job->getJobId()));
        });
    }

    public static function recordJob(string $class, bool $failed, int $durationMs = 0): void
    {
        $sampler = app(Sampler::class);
        if (self::enabled() && $sampler->shouldSample('job')) {
            self::quietly(fn () => $sampler->record('job', $class, $durationMs, statusCode: $failed ? 500 : 200));
        }
    }

    public static function recordException(Throwable $e): void
    {
        $sampler = app(Sampler::class);
        if (self::enabled() && app(ExceptionHandler::class)->shouldReport($e) && $sampler->shouldSample('exception')) {
            self::quietly(fn () => $sampler->record(
                'exception',
                $sampler->fingerprintException($e),
                0,
                label: mb_substr(get_class($e).': '.$e->getMessage(), 0, 255),
            ));
        }
    }

    private static function enabled(): bool
    {
        return (bool) config('admin-pulse.enabled', true);
    }

    /** Telemetry never takes the job or the error report down with it. */
    private static function quietly(callable $record): void
    {
        try {
            $record();
        } catch (Throwable) {
            // No samples table yet (a fresh install) or the database is gone.
        }
    }

    private static function elapsed(string $jobId): int
    {
        $started = self::$started[$jobId] ?? null;
        unset(self::$started[$jobId]);

        return $started === null ? 0 : (int) round((microtime(true) - $started) * 1000);
    }
}
