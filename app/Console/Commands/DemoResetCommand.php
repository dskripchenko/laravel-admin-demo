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

        // Uploaded and generated media live on the public disk; the seeder
        // draws the demo images again.
        Storage::disk((string) config('admin-media.disk', 'public'))
            ->deleteDirectory((string) config('admin-media.path_prefix', 'media'));
        Storage::disk((string) config('admin.uploads.disk', 'local'))
            ->deleteDirectory((string) config('admin.uploads.directory', 'uploads'));

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
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
