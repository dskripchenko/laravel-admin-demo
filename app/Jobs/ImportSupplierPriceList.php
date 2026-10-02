<?php

namespace App\Jobs;

use App\Models\Shop\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Reads a supplier's CSV price list (`sku,price`) from the local disk and
 * reports which catalogue prices differ from it.
 *
 * It only compares: the demo catalogue is never rewritten by a job. A file
 * that is not on the disk fails the job — retrying it fails the same way.
 */
final class ImportSupplierPriceList implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $path) {}

    /** @return array<string, float> the new price per SKU where it differs */
    public function handle(): array
    {
        $rows = array_map('str_getcsv', array_filter(explode("\n", File::get(Storage::disk('local')->path($this->path)))));
        $prices = [];
        foreach ($rows as $row) {
            if (count($row) >= 2 && is_numeric($row[1])) {
                $prices[(string) $row[0]] = (float) $row[1];
            }
        }

        return Product::query()->whereIn('sku', array_keys($prices))->get(['sku', 'price'])
            ->filter(fn (Product $product) => (float) $product->price !== $prices[$product->sku])
            ->mapWithKeys(fn (Product $product) => [$product->sku => $prices[$product->sku]])
            ->all();
    }
}
