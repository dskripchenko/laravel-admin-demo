<?php

namespace App\Admin\Showcase\Actions;

use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A small CSV file the download examples hand out: generated on the fly,
 * behind a temporary signed URL, so nothing is written to disk.
 */
final class SampleCsv
{
    /** A link valid for ten minutes. */
    public static function url(): string
    {
        return URL::temporarySignedRoute('showcase.sample-csv', now()->addMinutes(10));
    }

    public function __invoke(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['sku', 'name', 'price', 'stock']);
            foreach (range(1, 20) as $i) {
                fputcsv($out, [sprintf('SKU-%04d', $i), "Sample product {$i}", number_format($i * 4.75, 2, '.', ''), ($i * 7) % 50]);
            }
            fclose($out);
        }, 'showcase-sample.csv', ['Content-Type' => 'text/csv']);
    }
}
