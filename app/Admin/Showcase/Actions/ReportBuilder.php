<?php

namespace App\Admin\Showcase\Actions;

/**
 * The background handler of the "Background jobs" example. It runs in a queue
 * worker as a delayed process; the panel polls its status and shows the
 * returned message when it is done. Nothing is written anywhere.
 */
final class ReportBuilder
{
    /**
     * Pretends to build a report: a short pause per batch of rows.
     *
     * @return array{message: string, rows: int}
     */
    public function build(int $rows = 500, string $format = 'csv'): array
    {
        $rows = max(1, min($rows, 2000));
        foreach (range(1, (int) ceil($rows / 100)) as $_batch) {
            usleep(300_000);
        }

        return [
            'message' => __('The report is ready: :rows rows as :format.', ['rows' => $rows, 'format' => strtoupper($format)]),
            'rows' => $rows,
        ];
    }
}
