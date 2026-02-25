<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Adjust if your tax_classes seed uses a different ID.
     */
    private const TAX_CLASS_ID = 1;

    /**
     * Safety: keep the seed volume reasonable for local/dev.
     */
    private const PRODUCT_COUNT = 100;

    public function run(): void
    {
        DB::transaction(function () {
            $this->guardPrereqs();

            // 1) Attributes + options (seeded by CoreTablesSeeder)
            $attrs = $this->loadAttributes();

            // 2) Categories (seeded by CoreTablesSeeder)
            $cat = $this->loadCategories();

            // 3) Collections + collection_country
            $collections = $this->seedCollections();

            // 4) Products + prices + attributes + category assignment + relationship-only variants
            $products = $this->seedProductsAndVariants($attrs, $cat);

            // 5) Attach products to collections (manual curation)
            $this->attachCollections($collections, $products);

            // 6) Related products + some bundles (optional but matches schema)
            $this->seedRelatedProducts($products);
            $this->seedBundleProducts($products);

            // 7) Media assets + attachments + renditions (deterministic)
            $this->seedMediaSystem($collections, $products);

            // 8) Merchandising sections (Featured, New Arrivals, etc.)
            $this->seedMerchSections($products, $collections, $cat);
        });
    }

    private function guardPrereqs(): void
    {
        $hasCountryUS = DB::table('countries')->where('code', 'US')->exists();
        $hasCountryPK = DB::table('countries')->where('code', 'PK')->exists();

        if (! $hasCountryUS || ! $hasCountryPK) {
            throw new \RuntimeException('Missing countries. Ensure countries table includes US and PK before seeding catalog.');
        }

        $hasCurrencyUSD = DB::table('currencies')->where('code', 'USD')->exists();
        $hasCurrencyPKR = DB::table('currencies')->where('code', 'PKR')->exists();

        if (! $hasCurrencyUSD || ! $hasCurrencyPKR) {
            throw new \RuntimeException('Missing currencies. Ensure currencies table includes USD and PKR before seeding catalog.');
        }

        $hasTax = DB::table('tax_classes')->where('id', self::TAX_CLASS_ID)->exists();
        if (! $hasTax) {
            throw new \RuntimeException('Missing tax_classes row with id='.self::TAX_CLASS_ID.'. Adjust TAX_CLASS_ID in CatalogSeeder.');
        }
    }

    private function loadAttributes(): array
    {
        $requiredAttributes = [
            'piece_type',
            'fabric',
            'season',
            'color_family',
            'stock_status',
            'occasion',
            'embellishment',
            'material_notes',
            'what_included',
        ];

        $attributes = DB::table('attributes')
            ->whereIn('code', $requiredAttributes)
            ->pluck('id', 'code')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($requiredAttributes as $code) {
            if (! isset($attributes[$code])) {
                throw new \RuntimeException("Missing attribute [$code]. Run CoreTablesSeeder before CatalogSeeder.");
            }
        }

        $optionAttributes = [
            'piece_type',
            'fabric',
            'occasion',
            'season',
            'embellishment',
            'color_family',
            'stock_status',
        ];

        $optionRows = DB::table('attribute_options')
            ->join('attributes', 'attributes.id', '=', 'attribute_options.attribute_id')
            ->whereIn('attributes.code', $optionAttributes)
            ->orderBy('attribute_options.sort')
            ->select([
                'attributes.code as attribute_code',
                'attribute_options.id as option_id',
                'attribute_options.value as option_value',
            ])
            ->get();

        $options = [];
        foreach ($optionRows as $row) {
            if (! isset($options[$row->attribute_code])) {
                $options[$row->attribute_code] = [];
            }
            $options[$row->attribute_code][$row->option_value] = (int) $row->option_id;
        }

        $requiredOptions = [
            'piece_type' => ['2 Piece', '3 Piece'],
            'fabric' => ['Lawn', 'Khaddar', 'Cotton', 'Silk', 'Chiffon', 'Linen', 'Georgette'],
            'occasion' => ['Everyday', 'Festive', 'Wedding', 'Party'],
            'season' => ['Summer', 'Winter', 'Mid-Season'],
            'embellishment' => ['Printed', 'Digital Print', 'Embroidered', 'Handwork', 'Plain', 'Sequins', 'Mirror Work'],
            'color_family' => ['Black', 'White', 'Blue', 'Green', 'Red', 'Maroon', 'Pink', 'Purple', 'Yellow', 'Beige', 'Brown', 'Grey', 'Multi'],
            'stock_status' => ['In Stock', 'Out of Stock', 'Coming Soon'],
        ];

        foreach ($requiredOptions as $attributeCode => $values) {
            foreach ($values as $value) {
                if (! isset($options[$attributeCode][$value])) {
                    throw new \RuntimeException("Missing option [$value] for attribute [$attributeCode]. Run CoreTablesSeeder.");
                }
            }
        }

        return [
            'attr' => $attributes,
            'opt' => $options,
        ];
    }

    private function loadCategories(): array
    {
        $typeRoot = DB::table('categories')
            ->where('slug', 'type')
            ->whereNull('parent_id')
            ->first();

        if (! $typeRoot) {
            throw new \RuntimeException('Missing Type root category. Run CoreTablesSeeder before CatalogSeeder.');
        }

        $typeLeaves = DB::table('categories')
            ->where('parent_id', $typeRoot->id)
            ->orderBy('sort')
            ->get(['id', 'name']);

        $requiredLeafNames = ['Suit', 'Saree', 'Dupatta', 'Shawl'];
        $leafMap = [];

        foreach ($requiredLeafNames as $name) {
            $leaf = $typeLeaves->first(fn ($row) => Str::lower($row->name) === Str::lower($name));
            if (! $leaf) {
                throw new \RuntimeException("Missing Type child category [$name]. Run CoreTablesSeeder.");
            }
            $leafMap[$name] = (int) $leaf->id;
        }

        return [
            'roots' => [
                'type' => (int) $typeRoot->id,
            ],
            'leaf' => array_values($leafMap),
            'leaf_map' => $leafMap,
            'leaf_by_root' => [
                'type' => array_values($leafMap),
            ],
        ];
    }

    /**
     * Creates collections and sets collection_country.
     * Returns map: [name => collection_id]
     */
    private function seedCollections(): array
    {
        $names = [
            'New Arrivals',
            'Best Sellers',
            'Festive Edit',
            'Wedding Edit',
            'Everyday Essentials',
            'Limited Stock',
        ];

        $ids = [];
        foreach ($names as $index => $name) {
            $slug = Str::slug($name);

            $existing = DB::table('collections')->where('slug', $slug)->first();
            if ($existing) {
                DB::table('collections')->where('id', $existing->id)->update([
                    'name' => $name,
                    'sort' => $index + 1,
                    'description' => $this->collectionDescription($name),
                    'active' => true,
                    'updated_at' => now(),
                ]);

                $collectionId = (int) $existing->id;
            } else {
                $collectionId = (int) DB::table('collections')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'sort' => $index + 1,
                    'description' => $this->collectionDescription($name),
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $ids[$name] = $collectionId;

            // Sync collection_country (no timestamps in schema)
            DB::table('collection_country')->where('collection_id', $collectionId)->delete();

            // PK for all collections
            DB::table('collection_country')->insertOrIgnore([
                'collection_id' => $collectionId,
                'country_code' => 'PK',
            ]);

            // US for the first 4 collections
            if ($index < 4) {
                DB::table('collection_country')->insertOrIgnore([
                    'collection_id' => $collectionId,
                    'country_code' => 'US',
                ]);
            }
        }

        return $ids;
    }

    /**
     * Returns:
     * [
     *   'products' => [product_id, ...],
     *   'variants' => [variant_product_id, ...],
     *   'by_product' => [product_id => [variant_product_id, ...]],
     * ]
     */
    private function seedProductsAndVariants(array $attrs, array $cat): array
    {
        $leafCategoryIds = array_values($cat['leaf']);
        $categoryMap = $cat['leaf_map'] ?? [];

        $products = [];
        $variants = [];
        $byProduct = [];
        $simpleProductIds = [];
        $variableProductIds = [];

        $seq = 1;

        // A controlled mix of product types supported by schema
        $targets = [
            'variable' => (int) floor(self::PRODUCT_COUNT * 0.60),
            'simple' => (int) floor(self::PRODUCT_COUNT * 0.30),
        ];
        $targets['bundle'] = self::PRODUCT_COUNT - $targets['variable'] - $targets['simple'];

        $typePlan = array_merge(
            array_fill(0, $targets['variable'], 'variable'),
            array_fill(0, $targets['simple'], 'simple'),
            array_fill(0, $targets['bundle'], 'bundle'),
        );

        foreach ($typePlan as $type) {
            $categoryName = ! empty($categoryMap)
                ? (string) $this->pick(array_keys($categoryMap))
                : null;
            $categoryId = $categoryName !== null
                ? (int) $categoryMap[$categoryName]
                : (int) $this->pick($leafCategoryIds);

            $pieceType = $categoryName === 'Suit'
                ? $this->pick(array_keys($attrs['opt']['piece_type']))
                : null;
            $fabric = $this->pick(array_keys($attrs['opt']['fabric']));
            $season = $this->pick(array_keys($attrs['opt']['season']));
            $colorFamily = $this->pick(array_keys($attrs['opt']['color_family']));
            $stockStatus = $type === 'bundle'
                ? 'Coming Soon'
                : $this->pick(array_keys($attrs['opt']['stock_status']));
            $occasions = $this->pickMany(array_keys($attrs['opt']['occasion']), random_int(1, 2));
            $embellishments = $this->pickMany(array_keys($attrs['opt']['embellishment']), random_int(1, 3));

            $productName = $this->buildProductName($categoryName ?? 'Product', $pieceType, $fabric, $colorFamily);
            $productSku = $this->sku('PRD', $seq);
            $slug = $this->uniqueSlug('products', Str::slug($productName), $productSku);

            $productId = $this->upsertProduct(
                sku: $productSku,
                name: $productName,
                slug: $slug,
                type: $type,
                description: $this->buildProductDescription($categoryName ?? 'Product', $pieceType, $fabric, $colorFamily),
            );

            $products[] = $productId;
            $byProduct[$productId] = [];

            if ($type === 'simple') {
                $simpleProductIds[] = $productId;
            }
            if ($type === 'variable') {
                $variableProductIds[] = $productId;
            }

            // Assign to one leaf category
            DB::table('category_product')->where('product_id', $productId)->delete();
            DB::table('category_product')->insertOrIgnore([
                'category_id' => $categoryId,
                'product_id' => $productId,
            ]);

            // Product attributes (single-select + multiselect + text)
            DB::table('product_attributes')->where('product_id', $productId)->delete();

            if ($pieceType !== null) {
                $this->attachProductSelectAttr($productId, $attrs['attr']['piece_type'], $attrs['opt']['piece_type'][$pieceType]);
            }
            $this->attachProductSelectAttr($productId, $attrs['attr']['fabric'], $attrs['opt']['fabric'][$fabric]);
            $this->attachProductSelectAttr($productId, $attrs['attr']['season'], $attrs['opt']['season'][$season]);
            $this->attachProductSelectAttr($productId, $attrs['attr']['color_family'], $attrs['opt']['color_family'][$colorFamily]);
            $this->attachProductSelectAttr($productId, $attrs['attr']['stock_status'], $attrs['opt']['stock_status'][$stockStatus]);
            $this->attachProductMultiSelectAttr($productId, $attrs['attr']['occasion'], $attrs['opt']['occasion'], $occasions);
            $this->attachProductMultiSelectAttr($productId, $attrs['attr']['embellishment'], $attrs['opt']['embellishment'], $embellishments);
            $this->attachProductTextAttr($productId, $attrs['attr']['material_notes'], $this->materialNotes($fabric));
            $this->attachProductTextAttr($productId, $attrs['attr']['what_included'], $this->includedText($categoryName ?? 'Product', $pieceType));

            // Product prices per currency
            $usd = $this->randMoney(35, 250);
            $pkr = $this->randMoney(2500, 45000);
            $this->upsertProductPrice($productId, 'USD', $usd, null);
            $this->upsertProductPrice($productId, 'PKR', $pkr, null);

            // Publish product (optional but useful for dev)
            DB::table('products')->where('id', $productId)->update([
                'active' => true,
                'published_at' => now(),
                'first_published_at' => DB::raw('COALESCE(first_published_at, published_at)'),
                'updated_at' => now(),
            ]);

            // product_variants is relationship-only; clear stale rows on rerun.
            DB::table('product_variants')->where('product_id', $productId)->delete();

            $seq++;
        }

        // Variant relations: link variable products to sellable simple products.
        foreach ($variableProductIds as $productId) {
            $candidates = array_values(array_filter($simpleProductIds, fn ($id) => (int) $id !== (int) $productId));
            if (empty($candidates)) {
                continue;
            }

            shuffle($candidates);
            $max = min(4, count($candidates));
            $pickCount = $max <= 1 ? 1 : random_int(2, $max);
            $picked = array_slice($candidates, 0, $pickCount);

            foreach ($picked as $variantId) {
                DB::table('product_variants')->insertOrIgnore([
                    'product_id' => (int) $productId,
                    'variant_id' => (int) $variantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $variants[] = (int) $variantId;
                $byProduct[$productId][] = (int) $variantId;
            }
        }

        return [
            'products' => $products,
            'variants' => array_values(array_unique($variants)),
            'by_product' => $byProduct,
        ];
    }

    private function upsertProduct(string $sku, string $name, string $slug, string $type, ?string $description): int
    {
        $existing = DB::table('products')->where('sku', $sku)->first();
        if ($existing) {
            DB::table('products')->where('id', $existing->id)->update([
                'name' => $name,
                'slug' => $slug,
                'type' => $type,
                'description' => $description,
                'tax_class_id' => self::TAX_CLASS_ID,
                'active' => true,
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('products')->insertGetId([
            'sku' => $sku,
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'description' => $description,
            'tax_class_id' => self::TAX_CLASS_ID,
            'active' => true,
            'published_at' => null,
            'first_published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertProductPrice(int $productId, string $currencyCode, string $amount, ?string $compareAt): void
    {
        $existing = DB::table('product_prices')
            ->where('product_id', $productId)
            ->where('currency_code', $currencyCode)
            ->first();

        if ($existing) {
            DB::table('product_prices')->where('id', $existing->id)->update([
                'amount' => $amount,
                'compare_at' => $compareAt,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('product_prices')->insert([
            'product_id' => $productId,
            'currency_code' => $currencyCode,
            'amount' => $amount,
            'compare_at' => $compareAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachProductSelectAttr(int $productId, int $attributeId, int $optionId): void
    {
        // Enforce single-select behavior at seed-time.
        DB::table('product_attributes')
            ->where('product_id', $productId)
            ->where('attribute_id', $attributeId)
            ->delete();

        DB::table('product_attributes')->insertOrIgnore([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
            'option_id' => $optionId,
            'value' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachProductMultiSelectAttr(int $productId, int $attributeId, array $optionMap, array $selectedValues): void
    {
        DB::table('product_attributes')
            ->where('product_id', $productId)
            ->where('attribute_id', $attributeId)
            ->delete();

        foreach (array_values(array_unique($selectedValues)) as $value) {
            if (! isset($optionMap[$value])) {
                continue;
            }

            DB::table('product_attributes')->insertOrIgnore([
                'product_id' => $productId,
                'attribute_id' => $attributeId,
                'option_id' => (int) $optionMap[$value],
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function attachProductTextAttr(int $productId, int $attributeId, string $value): void
    {
        // With unique(product_id, attribute_id, option_id), NULL option_id is not unique-safe in PostgreSQL.
        // Clear old text rows first to keep reruns idempotent.
        DB::table('product_attributes')
            ->where('product_id', $productId)
            ->where('attribute_id', $attributeId)
            ->whereNull('option_id')
            ->delete();

        DB::table('product_attributes')->insertOrIgnore([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
            'option_id' => null,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachCollections(array $collections, array $products): void
    {
        // Curate with simple rules; ensure pivot uniqueness.
        $productIds = $products['products'];

        $map = [
            'New Arrivals' => array_slice($productIds, 0, 12),
            'Best Sellers' => array_slice($productIds, 10, 12),
            'Festive Edit' => array_slice($productIds, 25, 12),
            'Wedding Edit' => array_slice($productIds, 40, 12),
            'Everyday Essentials' => array_slice($productIds, 55, 12),
            'Limited Stock' => array_slice($productIds, 70, 12),
        ];

        foreach ($map as $collectionName => $ids) {
            if (! isset($collections[$collectionName])) {
                continue;
            }

            $collectionId = (int) $collections[$collectionName];
            $pos = 1;

            foreach ($ids as $pid) {
                DB::table('collection_product')->insertOrIgnore([
                    'collection_id' => $collectionId,
                    'product_id' => (int) $pid,
                    'sort' => $pos,
                ]);
                $pos++;
            }
        }
    }

    private function seedRelatedProducts(array $products): void
    {
        $ids = array_values(array_unique($products['products']));
        if (count($ids) < 8) {
            return;
        }

        // Create 3 related links per product (small, safe volume)
        foreach ($ids as $pid) {
            $pid = (int) $pid;

            $candidates = array_values(array_filter($ids, fn ($x) => (int) $x !== $pid));
            shuffle($candidates);

            $picked = array_slice($candidates, 0, 3);
            foreach ($picked as $rid) {
                DB::table('related_products')->insertOrIgnore([
                    'product_id' => $pid,
                    'related_product_id' => (int) $rid,
                ]);
            }
        }
    }

    private function seedBundleProducts(array $products): void
    {
        // product_bundles is relationship-only; bundle items are existing sellable products.
        $bundleProductIds = DB::table('products')->where('type', 'bundle')->pluck('id')->all();
        if (empty($bundleProductIds)) {
            return;
        }

        $allProductIds = $products['products'] ?? [];
        if (count($allProductIds) < 5) {
            return;
        }

        foreach ($bundleProductIds as $bundlePid) {
            $bundlePid = (int) $bundlePid;

            // Avoid duplicates across reruns
            DB::table('product_bundles')->where('product_id', $bundlePid)->delete();

            $candidates = array_values(array_filter($allProductIds, fn ($id) => (int) $id !== $bundlePid));
            if (empty($candidates)) {
                continue;
            }

            $max = min(4, count($candidates));
            $pickCount = $max <= 1 ? 1 : random_int(2, $max);
            $pick = $this->pickMany($candidates, $pickCount);

            foreach ($pick as $productId) {
                DB::table('product_bundles')->insertOrIgnore([
                    'product_id' => $bundlePid,
                    'bundle_item_id' => (int) $productId,
                    'quantity' => random_int(1, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedMediaSystem(array $collections, array $products): void
    {
        $ownerProduct = 'App\\Models\\Product';
        $ownerCategory = 'App\\Models\\Category';
        $ownerCollection = 'App\\Models\\Collection';

        // Category media: thumbnail + hero + og_image (single-slot roles)
        $categoryIds = DB::table('categories')->pluck('id')->all();
        foreach ($categoryIds as $categoryId) {
            $categoryId = (int) $categoryId;

            $thumbAssetId = $this->getOrCreateImageAsset("categories/{$categoryId}/thumbnail");
            $heroAssetId = $this->getOrCreateImageAsset("categories/{$categoryId}/hero");
            $ogAssetId = $this->getOrCreateImageAsset("categories/{$categoryId}/og_image");

            $this->attachMedia($thumbAssetId, $ownerCategory, $categoryId, 'thumbnail', 1, true);
            $this->attachMedia($heroAssetId, $ownerCategory, $categoryId, 'hero', 1, true);
            $this->attachMedia($ogAssetId, $ownerCategory, $categoryId, 'og_image', 1, true);

            $this->ensureRenditionsForRole($thumbAssetId, $ownerCategory, 'thumbnail');
            $this->ensureRenditionsForRole($heroAssetId, $ownerCategory, 'hero');
            $this->ensureRenditionsForRole($ogAssetId, $ownerCategory, 'og_image');
        }

        // Collection media: thumbnail + hero + og_image
        foreach ($collections as $name => $collectionId) {
            $collectionId = (int) $collectionId;
            $slug = Str::slug($name);

            $thumbAssetId = $this->getOrCreateImageAsset("collections/{$collectionId}-{$slug}/thumbnail");
            $heroAssetId = $this->getOrCreateImageAsset("collections/{$collectionId}-{$slug}/hero");
            $ogAssetId = $this->getOrCreateImageAsset("collections/{$collectionId}-{$slug}/og_image");

            $this->attachMedia($thumbAssetId, $ownerCollection, $collectionId, 'thumbnail', 1, true);
            $this->attachMedia($heroAssetId, $ownerCollection, $collectionId, 'hero', 1, true);
            $this->attachMedia($ogAssetId, $ownerCollection, $collectionId, 'og_image', 1, true);

            $this->ensureRenditionsForRole($thumbAssetId, $ownerCollection, 'thumbnail');
            $this->ensureRenditionsForRole($heroAssetId, $ownerCollection, 'hero');
            $this->ensureRenditionsForRole($ogAssetId, $ownerCollection, 'og_image');
        }

        // Product media:
        // - thumbnail (1)
        // - hero (1)
        // - og_image (1)
        // - gallery (3) with exactly one primary among gallery items
        $allProductIds = array_values(array_unique($products['products'] ?? []));

        foreach ($allProductIds as $productId) {
            $productId = (int) $productId;

            $thumbAssetId = $this->getOrCreateImageAsset("products/{$productId}/thumbnail");
            $heroAssetId = $this->getOrCreateImageAsset("products/{$productId}/hero");
            $ogAssetId = $this->getOrCreateImageAsset("products/{$productId}/og_image");

            $this->attachMedia($thumbAssetId, $ownerProduct, $productId, 'thumbnail', 1, true);
            $this->attachMedia($heroAssetId, $ownerProduct, $productId, 'hero', 1, true);
            $this->attachMedia($ogAssetId, $ownerProduct, $productId, 'og_image', 1, true);

            $this->ensureRenditionsForRole($thumbAssetId, $ownerProduct, 'thumbnail');
            $this->ensureRenditionsForRole($heroAssetId, $ownerProduct, 'hero');
            $this->ensureRenditionsForRole($ogAssetId, $ownerProduct, 'og_image');

            // Gallery positions 1..3 with one primary
            $galleryPrimaryPos = random_int(1, 3);
            for ($pos = 1; $pos <= 3; $pos++) {
                $assetId = $this->getOrCreateImageAsset("products/{$productId}/gallery/{$pos}");
                $this->attachMedia($assetId, $ownerProduct, $productId, 'gallery', $pos, $pos === $galleryPrimaryPos);
                $this->ensureRenditionsForRole($assetId, $ownerProduct, 'gallery');
            }
        }
    }

    private function getOrCreateImageAsset(string $seedPath): int
    {
        // Deterministic disk+key so reruns do not create orphaned assets.
        $disk = 's3';
        $key = 'seed/'.trim($seedPath, '/').'.jpg';

        $existing = DB::table('media_assets')->where('disk', $disk)->where('key', $key)->first();
        if ($existing) {
            return (int) $existing->id;
        }

        $assetId = (int) DB::table('media_assets')->insertGetId([
            'type' => 'image',
            'disk' => $disk,
            'key' => $key,
            'mime_type' => 'image/jpeg',
            'bytes' => random_int(80_000, 350_000),
            'width' => 1600,
            'height' => 2000,
            'alt_text' => null,
            'title' => null,
            'caption' => null,
            'checksum' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $assetId;
    }

    private function ensureRenditionsForRole(int $assetId, string $ownerType, string $role): void
    {
        $profiles = $this->profilesForRole($ownerType, $role);
        if (empty($profiles)) {
            return;
        }

        $key = DB::table('media_assets')->where('id', $assetId)->value('key');
        if (! $key) {
            return;
        }

        $this->ensureRenditions($assetId, $key, $profiles);
    }

    private function profilesForRole(string $ownerType, string $role): array
    {
        if ($ownerType === 'App\\Models\\Product') {
            return match ($role) {
                'thumbnail' => ['thumb_sm', 'plp_480w'],
                'gallery' => ['gallery_thumb', 'pdp_1200w'],
                'hero', 'og_image' => ['pdp_1200w'],
                default => [],
            };
        }

        if ($ownerType === 'App\\Models\\Category' || $ownerType === 'App\\Models\\Collection') {
            return ['thumb_sm', 'plp_480w'];
        }

        return [];
    }

    private function ensureRenditions(int $assetId, string $key, array $profiles): void
    {
        $renditions = config('media.renditions', []);

        foreach ($profiles as $profile) {
            $cfg = $renditions[$profile] ?? null;
            if (! $cfg || empty($cfg['width'])) {
                continue;
            }

            $width = (int) $cfg['width'];
            $height = (int) round($width * 1.25);
            $format = $cfg['format'] ?? 'jpg';
            $ext = $format === 'webp' ? 'webp' : 'jpg';
            $mime = $format === 'webp' ? 'image/webp' : 'image/jpeg';

            $pathInfo = pathinfo($key);
            $dir = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'].'/';
            $base = $pathInfo['filename'];
            $renditionKey = $dir.$base.'_'.$profile.'.'.$ext;

            DB::table('media_renditions')->insertOrIgnore([
                'media_asset_id' => $assetId,
                'profile' => $profile,
                'disk' => 's3',
                'key' => $renditionKey,
                'mime_type' => $mime,
                'bytes' => random_int(40_000, 220_000),
                'width' => $width,
                'height' => $height,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function attachMedia(
        int $mediaAssetId,
        string $ownerType,
        int $ownerId,
        string $role,
        int $position,
        bool $isPrimary
    ): void {
        DB::table('media_attachments')->insertOrIgnore([
            'media_asset_id' => $mediaAssetId,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'role' => $role,
            'position' => $position,
            'is_primary' => $isPrimary,
            'alt_text' => null,
            'caption' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedMerchSections(array $products, array $collections, array $cat): void
    {
        $codes = $this->curatedMerchSectionCodes();
        $sections = DB::table('merch_sections')
            ->whereIn('code', $codes)
            ->where('mode', 'curated')
            ->orderBy('sort')
            ->get(['id', 'code', 'item_type', 'default_limit']);

        $found = $sections->pluck('code')->all();
        $missing = array_values(array_diff($codes, $found));
        if (! empty($missing)) {
            throw new \RuntimeException('Missing curated merch sections: '.implode(', ', $missing).'. Run CoreTablesSeeder before CatalogSeeder.');
        }

        foreach ($sections as $section) {
            $sectionId = (int) $section->id;
            $limit = (int) $section->default_limit;

            DB::table('merch_section_items')->where('merch_section_id', $sectionId)->delete();

            if ($section->item_type === 'product') {
                $items = match ($section->code) {
                    'home_featured_products' => array_slice($products['products'], 0, $limit),
                    'home_new_arrivals' => array_slice($products['products'], 0, $limit),
                    default => array_slice($products['products'], 0, $limit),
                };

                $pos = 1;
                foreach ($items as $pid) {
                    DB::table('merch_section_items')->insertOrIgnore([
                        'merch_section_id' => $sectionId,
                        'item_type' => 'product',
                        'item_id' => (int) $pid,
                        'position' => $pos,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pos++;
                }

                continue;
            }

            if ($section->item_type === 'collection') {
                $collectionIds = array_values($collections);
                $items = $section->code === 'home_capsule_collection'
                    ? array_slice($collectionIds, 0, 1)
                    : array_slice($collectionIds, 0, $limit);

                $pos = 1;
                foreach ($items as $cid) {
                    DB::table('merch_section_items')->insertOrIgnore([
                        'merch_section_id' => $sectionId,
                        'item_type' => 'collection',
                        'item_id' => (int) $cid,
                        'position' => $pos,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pos++;
                }

                continue;
            }

            if ($section->item_type === 'category') {
                $leafByRoot = $cat['leaf_by_root'] ?? [];
                $pool = $leafByRoot['type'] ?? ($cat['leaf'] ?? []);
                $items = array_slice(array_values($pool), 0, $limit);

                $pos = 1;
                foreach ($items as $cid) {
                    DB::table('merch_section_items')->insertOrIgnore([
                        'merch_section_id' => $sectionId,
                        'item_type' => 'category',
                        'item_id' => (int) $cid,
                        'position' => $pos,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pos++;
                }

                continue;
            }

            throw new \RuntimeException("Unsupported item_type [{$section->item_type}] for merch section [{$section->code}].");
        }
    }

    private function curatedMerchSectionCodes(): array
    {
        return [
            'home_shop_by_collection',
            'home_new_arrivals',
            'home_shop_by_occasion',
            'home_shop_by_fabric',
            'home_capsule_collection',
            'home_featured_products',
        ];
    }

    // -----------------------------
    // Helpers (naming + text)
    // -----------------------------

    private function buildProductName(string $categoryName, ?string $pieceType, string $fabric, string $colorFamily): string
    {
        $pieceLabel = $pieceType ? "{$pieceType} " : '';

        return "Pinora Threads {$pieceLabel}{$categoryName} - {$fabric} - {$colorFamily}";
    }

    private function buildProductDescription(string $categoryName, ?string $pieceType, string $fabric, string $colorFamily): string
    {
        return implode("\n", array_filter([
            "Premium {$fabric} in {$colorFamily}.",
            $this->includedText($categoryName, $pieceType),
            'Care: Dry clean recommended. Color may vary due to lighting and device display.',
        ]));
    }

    private function includedText(string $categoryName, ?string $pieceType): string
    {
        if ($categoryName === 'Suit') {
            return match ($pieceType) {
                '3 Piece' => 'Includes: Shirt, Trouser, Dupatta.',
                '2 Piece' => 'Includes: Shirt, Trouser.',
                default => 'Includes: Suit set.',
            };
        }

        return match ($categoryName) {
            'Saree' => 'Includes: Saree and blouse piece.',
            'Dupatta' => 'Includes: Dupatta only.',
            'Shawl' => 'Includes: Shawl only.',
            default => 'Includes: As shown.',
        };
    }

    private function materialNotes(string $fabric): string
    {
        return match ($fabric) {
            'Lawn' => 'Lightweight lawn with breathable weave, ideal for warm weather.',
            'Khaddar' => 'Warm khaddar texture, best for cooler seasons.',
            'Cotton' => 'Soft cotton with durable finish.',
            'Silk' => 'Smooth silk hand-feel with natural sheen.',
            'Chiffon' => 'Flowy chiffon drape, delicate handling recommended.',
            'Linen' => 'Linen blend with crisp texture.',
            'Georgette' => 'Textured georgette with a fluid drape and light crepe feel.',
            default => 'Quality fabric with comfortable wear.',
        };
    }

    private function collectionDescription(string $name): string
    {
        return match ($name) {
            'New Arrivals' => 'Fresh styles added recently—discover what’s new.',
            'Best Sellers' => 'Customer favorites and top picks.',
            'Festive Edit' => 'Statement looks curated for festive moments.',
            'Wedding Edit' => 'Elevated pieces curated for wedding season.',
            'Everyday Essentials' => 'Easy-to-wear staples for everyday styling.',
            'Limited Stock' => 'Low-quantity items—grab before they’re gone.',
            default => null,
        };
    }

    private function sku(string $prefix, int $seq, ?int $variant = null): string
    {
        $base = $prefix.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        if ($variant !== null) {
            $base .= '-'.str_pad((string) $variant, 2, '0', STR_PAD_LEFT);
        }

        return $base;
    }

    private function uniqueSlug(string $table, string $baseSlug, string $sku): string
    {
        $slug = $baseSlug;
        $i = 1;

        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.Str::lower($sku).($i > 1 ? "-{$i}" : '');
            $i++;
        }

        return $slug;
    }

    private function randMoney(int $min, int $max): string
    {
        // Decimal(12,2) compatible; always "xx.yy"
        $amount = random_int($min * 100, $max * 100);

        return number_format($amount / 100, 2, '.', '');
    }

    private function pick(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    private function pickMany(array $arr, int $count): array
    {
        $arr = array_values($arr);
        shuffle($arr);

        return array_slice($arr, 0, max(0, $count));
    }
}
