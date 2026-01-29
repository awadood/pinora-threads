<?php

namespace App\Repositories\Storefront\Contracts;

use App\Models\MerchSection;

interface IMerchandisingRepository
{
    public function findActiveSectionByCode(string $code, ?string $countryCode = null): ?MerchSection;
}
