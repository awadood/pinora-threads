<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shipping\ShipmentRequest;
use App\Http\Requests\Shipping\ShipmentStatusRequest;
use App\Http\Resources\Shipping\ShipmentResource;
use App\Models\Order;
use App\Models\Shipment;
use App\Repositories\Shipping\Contracts\IShipmentRepository;
use App\Services\Shipping\ShipmentService;
use Illuminate\Http\Request;

/**
 * ShipmentController
 *
 * Customer:
 *  - View shipment for their own order.
 *
 * Admin:
 *  - List shipments
 *  - View shipment details
 *  - Create shipment for an order
 *  - Update shipment details
 *  - Update shipment status
 *
 * @author Abdul Wadood
 */
class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $service,
        protected IShipmentRepository $shipments
    ) {}

    /**
     * GET /api/orders/{order}/shipment
     * Customer-facing: show shipment for their own order.
     */
    public function shipmentForOrder(Request $request, Order $order)
    {
        $user = $request->user();

        $shipment = $this->service->findCustomerShipmentForOrder($user->id, $order);

        if (! $shipment) {
            abort(404);
        }

        return new ShipmentResource($shipment);
    }

    /**
     * GET /api/admin/shipments
     * Admin listing with simple filters.
     */
    public function index(Request $request)
    {
        $filters = [
            'order_id' => $request->query('order_id'),
            'shipment_status_code' => $request->query('status'),
            'stock_id' => $request->query('stock_id'),
            'shipment_method_code' => $request->query('method'),
            'carrier' => $request->query('carrier'),
        ];

        $shipments = $this->shipments->filteredList($filters);

        return ShipmentResource::collection($shipments);
    }

    /**
     * GET /api/admin/shipments/{shipment}
     */
    public function show(Shipment $shipment)
    {
        return new ShipmentResource($shipment);
    }

    /**
     * POST /api/admin/orders/{order}/shipments
     */
    public function storeForOrder(ShipmentRequest $request, Order $order)
    {
        $shipment = $this->service->createForOrder($order, $request->validated());

        return (new ShipmentResource($shipment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PATCH /api/admin/shipments/{shipment}
     */
    public function update(ShipmentRequest $request, Shipment $shipment)
    {
        $shipment = $this->service->updateShipment($shipment, $request->validated());

        return new ShipmentResource($shipment);
    }

    /**
     * PATCH /api/admin/shipments/{shipment}/status
     */
    public function updateStatus(ShipmentStatusRequest $request, Shipment $shipment)
    {
        $shipment = $this->service->updateStatus(
            $shipment,
            $request->input('shipment_status_code')
        );

        return new ShipmentResource($shipment);
    }
}
