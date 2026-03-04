<?php

return [
    'frontend_url' => env('STOREFRONT_FRONTEND_URL', 'http://localhost:3000'),
    'cookie_name' => env('STOREFRONT_COOKIE_NAME', 'store_ctx'),
    'ttl_days' => (int) env('STOREFRONT_TTL_DAYS', 30),
    'claim_link_ttl_minutes' => (int) env('STOREFRONT_CLAIM_LINK_TTL_MINUTES', 1440),
    'claim_link_secret' => env('STOREFRONT_CLAIM_LINK_SECRET', ''),

    // Allowed explicit override values (query param)
    'allowed_countries' => ['US', 'PK', 'GB', 'CA'],

    // Country -> Currency (v1: one currency per country)
    'country_currency' => [
        'US' => 'USD',
        'PK' => 'PKR',
        'GB' => 'GBP',
        'CA' => 'CAD',
    ],

    'default_country' => env('STOREFRONT_DEFAULT_COUNTRY', 'US'),
    'default_currency' => env('STOREFRONT_DEFAULT_CURRENCY', 'USD'),

    // IMPORTANT: since cookie is not encrypted, sign it with an HMAC secret.
    // Recommend a separate secret (not app.key). Keep it long/random.
    'cookie_signing_secret' => env('STOREFRONT_COOKIE_SIGNING_SECRET', '5RTxfWcu5+UKGx8BT+r/WbGnFCBCNgEnEmnjUfNJHN8AY30n4wcq/kFL0R4s6V8A'),

    // Cookie attributes
    'same_site' => env('STOREFRONT_COOKIE_SAMESITE', 'Lax'),
    'secure' => env('STOREFRONT_COOKIE_SECURE', null), // null => auto: $request->isSecure()
];
