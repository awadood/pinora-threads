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

            // 1) Attributes + options
            $attrs = $this->seedAttributes();

            // 2) Categories (tree)
            $cat = $this->seedCategories();

            // 3) Collections + collection_country
            $collections = $this->seedCollections();

            // 4) Products + variants + prices + variant attributes + category assignment
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

    /**
     * Returns:
     * [
     *   'attr' => ['piece_type' => 1, ...],
     *   'opt'  => ['piece_type' => ['3-piece suit' => 10, ...], ...],
     * ]
     */
    private function seedAttributes(): array
    {
        $upsertAttr = function (string $code, string $label, string $type = 'select', bool $active = true): int {
            $existing = DB::table('attributes')->where('code', $code)->first();

            if ($existing) {
                DB::table('attributes')->where('id', $existing->id)->update([
                    'label' => $label,
                    'type' => $type,
                    'active' => $active,
                    'updated_at' => now(),
                ]);

                return (int) $existing->id;
            }

            return (int) DB::table('attributes')->insertGetId([
                'code' => $code,
                'label' => $label,
                'type' => $type,
                'active' => $active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $upsertOption = function (int $attributeId, string $value, int $sort): int {
            $existing = DB::table('attribute_options')
                ->where('attribute_id', $attributeId)
                ->where('value', $value)
                ->first();

            if ($existing) {
                DB::table('attribute_options')->where('id', $existing->id)->update([
                    'sort' => $sort,
                    'updated_at' => now(),
                ]);

                return (int) $existing->id;
            }

            return (int) DB::table('attribute_options')->insertGetId([
                'attribute_id' => $attributeId,
                'value' => $value,
                'sort' => $sort,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $attrIds = [
            // Select attributes
            'piece_type' => $upsertAttr('piece_type', 'Piece Type', 'select'),
            'fabric' => $upsertAttr('fabric', 'Fabric', 'select'),
            'color' => $upsertAttr('color', 'Color', 'select'),
            'size' => $upsertAttr('size', 'Size', 'select'),

            // Text attributes (stored on product_attributes.value)
            'material_notes' => $upsertAttr('material_notes', 'Material Notes', 'text'),
            'what_included' => $upsertAttr('what_included', 'What’s Included', 'text'),
        ];

        $options = [
            'piece_type' => [
                '3-piece suit',
                '2-piece suit',
                '1-piece (fabric)',
                'dupatta',
                'shawl',
            ],
            'fabric' => [
                'Lawn',
                'Khaddar',
                'Cotton',
                'Silk',
                'Chiffon',
                'Linen',
            ],
            'color' => [
                'Black',
                'White',
                'Off White',
                'Navy',
                'Maroon',
                'Teal',
                'Mustard',
                'Olive',
                'Pink',
                'Beige',
            ],
            'size' => [
                'XS', 'S', 'M', 'L', 'XL',
            ],
        ];

        $optIds = [];
        foreach ($options as $code => $values) {
            $attrId = $attrIds[$code];
            $optIds[$code] = [];

            $sort = 1;
            foreach ($values as $v) {
                $optIds[$code][$v] = $upsertOption($attrId, $v, $sort);
                $sort++;
            }
        }

        return [
            'attr' => $attrIds,
            'opt' => $optIds,
        ];
    }

    private function seedCategories(): array
    {
        $upsertCategory = function (string $name, ?int $parentId, int $sort): int {
            $slug = Str::slug($name);

            $existing = DB::table('categories')
                ->where('slug', $slug)
                ->when($parentId === null, fn ($q) => $q->whereNull('parent_id'))
                ->when($parentId !== null, fn ($q) => $q->where('parent_id', $parentId))
                ->first();

            $data = [
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parentId,
                'sort' => $sort,
                'active' => true,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update($data);

                return (int) $existing->id;
            }

            $data['created_at'] = now();

            return (int) DB::table('categories')->insertGetId($data);
        };

        // Roots (LOCKED)
        $unstitched = $upsertCategory('Unstitched', null, 10);
        $occasion = $upsertCategory('Occasion', null, 20);

        // Unstitched -> Fabrics (LOCKED)
        $fabricNames = ['Lawn', 'Khaddar', 'Chiffon', 'Silk', 'Organza'];
        $fabricLeafIds = [];
        $pos = 1;
        foreach ($fabricNames as $name) {
            $fabricLeafIds[] = $upsertCategory($name, $unstitched, $pos * 10);
            $pos++;
        }

        // Occasion -> (LOCKED)
        $occasionNames = ['Everyday Wear', 'Festive Wear', 'Wedding Wear', 'Party Wear'];
        $occasionLeafIds = [];
        $pos = 1;
        foreach ($occasionNames as $name) {
            $occasionLeafIds[] = $upsertCategory($name, $occasion, $pos * 10);
            $pos++;
        }

        return [
            'roots' => [
                'unstitched' => $unstitched,
                'occasion' => $occasion,
            ],
            'leaf' => array_values(array_merge($fabricLeafIds, $occasionLeafIds)),
            'leaf_by_root' => [
                'unstitched' => array_values($fabricLeafIds),
                'occasion' => array_values($occasionLeafIds),
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

        $products = [];
        $variants = [];
        $byProduct = [];

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
            $piece = $this->pick(array_keys($attrs['opt']['piece_type']));
            $fabric = $this->pick(array_keys($attrs['opt']['fabric']));
            $color = $this->pick(array_keys($attrs['opt']['color']));

            $productName = $this->buildProductName($piece, $fabric, $color);
            $productSku = $this->sku('PRD', $seq);
            $slug = $this->uniqueSlug('products', Str::slug($productName), $productSku);

            $productId = $this->upsertProduct(
                sku: $productSku,
                name: $productName,
                slug: $slug,
                type: $type,
                description: $this->buildProductDescription($piece, $fabric, $color),
            );

            $products[] = $productId;

            // Assign to one leaf category
            $categoryId = (int) $this->pick($leafCategoryIds);
            DB::table('category_product')->insertOrIgnore([
                'category_id' => $categoryId,
                'product_id' => $productId,
            ]);

            // Product attributes (select + text)
            $sizeForProduct = $type === 'variable'
                ? $this->pick(['S', 'M', 'L', 'XL'])
                : $this->pick(['S', 'M', 'L']);

            $this->attachVariantSelectAttr($productId, $attrs['attr']['piece_type'], $attrs['opt']['piece_type'][$piece]);
            $this->attachVariantSelectAttr($productId, $attrs['attr']['fabric'], $attrs['opt']['fabric'][$fabric]);
            $this->attachVariantSelectAttr($productId, $attrs['attr']['color'], $attrs['opt']['color'][$color]);
            $this->attachVariantSelectAttr($productId, $attrs['attr']['size'], $attrs['opt']['size'][$sizeForProduct]);
            $this->attachVariantTextAttr($productId, $attrs['attr']['material_notes'], $this->materialNotes($fabric));
            $this->attachVariantTextAttr($productId, $attrs['attr']['what_included'], $this->includedText($piece));

            // Product prices per currency
            $usd = $this->randMoney(35, 250);
            $pkr = $this->randMoney(2500, 45000);
            $this->upsertVariantPrice($productId, 'USD', $usd, null);
            $this->upsertVariantPrice($productId, 'PKR', $pkr, null);

            // Variants: only for first 5 products
            $productIndex = $seq;
            $variantCount = $productIndex <= 5 ? random_int(2, 4) : 0;

            $byProduct[$productId] = [];

            for ($v = 1; $v <= $variantCount; $v++) {
                $variantSku = $this->sku('VAR', $seq, $v);

                // IMPORTANT: schema column is "name" (not title)
                $variantName = $variantCount > 1 ? "{$productName} - Option {$v}" : null;

                $variantId = $this->upsertVariant(
                    productId: $productId,
                    sku: $variantSku,
                    name: $variantName,
                    description: null,
                    isDefault: $v === 1,
                    active: true,
                );

                $variants[] = $variantId;
                $byProduct[$productId][] = $variantId;

                // Variant attributes (select)
                $this->attachVariantSelectAttr($variantId, $attrs['attr']['piece_type'], $attrs['opt']['piece_type'][$piece]);
                $this->attachVariantSelectAttr($variantId, $attrs['attr']['fabric'], $attrs['opt']['fabric'][$fabric]);
                $this->attachVariantSelectAttr($variantId, $attrs['attr']['color'], $attrs['opt']['color'][$color]);

                // Size: vary a bit for variable products
                $size = $type === 'variable'
                    ? $this->pick(['S', 'M', 'L', 'XL'])
                    : $this->pick(['S', 'M', 'L']);

                $this->attachVariantSelectAttr($variantId, $attrs['attr']['size'], $attrs['opt']['size'][$size]);

                // Variant attributes (text)
                $this->attachVariantTextAttr($variantId, $attrs['attr']['material_notes'], $this->materialNotes($fabric));
                $this->attachVariantTextAttr($variantId, $attrs['attr']['what_included'], $this->includedText($piece));

                // Variant prices per currency (unique per variant + currency)
                $vUsd = $this->randMoney(35, 250);
                $vPkr = $this->randMoney(2500, 45000);

                $this->upsertVariantPrice($variantId, 'USD', $vUsd, null);
                $this->upsertVariantPrice($variantId, 'PKR', $vPkr, null);
            }

            // Publish product (optional but useful for dev)
            DB::table('products')->where('id', $productId)->update([
                'active' => true,
                'published_at' => now(),
                'first_published_at' => DB::raw('COALESCE(first_published_at, published_at)'),
                'updated_at' => now(),
            ]);

            $seq++;
        }

        return [
            'products' => $products,
            'variants' => $variants,
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

    private function upsertVariant(
        int $productId,
        string $sku,
        ?string $name,
        ?string $description,
        bool $isDefault,
        bool $active
    ): int {
        $existing = DB::table('products')->where('sku', $sku)->first();
        if ($existing) {
            DB::table('products')->where('id', $existing->id)->update([
                'name' => $name ?? $existing->name,
                'description' => $description,
                'type' => 'simple',
                'active' => $active,
                'updated_at' => now(),
            ]);

            DB::table('product_variants')->insertOrIgnore([
                'product_id' => $productId,
                'variant_id' => (int) $existing->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        $slug = $this->uniqueSlug('products', Str::slug($name ?? $sku), $sku);

        $variantProductId = (int) DB::table('products')->insertGetId([
            'sku' => $sku,
            'name' => $name ?? $sku,
            'slug' => $slug,
            'type' => 'simple',
            'description' => $description,
            'tax_class_id' => self::TAX_CLASS_ID,
            'active' => $active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_variants')->insertOrIgnore([
            'product_id' => $productId,
            'variant_id' => $variantProductId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $variantProductId;
    }

    private function upsertVariantPrice(int $variantId, string $currencyCode, string $amount, ?string $compareAt): void
    {
        $existing = DB::table('product_prices')
            ->where('product_id', $variantId)
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
            'product_id' => $variantId,
            'currency_code' => $currencyCode,
            'amount' => $amount,
            'compare_at' => $compareAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachVariantSelectAttr(int $variantId, int $attributeId, int $optionId): void
    {
        DB::table('product_attributes')->insertOrIgnore([
            'product_id' => $variantId,
            'attribute_id' => $attributeId,
            'option_id' => $optionId,
            'value' => null,
        ]);
    }

    private function attachVariantTextAttr(int $variantId, int $attributeId, string $value): void
    {
        DB::table('product_attributes')->insertOrIgnore([
            'product_id' => $variantId,
            'attribute_id' => $attributeId,
            'option_id' => null,
            'value' => $value,
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
        // For products typed "bundle", create bundle lines pointing to other products.
        $bundleProductIds = DB::table('products')->where('type', 'bundle')->pluck('id')->all();
        if (empty($bundleProductIds)) {
            return;
        }

        $allVariantProductIds = $products['variants'] ?? [];
        if (count($allVariantProductIds) < 5) {
            return;
        }

        foreach ($bundleProductIds as $bundlePid) {
            $bundlePid = (int) $bundlePid;

            // Avoid duplicates across reruns
            DB::table('product_bundles')->where('product_id', $bundlePid)->delete();

            $pick = $this->pickMany($allVariantProductIds, random_int(2, 4));
            foreach ($pick as $vid) {
                DB::table('product_bundles')->insertOrIgnore([
                    'product_id' => $bundlePid,
                    'bundle_item_id' => (int) $vid,
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
        $allProductIds = array_values(array_unique(array_merge(
            $products['products'] ?? [],
            $products['variants'] ?? [],
        )));

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
        // Sections are homogeneous by schema: product OR collection OR category.
        // Seed home sections (mix of curated + query).
        $sections = [
            [
                'code' => 'home_shop_by_collection',
                'name' => 'Shop by Collection',
                'surface' => 'home',
                'item_type' => 'collection',
                'mode' => 'curated',
                'default_limit' => 6,
                'country_code' => null,
                'sort' => 10,
                'active' => true,
                'query_payload' => null,
            ],
            [
                'code' => 'home_new_arrivals',
                'name' => 'New Arrivals',
                'surface' => 'home',
                'item_type' => 'product',
                'mode' => 'curated',
                'default_limit' => 8,
                'country_code' => null,
                'sort' => 20,
                'active' => true,
                'query_payload' => null,
            ],
            [
                'code' => 'home_shop_by_occasion',
                'name' => 'Shop by Occasion',
                'surface' => 'home',
                'item_type' => 'category',
                'mode' => 'curated',
                'default_limit' => 6,
                'country_code' => null,
                'sort' => 30,
                'active' => true,
                'query_payload' => null,
            ],
            [
                // NEW: Unstitched items (Lawn/Khaddar/Chiffon/Silk/Organza)
                'code' => 'home_shop_by_fabric',
                'name' => 'Shop by Fabric',
                'surface' => 'home',
                'item_type' => 'category',
                'mode' => 'curated',
                'default_limit' => 6,
                'country_code' => null,
                'sort' => 40,
                'active' => true,
                'query_payload' => null,
            ],
            [
                // just add one collection
                'code' => 'home_capsule_collection',
                'name' => 'Capsule Collection',
                'surface' => 'home',
                'item_type' => 'collection',
                'mode' => 'curated',
                'default_limit' => 1,
                'country_code' => null,
                'sort' => 50,
                'active' => true,
                'query_payload' => null,
            ],
            [
                'code' => 'home_featured_products',
                'name' => 'Featured Products',
                'surface' => 'home',
                'item_type' => 'product',
                'mode' => 'curated',
                'default_limit' => 8,
                'country_code' => null,
                'sort' => 60,
                'active' => true,
                'query_payload' => null,
            ],
            [
                'code' => 'home_best_sellers_query',
                'name' => 'Best Sellers (Query)',
                'surface' => 'home',
                'item_type' => 'product',
                'mode' => 'query',
                'default_limit' => 8,
                'country_code' => null,
                'sort' => 70,
                'active' => true,
                // normalized shape used by your storefront query-mode section:
                // ['sort' => ..., 'filter' => [...]]
                'query_payload' => [
                    'sort' => 'newest',
                    'filter' => [
                        // Example: drive best-sellers from a dedicated collection.
                        // Replace slug to match your seeded/real collection.
                        'collection.slug.eq' => 'best-sellers',
                    ],
                ],
            ],
        ];

        foreach ($sections as $s) {
            $sectionId = $this->upsertMerchSection($s);

            if ($s['mode'] !== 'curated') {
                continue;
            }

            // Curated items
            DB::table('merch_section_items')->where('merch_section_id', $sectionId)->delete();

            if ($s['item_type'] === 'product') {
                $items = match ($s['code']) {
                    'home_featured_products' => array_slice($products['products'], 0, (int) $s['default_limit']),
                    'home_new_arrivals' => array_slice($products['products'], 0, (int) $s['default_limit']),
                    default => array_slice($products['products'], 0, (int) $s['default_limit']),
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
            }

            if ($s['item_type'] === 'collection') {
                $collectionIds = array_values($collections);

                // capsule collection: only one
                $items = $s['code'] === 'home_capsule_collection'
                    ? array_slice($collectionIds, 0, 1)
                    : array_slice($collectionIds, 0, (int) $s['default_limit']);

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
            }

            if ($s['item_type'] === 'category') {
                $leafByRoot = $cat['leaf_by_root'] ?? [];

                $pool = match ($s['code']) {
                    'home_shop_by_occasion' => $leafByRoot['occasion'] ?? ($cat['leaf'] ?? []),
                    'home_shop_by_fabric' => $leafByRoot['unstitched'] ?? ($cat['leaf'] ?? []),
                    default => $cat['leaf'] ?? [],
                };

                $items = array_slice(array_values($pool), 0, (int) $s['default_limit']);

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
            }
        }
    }

    private function upsertMerchSection(array $s): int
    {
        $existing = DB::table('merch_sections')->where('code', $s['code'])->first();

        // Normalize query_payload:
        // - curated: null
        // - query: array with keys { sort, filter }
        $payload = $s['query_payload'] ?? null;
        if (is_array($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } elseif (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = (json_last_error() === JSON_ERROR_NONE)
                ? json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : null;
        } elseif ($payload !== null) {
            $payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $data = [
            'name' => $s['name'],
            'surface' => $s['surface'],
            'item_type' => $s['item_type'],
            'mode' => $s['mode'],
            'default_limit' => (int) $s['default_limit'],
            'country_code' => $s['country_code'],
            'starts_at' => $s['starts_at'] ?? null,
            'ends_at' => $s['ends_at'] ?? null,
            'sort' => (int) $s['sort'],
            'active' => (bool) $s['active'],
            'query_payload' => $payload,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('merch_sections')->where('id', $existing->id)->update($data);

            return (int) $existing->id;
        }

        $data['code'] = $s['code'];
        $data['created_at'] = now();

        return (int) DB::table('merch_sections')->insertGetId($data);
    }

    // -----------------------------
    // Helpers (naming + text)
    // -----------------------------

    private function buildProductName(string $piece, string $fabric, string $color): string
    {
        $pieceLabel = match ($piece) {
            '3-piece suit' => '3-Piece Unstitched Suit',
            '2-piece suit' => '2-Piece Unstitched Suit',
            '1-piece (fabric)' => '1-Piece Fabric',
            'dupatta' => 'Dupatta',
            'shawl' => 'Shawl',
            default => 'Product',
        };

        return "Pinora Threads {$pieceLabel} - {$fabric} - {$color}";
    }

    private function buildProductDescription(string $piece, string $fabric, string $color): string
    {
        return implode("\n", array_filter([
            "Premium {$fabric} in {$color}.",
            $this->includedText($piece),
            'Care: Dry clean recommended. Color may vary due to lighting and device display.',
        ]));
    }

    private function includedText(string $piece): string
    {
        return match ($piece) {
            '3-piece suit' => 'Includes: Shirt, Trouser, Dupatta.',
            '2-piece suit' => 'Includes: Shirt, Trouser.',
            '1-piece (fabric)' => 'Includes: Fabric cut (unstitched).',
            'dupatta' => 'Includes: Dupatta only.',
            'shawl' => 'Includes: Shawl only.',
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
