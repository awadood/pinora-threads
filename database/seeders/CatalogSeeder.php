<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    private const TAX_CLASS_ID = 1;

    public function run(): void
    {
        DB::transaction(function () {
            $this->guardPrereqs();

            // 1) Attributes + options (for filters)
            $attrs = $this->seedAttributes();

            // 2) Categories (tree)
            $cat = $this->seedCategories();

            // 3) Collections (manual)
            $collections = $this->seedCollections();

            // 4) 100 products + (1-3) variants + prices + attributes + category assignment
            $products = $this->seedProductsAndVariants($attrs, $cat);

            // 5) Attach products to collections (manual curation)
            $this->attachCollections($collections, $products);

            // 6) Seed media assets + attachments + renditions (dummy keys)
            //    NOTE: Products have NO media anymore. Media belongs to:
            //    - variants: thumbnail, gallery, hero, og_image
            //    - categories: thumbnail, hero, og_image
            //    - collections: thumbnail, hero, og_image
            $this->seedMediaSystem($cat, $collections, $products);
        });
    }

    private function guardPrereqs(): void
    {
        $hasUsd = DB::table('countries')->where('code', 'US')->exists();
        $hasPkr = DB::table('countries')->where('code', 'PK')->exists();
        $hasUsd = DB::table('currencies')->where('code', 'USD')->exists();
        $hasPkr = DB::table('currencies')->where('code', 'PKR')->exists();

        if (! $hasUsd || ! $hasPkr) {
            throw new \RuntimeException('Missing currencies. Ensure currencies table includes USD and PKR before seeding.');
        }

        $hasTax = DB::table('tax_classes')->where('id', self::TAX_CLASS_ID)->exists();
        if (! $hasTax) {
            throw new \RuntimeException('Missing tax_classes row with id='.self::TAX_CLASS_ID.'. Adjust TAX_CLASS_ID in CatalogSeeder.');
        }
    }

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

        $attrs = [
            'piece_type' => $upsertAttr('piece_type', 'Piece Type', 'select'),
            'fabric_type' => $upsertAttr('fabric_type', 'Fabric Type', 'select'),
            'color_family' => $upsertAttr('color_family', 'Color Family', 'select'),
            'season' => $upsertAttr('season', 'Season', 'select'),

            // Text attributes
            'occasion_tags' => $upsertAttr('occasion_tags', 'Occasion Tags', 'text'),
            'shirt_length' => $upsertAttr('shirt_length', 'Shirt Fabric Length', 'text'),
            'trouser_length' => $upsertAttr('trouser_length', 'Trouser Fabric Length', 'text'),
            'dupatta_length' => $upsertAttr('dupatta_length', 'Dupatta Fabric Length', 'text'),
            'what_included' => $upsertAttr('what_included', "What's Included", 'text'),
        ];

        $upsertOptions = function (int $attributeId, array $values): array {
            $optionIds = [];
            $sort = 1;
            foreach ($values as $value) {
                $existing = DB::table('attribute_options')
                    ->where('attribute_id', $attributeId)
                    ->where('value', $value)
                    ->first();

                if ($existing) {
                    DB::table('attribute_options')->where('id', $existing->id)->update([
                        'sort' => $sort++,
                        'updated_at' => now(),
                    ]);
                    $optionIds[$value] = (int) $existing->id;

                    continue;
                }

                $id = DB::table('attribute_options')->insertGetId([
                    'attribute_id' => $attributeId,
                    'value' => $value,
                    'sort' => $sort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $optionIds[$value] = (int) $id;
            }

            return $optionIds;
        };

        $options = [];
        $options['piece_type'] = $upsertOptions($attrs['piece_type'], [
            '3-piece suit', '2-piece suit', '1-piece (fabric)', 'dupatta', 'shawl',
        ]);

        $options['fabric_type'] = $upsertOptions($attrs['fabric_type'], [
            'lawn', 'cotton', 'chiffon', 'silk', 'winter',
        ]);

        $options['color_family'] = $upsertOptions($attrs['color_family'], [
            'black', 'white', 'red', 'blue', 'green', 'beige', 'pink', 'purple', 'maroon', 'teal', 'grey',
        ]);

        $options['season'] = $upsertOptions($attrs['season'], [
            'summer', 'winter', 'all-season',
        ]);

        return [
            'attr' => $attrs,
            'opt' => $options,
        ];
    }

    private function seedCategories(): array
    {
        $upsertCategory = function (string $name, ?int $parentId, int $sort): int {
            $slug = Str::slug($name);
            $existing = DB::table('categories')->where('slug', $slug)->first();

            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update([
                    'name' => $name,
                    'parent_id' => $parentId,
                    'sort' => $sort,
                    'active' => true,
                    'updated_at' => now(),
                ]);

                return (int) $existing->id;
            }

            return (int) DB::table('categories')->insertGetId([
                'name' => $name,
                'meta_title' => $name,
                'meta_description' => $name,
                'slug' => $slug,
                'parent_id' => $parentId,
                'sort' => $sort,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $unstitched = $upsertCategory('Unstitched', null, 1);
        $separates = $upsertCategory('Separates', null, 2);

        $threePiece = $upsertCategory('Wedding Wear', $unstitched, 1);
        $twoPiece = $upsertCategory('Festive Wear', $unstitched, 2);
        $onePiece = $upsertCategory('Everyday', $unstitched, 3);

        $dupattas = $upsertCategory('Dupattas', $separates, 1);
        $shawls = $upsertCategory('Shawls', $separates, 2);

        return [
            'root' => [
                'unstitched' => $unstitched,
                'separates' => $separates,
            ],
            'leaf' => [
                'three_piece' => $threePiece,
                'two_piece' => $twoPiece,
                'one_piece' => $onePiece,
                'dupatta' => $dupattas,
                'shawl' => $shawls,
            ],
        ];
    }

    private function seedCollections(): array
    {
        $names = [
            'Latest Drop',
            'Eid Edit',
            'Wedding Guest Edit',
            'Festive Edit',
            'Everyday Elegance',
            'Signature Prints',
            'Limited Pieces',
        ];

        $ids = [];
        $sort = 1;

        foreach ($names as $index => $name) {
            $slug = Str::slug($name);
            $existing = DB::table('collections')->where('slug', $slug)->first();

            if ($existing) {
                DB::table('collections')->where('id', $existing->id)->update([
                    'name' => $name,
                    'sort' => $sort++,
                    'active' => true,
                    'updated_at' => now(),
                ]);
                $collectionId = (int) $existing->id;
            } else {
                $collectionId = (int) DB::table('collections')->insertGetId([
                    'name' => $name,
                    'meta_title' => $name,
                    'meta_description' => $name,
                    'slug' => $slug,
                    'sort' => $sort++,
                    'description' => 'Seeded collection for storefront merchandising',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $ids[$name] = $collectionId;

            // 2. Sync Relationships in pivot table (collection_country)
            // Clear existing to avoid duplicates if re-running
            DB::table('collection_country')->where('collection_id', $collectionId)->delete();

            // Add PK for all collections
            DB::table('collection_country')->insert(['collection_id' => $collectionId, 'country_code' => 'PK']);

            // Add USA for the first four collections (index 0, 1, 2, 3)
            if ($index < 4) {
                DB::table('collection_country')->insert(['collection_id' => $collectionId, 'country_code' => 'US']);
            }
        }

        return $ids;
    }

    /**
     * Creates exactly 100 products.
     * Each product: 1-3 variants (so “at least one or more variants” is satisfied).
     * Product type set to "variable" because now we truly have multiple variants.
     */
    private function seedProductsAndVariants(array $attrs, array $cat): array
    {
        $fabricPool = ['lawn', 'cotton', 'chiffon', 'silk', 'winter'];
        $colorPool = ['black', 'white', 'red', 'blue', 'green', 'beige', 'pink', 'purple', 'maroon', 'teal', 'grey'];
        $seasonPool = ['summer', 'winter', 'all-season'];
        $occasionSets = [
            'eid,festive',
            'wedding,festive',
            'festive',
            'casual,everyday',
            'eid,wedding',
            'everyday',
        ];

        $plan = [
            ['piece' => '3-piece suit',        'leaf' => 'three_piece', 'count' => 30],
            ['piece' => '2-piece suit',        'leaf' => 'two_piece',   'count' => 25],
            ['piece' => '1-piece (fabric)',    'leaf' => 'one_piece',   'count' => 15],
            ['piece' => 'dupatta',             'leaf' => 'dupatta',     'count' => 15],
            ['piece' => 'shawl',               'leaf' => 'shawl',       'count' => 15],
        ];

        $products = [];
        $seq = 1;

        foreach ($plan as $chunk) {
            for ($i = 1; $i <= $chunk['count']; $i++) {

                $fabric = $fabricPool[array_rand($fabricPool)];
                $color = $colorPool[array_rand($colorPool)];
                $season = $seasonPool[array_rand($seasonPool)];
                $ocTags = $occasionSets[array_rand($occasionSets)];

                $name = $this->buildProductName($chunk['piece'], $fabric, $color);
                $slug = Str::slug($name).'-'.$seq;

                $productSku = sprintf('PNR-PROD-%04d', $seq);

                $productId = (int) DB::table('products')->insertGetId([
                    'sku' => $productSku,
                    'name' => $name,
                    'meta_title' => $name,
                    'meta_description' => "Pinora Threads: {$name}",
                    'slug' => $slug,
                    'type' => 'variable',
                    'description' => $this->buildProductDescription($chunk['piece'], $fabric, $color),
                    'tax_class_id' => self::TAX_CLASS_ID,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Pivot table has no timestamps in your migration, so do not add them.
                DB::table('category_product')->insert([
                    'category_id' => $cat['leaf'][$chunk['leaf']],
                    'product_id' => $productId,
                ]);

                // Variants: 1–3 per product
                $variantCount = random_int(1, 3);

                // Ensure we don’t repeat exact same (fabric,color) inside the same product
                $usedCombos = [];

                $minUsd = null;
                $minPkr = null;

                for ($v = 1; $v <= $variantCount; $v++) {
                    // Mix up fabric/color per variant for differentiation
                    $vfabric = $fabricPool[array_rand($fabricPool)];
                    $vcolor = $colorPool[array_rand($colorPool)];

                    $comboKey = "{$vfabric}|{$vcolor}";
                    if (isset($usedCombos[$comboKey])) {
                        // retry once
                        $vfabric = $fabricPool[array_rand($fabricPool)];
                        $vcolor = $colorPool[array_rand($colorPool)];
                        $comboKey = "{$vfabric}|{$vcolor}";
                    }
                    $usedCombos[$comboKey] = true;

                    $variantSku = sprintf('PNR-VAR-%04d-%02d', $seq, $v);

                    $variantTitle = $this->buildVariantTitle($chunk['piece'], $vfabric, $vcolor);

                    $variantId = (int) DB::table('product_variants')->insertGetId([
                        'product_id' => $productId,
                        'sku' => $variantSku,
                        'title' => $variantTitle,
                        'description' => null,
                        'default' => $v === 1,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Select attributes via option_id
                    $this->attachVariantSelectAttr($variantId, $attrs['attr']['piece_type'], $attrs['opt']['piece_type'][$chunk['piece']]);
                    $this->attachVariantSelectAttr($variantId, $attrs['attr']['fabric_type'], $attrs['opt']['fabric_type'][$vfabric]);
                    $this->attachVariantSelectAttr($variantId, $attrs['attr']['color_family'], $attrs['opt']['color_family'][$vcolor]);
                    $this->attachVariantSelectAttr($variantId, $attrs['attr']['season'], $attrs['opt']['season'][$season]);

                    // Text attributes
                    $this->attachVariantTextAttr($variantId, $attrs['attr']['occasion_tags'], $ocTags);

                    if (in_array($chunk['piece'], ['3-piece suit', '2-piece suit', '1-piece (fabric)'], true)) {
                        $lengths = $this->generateUnstitchedLengths($chunk['piece']);
                        $this->attachVariantTextAttr($variantId, $attrs['attr']['shirt_length'], $lengths['shirt']);
                        $this->attachVariantTextAttr($variantId, $attrs['attr']['trouser_length'], $lengths['trouser']);
                        $this->attachVariantTextAttr($variantId, $attrs['attr']['dupatta_length'], $lengths['dupatta']);
                        $this->attachVariantTextAttr($variantId, $attrs['attr']['what_included'], $lengths['included']);
                    } else {
                        $included = $chunk['piece'] === 'dupatta'
                            ? '1 x Dupatta (standalone)'
                            : '1 x Shawl (standalone)';
                        $this->attachVariantTextAttr($variantId, $attrs['attr']['what_included'], $included);
                    }

                    // Prices per variant
                    $usd = $this->randMoney(150, 600);
                    $pkr = $this->randMoney(4000, 60000);

                    DB::table('product_variant_prices')->insert([
                        [
                            'product_variant_id' => $variantId,
                            'currency_code' => 'USD',
                            'amount' => $usd,
                            'compare_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'product_variant_id' => $variantId,
                            'currency_code' => 'PKR',
                            'amount' => $pkr,
                            'compare_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);

                    // Maintain product min prices
                    $minUsd = $minUsd === null ? (float) $usd : min($minUsd, (float) $usd);
                    $minPkr = $minPkr === null ? (float) $pkr : min($minPkr, (float) $pkr);

                    // Return structure used by your media/collection logic
                    $products[] = [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'piece' => $chunk['piece'],
                        'fabric' => $vfabric,
                        'color' => $vcolor,
                    ];
                }

                $seq++;
            }
        }

        return $products;
    }

    private function attachVariantSelectAttr(int $variantId, int $attributeId, int $optionId): void
    {
        DB::table('product_variant_attributes')->insert([
            'product_variant_id' => $variantId,
            'attribute_id' => $attributeId,
            'option_id' => $optionId,
            'value' => null,
        ]);
    }

    private function attachVariantTextAttr(int $variantId, int $attributeId, string $value): void
    {
        DB::table('product_variant_attributes')->insert([
            'product_variant_id' => $variantId,
            'attribute_id' => $attributeId,
            'option_id' => null,
            'value' => $value,
        ]);
    }

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

        return 'Pinora Threads '.Str::ucfirst($fabric)." {$pieceLabel} - ".Str::ucfirst($color);
    }

    private function buildVariantTitle(string $piece, string $fabric, string $color): string
    {
        return Str::ucfirst($fabric).' · '.Str::ucfirst($color).' · '.match ($piece) {
            '3-piece suit' => '3-Piece',
            '2-piece suit' => '2-Piece',
            '1-piece (fabric)' => '1-Piece',
            'dupatta' => 'Dupatta',
            'shawl' => 'Shawl',
            default => 'Variant',
        };
    }

    private function buildProductDescription(string $piece, string $fabric, string $color): string
    {
        $base = 'Pinora Threads textile design crafted for modern South Asian women. ';
        $base .= "Fabric: {$fabric}. Color family: {$color}. ";

        return $base.match ($piece) {
            '3-piece suit' => 'Includes shirt, trouser, and dupatta fabric (unstitched).',
            '2-piece suit' => 'Includes shirt and trouser fabric (unstitched).',
            '1-piece (fabric)' => 'Includes shirt fabric only (unstitched).',
            'dupatta' => 'Standalone dupatta—no stitching required.',
            'shawl' => 'Standalone shawl—no stitching required.',
            default => 'Premium textile product.',
        };
    }

    private function generateUnstitchedLengths(string $piece): array
    {
        $shirt = $this->randStep(2.75, 3.25, 0.25).' m (shirt)';
        $trouser = $this->randStep(2.25, 2.75, 0.25).' m (trouser)';
        $dupatta = $this->randStep(2.25, 2.75, 0.25).' m (dupatta)';

        return match ($piece) {
            '3-piece suit' => [
                'shirt' => $shirt,
                'trouser' => $trouser,
                'dupatta' => $dupatta,
                'included' => 'Shirt fabric + Trouser fabric + Dupatta fabric (unstitched)',
            ],
            '2-piece suit' => [
                'shirt' => $shirt,
                'trouser' => $trouser,
                'dupatta' => '',
                'included' => 'Shirt fabric + Trouser fabric (unstitched)',
            ],
            '1-piece (fabric)' => [
                'shirt' => $shirt,
                'trouser' => '',
                'dupatta' => '',
                'included' => 'Shirt fabric only (unstitched)',
            ],
            default => [
                'shirt' => '',
                'trouser' => '',
                'dupatta' => '',
                'included' => '',
            ],
        };
    }

    private function randMoney(int $min, int $max): string
    {
        return number_format(random_int($min, $max), 2, '.', '');
    }

    private function randStep(float $min, float $max, float $step): string
    {
        $count = (int) floor(($max - $min) / $step);
        $n = random_int(0, max(0, $count));

        return number_format($min + ($n * $step), 2, '.', '');
    }

    private function attachCollections(array $collections, array $products): void
    {
        $byId = array_values($products);

        $map = [
            'Latest Drop' => array_slice($byId, 0, 20),
            'Eid Edit' => array_slice($byId, 20, 16),
            'Wedding Guest Edit' => array_slice($byId, 36, 16),
            'Festive Edit' => array_slice($byId, 52, 16),
            'Everyday Elegance' => array_slice($byId, 68, 16),
            'Signature Prints' => array_slice($byId, 84, 12),
            'Limited Pieces' => array_slice($byId, 96, 4),
        ];

        foreach ($map as $collectionName => $items) {
            $collectionId = $collections[$collectionName] ?? null;
            if (! $collectionId) {
                continue;
            }

            $sort = 1;
            foreach ($items as $p) {
                // collection_product has no timestamps in your migration
                DB::table('collection_product')->insertOrIgnore([
                    'collection_id' => $collectionId,
                    'product_id' => $p['product_id'],
                    'sort' => $sort++,
                ]);
            }
        }
    }

    private function seedMediaSystem(array $cat, array $collections, array $products): void
    {
        $ownerVariant = 'App\\Models\\ProductVariant';
        $ownerCategory = 'App\\Models\\Category';
        $ownerCollection = 'App\\Models\\Collection';

        // Category media: thumbnail + hero + og_image for each category (exactly 1 each)
        $categoryIds = DB::table('categories')->pluck('id')->all();
        foreach ($categoryIds as $categoryId) {
            $categoryId = (int) $categoryId;

            $thumbAssetId = $this->createImageAsset("Category {$categoryId} thumbnail");
            $heroAssetId = $this->createImageAsset("Category {$categoryId} hero");
            $ogAssetId = $this->createImageAsset("Category {$categoryId} og image");

            $this->attachMedia($thumbAssetId, $ownerCategory, $categoryId, 'thumbnail', 1, true);
            $this->attachMedia($heroAssetId, $ownerCategory, $categoryId, 'hero', 1, true);
            $this->attachMedia($ogAssetId, $ownerCategory, $categoryId, 'og_image', 1, true);
        }

        // Collection media: thumbnail + hero + og_image for each collection (exactly 1 each)
        foreach ($collections as $name => $collectionId) {
            $collectionId = (int) $collectionId;

            $thumbAssetId = $this->createImageAsset("Collection {$name} thumbnail");
            $heroAssetId = $this->createImageAsset("Collection {$name} hero");
            $ogAssetId = $this->createImageAsset("Collection {$name} og image");

            $this->attachMedia($thumbAssetId, $ownerCollection, $collectionId, 'thumbnail', 1, true);
            $this->attachMedia($heroAssetId, $ownerCollection, $collectionId, 'hero', 1, true);
            $this->attachMedia($ogAssetId, $ownerCollection, $collectionId, 'og_image', 1, true);
        }

        // Variant media ONLY (products have no media):
        // - thumbnail (1)
        // - gallery (7) => position 1 primary, others not
        // - hero (1)
        // - og_image (1)
        foreach ($products as $p) {
            $variantId = (int) $p['variant_id'];

            // thumbnail
            $vThumbAssetId = $this->createImageAsset("Variant {$variantId} thumbnail");
            $this->attachMedia($vThumbAssetId, $ownerVariant, $variantId, 'thumbnail', 1, true);

            // gallery (7)
            for ($i = 1; $i <= 7; $i++) {
                $assetId = $this->createImageAsset("Variant {$variantId} gallery {$i}");
                $this->attachMedia($assetId, $ownerVariant, $variantId, 'gallery', $i, $i === 1);
            }

            // hero
            $vHeroAssetId = $this->createImageAsset("Variant {$variantId} hero");
            $this->attachMedia($vHeroAssetId, $ownerVariant, $variantId, 'hero', 1, true);

            // og_image
            $vOgAssetId = $this->createImageAsset("Variant {$variantId} og image");
            $this->attachMedia($vOgAssetId, $ownerVariant, $variantId, 'og_image', 1, true);
        }
    }

    private function createImageAsset(string $label): int
    {
        $key = sprintf(
            'local/assets/image/%s/%s.jpg',
            now()->format('Y/m'),
            (string) Str::uuid()
        );

        $assetId = (int) DB::table('media_assets')->insertGetId([
            'type' => 'image',
            'disk' => 's3',
            'key' => $key,
            'mime_type' => 'image/jpeg',
            'bytes' => random_int(120_000, 600_000),
            'width' => 1200,
            'height' => 1600,
            'alt_text' => $label,
            'title' => $label,
            'caption' => null,
            'checksum' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Only the two thumbnail renditions requested: thumb_sm + thumb_md
        $profiles = [
            ['profile' => 'thumb_sm', 'w' => 200, 'h' => 260],
            ['profile' => 'thumb_md', 'w' => 360, 'h' => 480],
        ];

        foreach ($profiles as $p) {
            DB::table('media_renditions')->insert([
                'media_asset_id' => $assetId,
                'profile' => $p['profile'],
                'disk' => 's3',
                'key' => str_replace('.jpg', '_'.$p['profile'].'.jpg', $key),
                'mime_type' => 'image/jpeg',
                'bytes' => random_int(40_000, 220_000),
                'width' => $p['w'],
                'height' => $p['h'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $assetId;
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
}
