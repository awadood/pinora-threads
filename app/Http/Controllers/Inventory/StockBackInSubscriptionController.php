<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockBackInSubscriptionRequest;
use App\Http\Resources\Inventory\StockBackInSubscriptionResource;
use App\Repositories\Inventory\Contracts\IStockBackInSubscriptionRepository;
use App\Services\Inventory\BackInStockNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockBackInSubscriptionController
 *
 * Manage "notify me when back in stock" subscriptions for variants.
 *
 * @author Abdul Wadood
 */
class StockBackInSubscriptionController extends Controller
{
    public function __construct(
        private readonly IStockBackInSubscriptionRepository $subscriptions,
        private readonly BackInStockNotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $criteria = [];

        if ($request->filled('variant_id')) {
            $criteria[] = ['col' => 'variant_id', 'op' => '=', 'value' => (int) $request->query('variant_id')];
        }

        if ($request->filled('user_id')) {
            $criteria[] = ['col' => 'user_id', 'op' => '=', 'value' => (int) $request->query('user_id')];
        }

        $items = empty($criteria)
            ? $this->subscriptions->all()
            : $this->subscriptions->search($criteria);

        return StockBackInSubscriptionResource::collection($items);
    }

    public function show(int $stock_back_in_subscription)
    {
        $entity = $this->subscriptions->find($stock_back_in_subscription);
        abort_if(! $entity, 404);

        return new StockBackInSubscriptionResource($entity);
    }

    public function store(StockBackInSubscriptionRequest $request)
    {
        $user = $request->user();

        $subscription = $this->notificationService->subscribe(
            variantId: $request->validated()['variant_id'],
            userId: $user?->getKey(),
            email: $request->validated()['email'] ?? $user?->email,
        );

        return (new StockBackInSubscriptionResource($subscription))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(int $stock_back_in_subscription): JsonResponse
    {
        $entity = $this->subscriptions->find($stock_back_in_subscription);
        abort_if(! $entity, 404);

        $this->subscriptions->disableIfNotDestroy($entity);

        return response()->json([], 204);
    }
}
