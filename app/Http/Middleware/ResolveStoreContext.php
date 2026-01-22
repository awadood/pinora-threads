<?php

namespace App\Http\Middleware;

use App\Support\Storefront\StoreContext;
use App\Support\Storefront\StoreContextResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

final class ResolveStoreContext
{
    public function __construct(private readonly StoreContextResolver $resolver) {}

    public function handle(Request $request, Closure $next)
    {
        // Attempt to read current cookie ctx (only for "shouldWriteCookie" comparison)
        $current = $this->tryReadCurrent($request);

        $resolved = $this->resolver->resolve($request);

        // Make available to controllers/services
        $request->attributes->set('store_ctx', $resolved);
        app()->instance(StoreContext::class, $resolved);

        $response = $next($request);

        // Decide if we should set/refresh cookie
        if ($this->resolver->shouldWriteCookie($current, $resolved)) {
            $cookieName = (string) config('storefront.cookie_name');
            $ttlDays = (int) config('storefront.ttl_days', 30);

            $cookieValue = $this->resolver->buildSignedCookieValue($resolved);

            $secureConfig = config('storefront.secure');
            $secure = is_bool($secureConfig) ? $secureConfig : $request->isSecure();

            $cookie = Cookie::create($cookieName)
                ->withValue($cookieValue)
                ->withExpires(time() + ($ttlDays * 86400))
                ->withPath('/')
                ->withHttpOnly(true)
                ->withSecure($secure)
                ->withSameSite((string) config('storefront.same_site', 'Lax'));

            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    private function tryReadCurrent(Request $request): ?StoreContext
    {
        // We do not need full verification here; it only drives "shouldWriteCookie".
        // If it’s invalid/unreadable, treat as null.
        $cookieName = (string) config('storefront.cookie_name');
        $raw = $request->cookie($cookieName);

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        // Let resolver verify fully by doing a resolve() with no query param:
        // But resolve() would possibly GeoIP which we don’t want just to compare.
        // So we’ll keep it simple: return null and let resolver decide to write cookie when needed.
        return null;
    }
}
