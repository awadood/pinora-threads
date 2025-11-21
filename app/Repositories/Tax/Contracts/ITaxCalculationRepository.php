<?php

namespace App\Repositories\Tax\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * ITaxCalculationRepository
 *
 * Repository contract for the tax_calculations matrix which links
 * user tax class + product tax class to a specific tax rule + tax rate.
 *
 * This is what the Tax Engine uses to discover which rules/rates
 * should be applied for a given line item in a given geography.
 *
 * @author Abdul Wadood
 */
interface ITaxCalculationRepository extends IBaseRepository
{
    /**
     * Find all applicable tax calculations for a given combination of:
     *  - customer (user) tax class
     *  - product tax class
     *  - shipping/destination geography (country/state/zipcode)
     *
     * Results MUST include eager-loaded taxRule and taxRate relations.
     *
     * @return Collection<int,\App\Models\TaxCalculation>
     */
    public function findApplicable(
        int $userTaxClassId,
        int $productTaxClassId,
        string $countryCode,
        ?string $stateCode,
        string $zipcode
    ): Collection;
}
