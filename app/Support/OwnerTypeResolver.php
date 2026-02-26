<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OwnerTypeResolver
{
    /**
     * Client short keys -> class-string<Model>
     *
     * @return array<string, class-string<Model>>
     */
    public static function allowed(): array
    {
        return [
            'product' => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
            'collection' => \App\Models\Collection::class,
            'attribute_option' => \App\Models\AttributeOption::class,
        ];
    }

    /**
     * Normalize the client key (product/category/collection/attribute_option)
     */
    public static function normalizeKey(string $ownerTypeKey): string
    {
        $key = (string) Str::of($ownerTypeKey)->trim()->lower();

        if ($key === '' || ! Arr::exists(self::allowed(), $key)) {
            throw ValidationException::withMessages([
                'owner_type' => 'Invalid owner_type. Allowed: product, category, collection, attribute_option.',
            ]);
        }

        return $key;
    }

    /**
     * Resolve client key to model class.
     *
     * @return class-string<Model>
     */
    public static function resolveClass(string $ownerTypeKey): string
    {
        $key = self::normalizeKey($ownerTypeKey);

        return self::allowed()[$key];
    }

    /**
     * Convert class string back to key (for API responses).
     */
    public static function keyFromClass(string $ownerClass): ?string
    {
        $k = array_search($ownerClass, self::allowed(), true);

        return $k === false ? null : (string) $k;
    }

    public static function allowedRoles(): array
    {
        return [
            'product' => ['thumbnail', 'gallery', 'hero', 'og_image'],
            'category' => ['thumbnail', 'hero', 'og_image'],
            'collection' => ['hero', 'og_image'],
            'attribute_option' => ['thumbnail'],
        ];
    }

    public static function assertRoleAllowed(string $ownerTypeKey, string $role): void
    {
        $key = self::normalizeKey($ownerTypeKey);

        $roles = self::allowedRoles()[$key] ?? [];
        if (! in_array($role, $roles, true)) {
            throw ValidationException::withMessages([
                'role' => "Invalid role for owner_type={$key}.",
            ]);
        }
    }

    public static function assertOwnerExists(string $ownerTypeKey, int $ownerId): void
    {
        if ($ownerId <= 0) {
            throw ValidationException::withMessages([
                'owner_id' => 'owner_id must be a positive integer.',
            ]);
        }

        $class = self::resolveClass($ownerTypeKey);

        if (! $class::query()->whereKey($ownerId)->exists()) {
            throw ValidationException::withMessages([
                'owner_id' => 'Owner record not found for the given owner_type and owner_id.',
            ]);
        }
    }

    /**
     * Validate owner and return BOTH:
     * - owner_type_key (client key)
     * - owner_type (resolved class string to store in DB)
     *
     * @return array{owner_type_key:string, owner_type: class-string<Model>, owner_id:int}
     */
    public static function validateOwner(string $ownerTypeKey, int $ownerId): array
    {
        $key = self::normalizeKey($ownerTypeKey);
        self::assertOwnerExists($key, $ownerId);

        return [
            'owner_type_key' => $key,
            'owner_type' => self::resolveClass($key),
            'owner_id' => $ownerId,
        ];
    }
}
