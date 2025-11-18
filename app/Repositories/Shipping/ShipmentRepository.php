<?php

namespace App\Repositories\Shipping;

use App\Models\Shipment;
use App\Repositories\BaseRepository;
use App\Repositories\Shipping\Contracts\IShipmentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * ShipmentRepository
 *
 * Eloquent implementation of IShipmentRepository.
 * Extends BaseRepository for common CRUD + safe search.
 *
 * @author Abdul Wadood
 */
class ShipmentRepository extends BaseRepository implements IShipmentRepository
{
    /**
     * @var class-string<Model>
     */
    protected string $modelClass = Shipment::class;

    /**
     * Whitelisted columns for search() if needed.
     *
     * @var array<string, true>
     */
    protected array $allowedSearchColumns = [
        'order_id' => true,
        'stock_id' => true,
        'shipment_status_code' => true,
        'shipment_method_code' => true,
        'carrier' => true,
    ];

    /**
     * Override create to avoid injecting 'active' into models
     * that do not have such a column.
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function findByOrderId(int $orderId): ?Shipment
    {
        /** @var Shipment|null $shipment */
        $shipment = $this->query()
            ->where('order_id', $orderId)
            ->first();

        return $shipment;
    }

    /**
     * Simple filtered list for admin index.
     *
     * @param  array<string,mixed>  $filters
     * @return Collection<int,Shipment>
     */
    public function filteredList(array $filters): Collection
    {
        $q = $this->query();

        if (! empty($filters['order_id'])) {
            $q->where('order_id', (int) $filters['order_id']);
        }

        if (! empty($filters['shipment_status_code'])) {
            $q->where('shipment_status_code', $filters['shipment_status_code']);
        }

        if (! empty($filters['stock_id'])) {
            $q->where('stock_id', (int) $filters['stock_id']);
        }

        if (! empty($filters['shipment_method_code'])) {
            $q->where('shipment_method_code', $filters['shipment_method_code']);
        }

        if (! empty($filters['carrier'])) {
            $q->where('carrier', $filters['carrier']);
        }

        $q->orderByDesc('created_at');

        /** @var Collection<int,Shipment> $result */
        $result = $q->get();

        return $result;
    }
}
