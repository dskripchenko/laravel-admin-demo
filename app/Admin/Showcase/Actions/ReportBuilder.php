<?php

namespace App\Admin\Showcase\Actions;

use Dskripchenko\DelayedProcess\Contracts\ProcessProgressInterface;

/**
 * The background handler of the "Background jobs" example. It runs in a queue
 * worker as a delayed process and reports its progress after every batch; the
 * panel polls the status, draws the progress bar and shows the returned
 * message when it is done. Nothing is written anywhere.
 */
final class ReportBuilder
{
    public function __construct(private readonly ProcessProgressInterface $progress) {}

    /**
     * Pretends to build a report: a short pause per batch of rows.
     *
     * @return array{message: string, rows: int}
     */
    public function build(int $rows = 500, string $format = 'csv'): array
    {
        $rows = max(1, min($rows, 2000));
        $batches = (int) ceil($rows / 100);
        foreach (range(1, $batches) as $batch) {
            usleep(300_000);
            // Outside a delayed-process run (a direct call) this does nothing.
            $this->progress->setProgress(intdiv($batch * 100, $batches));
        }

        return [
            'message' => __('The report is ready: :rows rows as :format.', ['rows' => $rows, 'format' => strtoupper($format)]),
            'rows' => $rows,
        ];
    }
}
