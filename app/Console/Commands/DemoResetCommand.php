<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Puts the demo stand back to its initial state: a fresh database with the
 * seed data, the generated images, the admin frontend and empty caches.
 * Scheduled hourly in routes/console.php.
 */
final class DemoResetCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'demo:reset {--force : Run without confirmation in production}';

    protected $description = 'Reset the demo stand: fresh database, seed data, media, caches';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $started = microtime(true);

        // The stand serves a public site on SQLite: sessions, the queue and the
        // health checks all write to it. A migrate:fresh racing those writers
        // timed out on "database is locked" halfway through and left a broken
        // schema behind (2026-10-26). Maintenance mode stops all of them —
        // requests get a short "resetting" page, the queue worker pauses and
        // scheduled commands skip — and the reset has the file to itself.
        $wasDown = app()->isDownForMaintenance();
        if (! $wasDown) {
            $this->callSilently('down', ['--render' => 'demo.resetting', '--retry' => 20, '--refresh' => 8]);
            // Requests already inside the app finish their writes.
            sleep(2);
        }

        try {
            return $this->reset($started);
        } finally {
            if (! $wasDown) {
                $this->callSilently('up');
            }
        }
    }

    private function reset(float $started): int
    {

        // Uploaded and generated media live on the public disk; the seeder
        // draws the demo images again.
        Storage::disk((string) config('admin-media.disk', 'public'))
            ->deleteDirectory((string) config('admin-media.path_prefix', 'media'));
        Storage::disk((string) config('admin.uploads.disk', 'local'))
            ->deleteDirectory((string) config('admin.uploads.directory', 'uploads'));

        // A writer that slipped in before the switch can still hold the lock
        // for a moment: try again rather than leave a half-built schema.
        $attempts = 0;
        while (true) {
            try {
                $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
                break;
            } catch (\Throwable $e) {
                if (++$attempts >= 3 || ! str_contains($e->getMessage(), 'database is locked')) {
                    throw $e;
                }
                $this->components->warn('The database is busy, retrying the reset…');
                sleep(5);
            }
        }
        $this->call('admin:publish');

        if (! File::exists(public_path('storage'))) {
            $this->callSilently('storage:link');
        }

        $this->call('cache:clear');
        // So the health indicator has results from the first minute.
        $this->callSilently('admin:health:run');

        $this->components->info(sprintf('Demo reset in %.1fs.', microtime(true) - $started));

        return self::SUCCESS;
    }
}
