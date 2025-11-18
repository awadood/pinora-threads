<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Repositories\Payment\Contracts\IPaymentAttemptRepository;
use Illuminate\Support\Facades\DB;

/**
 * PaymentAttemptService
 *
 * Manages lifecycle of attempts: start, succeed, fail.
 * Gateway-specific logic will be plugged in later.
 *
 * @author Abdul Wadood
 */
class PaymentAttemptService
{
    public function __construct(
        protected IPaymentAttemptRepository $attempts
    ) {}

    /**
     * @param  array{
     *     method:string,
     *     action:string,
     *     amount:float|int|string,
     *     idempotency_key?:string|null,
     *     request_payload?:mixed,
     * }  $data
     */
    public function startAttempt(Order $order, array $data): PaymentAttempt
    {
        return DB::transaction(function () use ($order, $data) {
            if (! empty($data['idempotency_key'])) {
                $existing = $this->attempts->findByIdempotencyKey($data['idempotency_key']);
                if ($existing) {
                    return $existing;
                }
            }

            /** @var PaymentAttempt $attempt */
            $attempt = $this->attempts->create([
                'order_id' => $order->id,
                'payment_id' => null,
                'currency_code' => $order->currency_code,
                'method' => $data['method'],
                'action' => $data['action'],
                'status' => 'pending',
                'amount' => (float) $data['amount'],
                'error_code' => null,
                'error_message' => null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'remote_ip' => request()->ip(),
                'request_payload' => $data['request_payload'] ?? null,
                'response_payload' => null,
                'attempted_at' => now(),
            ]);

            return $attempt;
        });
    }

    public function markSucceeded(PaymentAttempt $attempt, Payment $payment, mixed $responsePayload = null): void
    {
        $attempt->status = 'succeeded';
        $attempt->payment_id = $payment->id;
        $attempt->response_payload = $responsePayload;
        $attempt->save();
    }

    public function markFailed(PaymentAttempt $attempt, string $errorCode, string $message, mixed $responsePayload = null): void
    {
        $attempt->status = 'failed';
        $attempt->error_code = $errorCode;
        $attempt->error_message = $message;
        $attempt->response_payload = $responsePayload;
        $attempt->save();
    }
}
