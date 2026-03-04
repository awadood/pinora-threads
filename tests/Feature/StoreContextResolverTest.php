<?php

namespace Tests\Feature;

use App\Support\Storefront\StoreContext;
use App\Support\Storefront\StoreContextResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

class StoreContextResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('storefront.cookie_name', 'store_ctx');
        config()->set('storefront.cookie_signing_secret', 'test-secret');
        config()->set('storefront.allowed_countries', ['US', 'PK', 'GB', 'CA']);
        config()->set('storefront.country_currency', [
            'US' => 'USD',
            'PK' => 'PKR',
            'GB' => 'GBP',
            'CA' => 'CAD',
        ]);
        config()->set('storefront.default_country', 'US');
        config()->set('storefront.default_currency', 'USD');
    }

    public function test_query_country_overrides_cloudfront_header(): void
    {
        $resolver = app(StoreContextResolver::class);
        $request = Request::create('/api/store-context?country=PK', 'GET');
        $request->headers->set('CloudFront-Viewer-Country', 'US');

        $ctx = $resolver->resolve($request);

        $this->assertSame('PK', $ctx->country);
        $this->assertSame('PKR', $ctx->currency);
        $this->assertSame('query', $ctx->source);
    }

    public function test_signed_cookie_overrides_cloudfront_header(): void
    {
        $resolver = app(StoreContextResolver::class);

        $cookieValue = $resolver->buildSignedCookieValue(new StoreContext('PK', 'PKR', 'query'));

        $request = Request::create('/api/store-context', 'GET');
        $request->cookies->set('store_ctx', $cookieValue);
        $request->headers->set('CloudFront-Viewer-Country', 'US');

        $ctx = $resolver->resolve($request);

        $this->assertSame('PK', $ctx->country);
        $this->assertSame('PKR', $ctx->currency);
        $this->assertSame('cookie', $ctx->source);
    }

    public function test_cloudfront_header_is_used_when_query_and_cookie_are_missing(): void
    {
        $resolver = app(StoreContextResolver::class);
        $request = Request::create('/api/store-context', 'GET');
        $request->headers->set('CloudFront-Viewer-Country', 'gb');

        $ctx = $resolver->resolve($request);

        $this->assertSame('GB', $ctx->country);
        $this->assertSame('GBP', $ctx->currency);
        $this->assertSame('cloudfront', $ctx->source);
    }

    public function test_invalid_cloudfront_header_falls_back_to_default(): void
    {
        $resolver = app(StoreContextResolver::class);
        $request = Request::create('/api/store-context', 'GET');
        $request->headers->set('CloudFront-Viewer-Country', 'ZZ');

        $ctx = $resolver->resolve($request);

        $this->assertSame('US', $ctx->country);
        $this->assertSame('USD', $ctx->currency);
        $this->assertSame('default', $ctx->source);
    }
}
