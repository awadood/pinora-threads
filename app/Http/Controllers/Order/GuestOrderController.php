<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use App\Services\Order\OrderClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class GuestOrderController extends Controller
{
    public function __construct(private readonly OrderClaimService $claimService) {}

    /**
     * GET /api/orders/track?token=...
     */
    public function track(Request $request): OrderResource
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $order = Order::query()
            ->where('guest_token', $data['token'])
            ->with('items')
            ->firstOrFail();

        return OrderResource::make($order);
    }

    /**
     * GET /api/orders/claim
     * Query: token, email, expires, signature
     */
    public function showClaim(Request $request)
    {
        $data = $this->validateClaimParams($request);
        $email = $this->claimService->normalizeEmail($data['email']);

        if (! $this->claimService->verifySignature($data['token'], $email, (int) $data['expires'], $data['signature'])) {
            return abort(403, 'Invalid or expired claim link.');
        }

        $order = Order::query()
            ->where('guest_token', $data['token'])
            ->whereRaw('lower(customer_email) = ?', [$email])
            ->firstOrFail();

        $userExists = User::where('email', $email)->exists();

        return response()->json([
            'email' => $email,
            'order_number' => $order->number,
            'claim_status' => $order->claim_status,
            'requires_password' => ! $userExists,
        ]);
    }

    /**
     * POST /api/orders/claim
     * Body: token, email, expires, signature, password?
     */
    public function claim(Request $request)
    {
        $data = $this->validateClaimParams($request);
        $email = $this->claimService->normalizeEmail($data['email']);

        if (! $this->claimService->verifySignature($data['token'], $email, (int) $data['expires'], $data['signature'])) {
            return abort(403, 'Invalid or expired claim link.');
        }

        $order = Order::query()
            ->where('guest_token', $data['token'])
            ->whereRaw('lower(customer_email) = ?', [$email])
            ->firstOrFail();

        $user = User::where('email', $email)->first();

        if (! $user) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'max:255'],
            ]);

            $user = User::create([
                'name' => $order->customer_name,
                'email' => $email,
                'phone' => $order->customer_phone,
                'password' => Hash::make($request->input('password')),
                'active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $this->claimService->claimOrdersForUser($user);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => UserResource::make($user),
            'order' => OrderResource::make($order->fresh(['items'])),
        ]);
    }

    private function validateClaimParams(Request $request): array
    {
        return $request->validate([
            'token' => ['required', 'uuid'],
            'email' => ['required', 'email'],
            'expires' => ['required', 'integer'],
            'signature' => ['required', 'string'],
        ]);
    }
}
