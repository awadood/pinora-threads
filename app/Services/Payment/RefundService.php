<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Repositories\Payment\Contracts\IRefundRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * RefundService
 *
 * Encapsulates creation and status management for refunds.
 * Gateway integration will be added later.
 *
 * @author Abdul Wadood
 */
class RefundService
{
    public function __construct(
        protected IRefundRepository $refunds
    ) {}

    /**
     * Request a refund against a successful payment.
     *
     * @param  array{
     *     amount:float|int|string,
     *     reason?:string|null,
     *     idempotency_key?:string|null,
     * }  $data
     */
    public function requestRefund(Order $order, Payment $payment, array $data): Refund
    {
        return DB::transaction(function () use ($order, $payment, $data) {
            if ($payment->currency_code !== $order->currency_code) {
                throw new InvalidArgumentException('Payment currency does not match order currency.');
            }

            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new InvalidArgumentException('Refund amount must be positive.');
            }

            if (! empty($data['idempotency_key'])) {
                $existing = $this->refunds->findByIdempotencyKey($data['idempotency_key']);
                if ($existing) {
                    return $existing;
                }
            }

            /** @var Refund $refund */
            $refund = $this->refunds->create([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'currency_code' => $order->currency_code,
                'amount' => $amount,
                'refund_status_code' => 'requested', // ensure this exists in refund_statuses
                'gateway_refund_id' => null,
                'reason' => $data['reason'] ?? null,
                'processed_at' => null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            // TODO: Call gateway to initiate real refund. On success, mark as succeeded.

            return $refund;
        });
    }

    public function markSucceeded(Refund $refund, string $gatewayRefundId): void
    {
        $refund->refund_status_code = 'succeeded';
        $refund->gateway_refund_id = $gatewayRefundId;
        $refund->processed_at = now();
        $refund->save();

        // TODO: update order status / refunded_at via Order domain service.
        $order = $refund->order; // relationship assumed
        if ($order && ! $order->refunded_at) {
            $order->refunded_at = now();
            $order->save();
        }
    }

    public function markFailed(Refund $refund, string $reason): void
    {
        $refund->refund_status_code = 'failed';
        $refund->reason = trim(($refund->reason ? $refund->reason.' ' : '').$reason);
        $refund->processed_at = now();
        $refund->save();
    }
}
