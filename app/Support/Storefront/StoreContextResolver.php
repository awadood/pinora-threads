<?php

namespace App\Support\Storefront;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MaxMind\Db\Reader;

final class StoreContextResolver
{
    public function resolve(Request $request): StoreContext
    {
        // 1) Query param override wins (if valid)
        $qCountry = strtoupper((string) $request->query('country', ''));
        if ($qCountry !== '' && $this->isAllowedCountry($qCountry)) {
            return new StoreContext($qCountry, $this->countryToCurrency($qCountry), 'query');
        }

        // 2) Signed cookie (trust only if signature OK)
        $cookieName = (string) config('storefront.cookie_name');
        $raw = $request->cookie($cookieName);

        if (is_string($raw) && $raw !== '') {
            $decoded = $this->decodeAndVerifyCookie($raw);
            if ($decoded !== null) {
                $country = $decoded['country'];
                $currency = $decoded['currency'];

                if ($this->isAllowedCountry($country) && $currency === $this->countryToCurrency($country)) {
                    return new StoreContext($country, $currency, 'cookie');
                }
            }
        }

        // 3) GeoIP best-effort
        $geoCountry = $this->resolveCountryByGeoIp($request);
        if ($geoCountry !== null && $this->isAllowedCountry($geoCountry)) {
            return new StoreContext($geoCountry, $this->countryToCurrency($geoCountry), 'geoip');
        }

        // 4) Defaults
        $defCountry = (string) config('storefront.default_country', 'PK');
        $defCountry = $this->isAllowedCountry($defCountry) ? $defCountry : 'PK';

        return new StoreContext($defCountry, $this->countryToCurrency($defCountry), 'default');
    }

    public function shouldWriteCookie(?StoreContext $current, StoreContext $resolved): bool
    {
        // If no current cookie ctx, or if changed country/currency, write it.
        if ($current === null) return true;
        return $current->country !== $resolved->country || $current->currency !== $resolved->currency;
    }

    public function buildSignedCookieValue(StoreContext $ctx): string
    {
        $payload = [
            'v' => 1,
            'country' => $ctx->country,
            'currency' => $ctx->currency,
            'ts' => time(),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $b64 = $this->b64urlEncode($json);

        $sig = hash_hmac('sha256', $b64, $this->signingSecret());
        return $b64 . '.' . $sig;
    }

    private function decodeAndVerifyCookie(string $raw): ?array
    {
        $parts = explode('.', $raw);
        if (count($parts) !== 2) return null;

        [$b64, $sig] = $parts;

        $expected = hash_hmac('sha256', $b64, $this->signingSecret());
        if (!hash_equals($expected, $sig)) return null;

        $json = $this->b64urlDecode($b64);
        if ($json === null) return null;

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) return null;

        if (!isset($decoded['country'], $decoded['currency'])) return null;

        $country = strtoupper((string) $decoded['country']);
        $currency = strtoupper((string) $decoded['currency']);

        return [
            'country' => $country,
            'currency' => $currency,
        ];
    }

    private function resolveCountryByGeoIp(Request $request): ?string
    {
        $ip = $request->ip() ?? '0.0.0.0';

        // Skip private/local IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $cacheTtl = (int) config('storefront.geoip_cache_ttl_minutes', 60);
        $cacheKey = 'geoip_country:' . $ip;

        return Cache::remember($cacheKey, now()->addMinutes($cacheTtl), function () use ($ip) {
            $dbPath = (string) config('storefront.geoip_db_path');
            if (!is_file($dbPath)) return null;

            try {
                $reader = new Reader($dbPath);
                $record = $reader->get($ip);

                $iso = $record['country']['iso_code'] ?? null;
                if (!is_string($iso) || strlen($iso) !== 2) return null;

                return strtoupper($iso);
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    private function isAllowedCountry(string $country): bool
    {
        $allowed = (array) config('storefront.allowed_countries', []);
        return in_array(strtoupper($country), $allowed, true);
    }

    private function countryToCurrency(string $country): string
    {
        $map = (array) config('storefront.country_currency', []);
        return strtoupper($map[strtoupper($country)] ?? (string) config('storefront.default_currency', 'PKR'));
    }

    private function signingSecret(): string
    {
        $secret = (string) config('storefront.cookie_signing_secret');
        if ($secret === '') {
            // Fail closed: without a signing secret, cookie cannot be trusted.
            // You can throw here if you'd rather hard-fail.
            return 'invalid-secret';
        }
        return $secret;
    }

    private function b64urlEncode(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }

    private function b64urlDecode(string $s): ?string
    {
        $pad = strlen($s) % 4;
        if ($pad > 0) $s .= str_repeat('=', 4 - $pad);

        $decoded = base64_decode(strtr($s, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }
}
