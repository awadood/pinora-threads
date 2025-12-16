<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockBackInSubscriptionRequest;
use App\Http\Resources\Inventory\StockBackInSubscriptionResource;
use App\Models\StockBackInSubscription;
use App\Repositories\Inventory\Contracts\IStockBackInSubscriptionRepository;
use App\Services\Inventory\BackInStockNotificationService;
use App\Support\QueryFilterable;
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
    use QueryFilterable;

    public function __construct(
        private readonly IStockBackInSubscriptionRepository $subscriptions,
        private readonly BackInStockNotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->subscriptions->query(), $request),
            $request
        );

        $perPage = $request->integer('per_page', 25);

        return StockBackInSubscriptionResource::collection(
            $query->with([
                'stock',
                'variant.attributes.option.attribute',
            ])->paginate($perPage)
        );
    }

    public function show(StockBackInSubscription $stockBackInSubscription)
    {
        return StockBackInSubscriptionResource::make($stockBackInSubscription);
    }

    public function store(StockBackInSubscriptionRequest $request)
    {
        $user = $request->user();

        $subscription = $this->notificationService->subscribe(
            variantId: $request->validated()['variant_id'],
            userId: $user?->getKey(),
            email: $request->validated()['email'] ?? $user?->email,
        );

        return StockBackInSubscriptionResource::make($subscription)->response()->setStatusCode(201);
    }

    public function destroy(StockBackInSubscription $stockBackInSubscription): JsonResponse
    {
        $this->subscriptions->disableIfNotDestroy($stockBackInSubscription);

        return response()->json([], 204);
    }

    public function notify(StockBackInSubscription $stockBackInSubscription)
    {
        // If already notified, you may choose to return 200 or 4XX
        if ($stockBackInSubscription->notified_at) {
            return response()->json(['message' => 'Subscription already notified.']);
        }

        $this->notificationService->notify($stockBackInSubscription);

        return StockBackInSubscriptionResource::make($stockBackInSubscription);
    }

    public function notifyAll(Request $request)
    {
        $data = $request->validate(['variant_id' => ['required', 'integer', 'exists:product_variants,id']]);

        $count = $this->notificationService->notifyAll($data['variant_id']);

        return response()->json(['message' => 'Notifications sent.', 'count' => $count]);
    }
}
