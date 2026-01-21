<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Storefront\StoreContext;

final class StoreContextController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var StoreContext $ctx */
        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);

        return response()->json([
            'country' => $ctx->country,
            'currency' => $ctx->currency,
            'source' => $ctx->source,
        ]);
    }
}
