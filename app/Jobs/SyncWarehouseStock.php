<?php

namespace App\Jobs;

use App\Models\Shop\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

/**
 * Compares the stock of a chunk of products with the warehouse's figures.
 *
 * A batch of these runs per sync. The seed replays a night when the
 * warehouse timed out; the stand itself has no warehouse
 * (`services.warehouse.endpoint` is empty), so here the job reads the chunk
 * from the catalogue and succeeds — which is what a retry from the Jobs page
 * does.
 */
final class SyncWarehouseStock implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    /** @param  list<int>  $productIds */
    public function __construct(public array $productIds) {}

    /** @return array<string, int> the stock per SKU the warehouse disagrees about */
    public function handle(): array
    {
        if ($this->batch()?->cancelled()) {
            return [];
        }

        $stock = Product::query()->whereKey($this->productIds)->pluck('stock', 'sku')->all();

        $endpoint = config('services.warehouse.endpoint');
        if (! is_string($endpoint) || $endpoint === '') {
            return [];
        }

        $remote = Http::timeout(10)->get(rtrim($endpoint, '/').'/stock', ['sku' => array_keys($stock)])->throw()->json('stock', []);

        return array_filter(
            (array) $remote,
            fn (mixed $count, string $sku) => isset($stock[$sku]) && (int) $count !== (int) $stock[$sku],
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
