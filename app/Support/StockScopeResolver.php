<?php

namespace App\Support;

use App\Models\Stock;
use Illuminate\Support\Facades\Cache;

class StockScopeResolver
{
    /**
     * @return array<int,int> stock IDs in priority order
     */
    public function forCountry(string $countryCode): array
    {
        $cacheKey = "stock_scope:{$countryCode}";

        // Cache for a short period; stocks don't change frequently.
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($countryCode) {
            return Stock::where('country_code', $countryCode)
                ->where('active', true)
                ->orderBy('priority', 'asc')
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        });
    }
}
