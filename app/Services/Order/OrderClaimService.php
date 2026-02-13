<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

final class OrderClaimService
{
    public function buildTrackingUrl(Order $order): ?string
    {
        if (! $order->guest_token) {
            return null;
        }

        $base = $this->frontendBase();
        if ($base === null) {
            return null;
        }

        return $base.'/orders/track?token='.urlencode($order->guest_token);
    }

    public function buildClaimUrl(Order $order): ?string
    {
        if (! $order->guest_token || $order->claim_status === Order::CLAIM_STATUS_CLAIMED) {
            return null;
        }

        $base = $this->frontendBase();
        if ($base === null) {
            return null;
        }

        $email = $this->normalizeEmail($order->customer_email);
        $expires = now()->addMinutes($this->claimLinkTtlMinutes())->timestamp;
        $signature = $this->signature($order->guest_token, $email, $expires);

        $query = http_build_query([
            'token' => $order->guest_token,
            'email' => $email,
            'expires' => $expires,
            'signature' => $signature,
        ]);

        return $base.'/claim?'.$query;
    }

    public function verifySignature(string $token, string $email, int $expires, string $signature): bool
    {
        if ($expires < time()) {
            return false;
        }

        $expected = $this->signature($token, $email, $expires);

        return hash_equals($expected, $signature);
    }

    public function claimOrdersForUser(User $user): int
    {
        $email = $this->normalizeEmail($user->email);

        $orders = Order::query()
            ->whereNull('user_id')
            ->whereRaw('lower(customer_email) = ?', [$email])
            ->whereIn('claim_status', [Order::CLAIM_STATUS_NEW, Order::CLAIM_STATUS_PENDING])
            ->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        foreach ($orders as $order) {
            $order->user_id = $user->id;
            $order->claim_status = Order::CLAIM_STATUS_CLAIMED;
            $order->save();
        }

        return $orders->count();
    }

    public function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function frontendBase(): ?string
    {
        $base = rtrim((string) config('storefront.frontend_url', ''), '/');

        return $base !== '' ? $base : null;
    }

    private function signature(string $token, string $email, int $expires): string
    {
        $payload = implode('|', [$token, $this->normalizeEmail($email), $expires]);

        return hash_hmac('sha256', $payload, $this->secret());
    }

    private function claimLinkTtlMinutes(): int
    {
        return (int) config('storefront.claim_link_ttl_minutes', 1440);
    }

    private function secret(): string
    {
        $secret = (string) config('storefront.claim_link_secret', '');

        if ($secret === '') {
            $secret = (string) config('app.key', 'fallback-secret');
        }

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            if ($decoded !== false) {
                $secret = $decoded;
            }
        }

        return $secret;
    }
}
