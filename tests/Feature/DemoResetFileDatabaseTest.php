<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\TestCase;

/**
 * The stand runs on a SQLite file in WAL mode, with the queue worker and the
 * scheduler holding connections open while the hourly reset rebuilds it. The
 * reset must not truncate the file under them: migrate:fresh did, and every
 * open connection then read "database is locked" until a restart.
 */
class DemoResetFileDatabaseTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = tempnam(sys_get_temp_dir(), 'demo-reset').'.sqlite';
        touch($this->path);
        config(['database.connections.sqlite.database' => $this->path]);
        DB::purge('sqlite');
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        foreach (['', '-wal', '-shm'] as $suffix) {
            @unlink($this->path.$suffix);
        }

        parent::tearDown();
    }

    public function test_a_connection_held_through_the_reset_keeps_working(): void
    {
        Storage::fake('public');
        $this->artisan('demo:reset', ['--force' => true])->assertSuccessful();

        // Another process: the queue worker, say — connected and in WAL mode.
        $worker = new PDO('sqlite:'.$this->path);
        $worker->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $worker->exec('pragma journal_mode = wal; pragma busy_timeout = 5000');
        $before = (int) $worker->query('select count(*) from products')->fetchColumn();
        $this->assertGreaterThan(0, $before);

        $inode = fileinode($this->path);
        $this->artisan('demo:reset', ['--force' => true])->assertSuccessful();

        // Same file, not truncated and recreated…
        $this->assertSame($inode, fileinode($this->path));
        $this->assertGreaterThan(0, filesize($this->path));
        // …and the connection that stayed open reads the rebuilt data.
        $this->assertGreaterThan(0, (int) $worker->query('select count(*) from products')->fetchColumn());
        $worker->exec('insert into sessions (id, payload, last_activity) values (\'probe\', \'\', 0)');
    }
}
