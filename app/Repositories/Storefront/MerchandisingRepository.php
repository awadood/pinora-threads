<?php

namespace App\Repositories\Storefront;

use App\Models\MerchSection;
use App\Repositories\Storefront\Contracts\IMerchandisingRepository;

class MerchandisingRepository implements IMerchandisingRepository
{
    public function findActiveSectionByCode(string $code, ?string $countryCode = null): ?MerchSection
    {
        // 1) Try country-specific
        if ($countryCode) {
            $specific = MerchSection::query()
                ->where('code', $code)
                ->where('country_code', $countryCode)
                ->active()
                ->withinSchedule()
                ->with(['items'])
                ->first();

            if ($specific) {
                return $specific;
            }
        }

        // 2) Fallback to global
        return MerchSection::query()
            ->where('code', $code)
            ->whereNull('country_code')
            ->active()
            ->withinSchedule()
            ->with(['items'])
            ->first();
    }
}
