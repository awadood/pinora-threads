<?php

namespace App\Repositories\Tax;

use App\Models\TaxCalculation;
use App\Repositories\BaseRepository;
use App\Repositories\Tax\Contracts\ITaxCalculationRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * TaxCalculationRepository
 *
 * Eloquent-based repository for tax_calculations, including
 * finder methods that the Tax Engine uses to resolve which
 * rules/rates apply to a given line item and geography.
 *
 * @author Abdul Wadood
 */
class TaxCalculationRepository extends BaseRepository implements ITaxCalculationRepository
{
    protected string $modelClass = TaxCalculation::class;

    /**
     * @var array<string, true>
     */
    protected array $allowedSearchColumns = [
        'tax_rate_id' => true,
        'tax_rule_id' => true,
        'user_tax_class_id' => true,
        'product_tax_class_id' => true,
    ];

    public function findApplicable(
        int $userTaxClassId,
        int $productTaxClassId,
        string $countryCode,
        ?string $stateCode,
        string $zipcode
    ): Collection {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->query()
            ->with(['taxRate', 'taxRule'])
            ->where('user_tax_class_id', $userTaxClassId)
            ->where('product_tax_class_id', $productTaxClassId)
            ->whereHas('taxRule', function ($q) {
                $q->where('active', true);
            })
            ->whereHas('taxRate', function ($q) use ($countryCode, $stateCode, $zipcode) {
                $q->where('country_code', $countryCode)
                    ->where('active', true)
                    ->where(function ($q2) use ($stateCode) {
                        if ($stateCode !== null && $stateCode !== '') {
                            $q2->where('state_code', $stateCode)
                                ->orWhereNull('state_code');
                        } else {
                            $q2->whereNull('state_code');
                        }
                    })
                    ->where(function ($q3) use ($zipcode) {
                        $q3->where(function ($q4) use ($zipcode) {
                            $q4->where('zip_is_range', false)
                                ->where(function ($q5) use ($zipcode) {
                                    $q5->where('zipcode', $zipcode)
                                        ->orWhere('zipcode', '*');
                                });
                        })->orWhere(function ($q4) use ($zipcode) {
                            $q4->where('zip_is_range', true)
                                ->where('zip_from', '<=', $zipcode)
                                ->where('zip_to', '>=', $zipcode);
                        });
                    });
            });

        /** @var Collection<int,TaxCalculation> $result */
        $result = $query->get();

        return $result;
    }
}
