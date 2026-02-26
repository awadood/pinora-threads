<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CoreTablesSeeder extends Seeder
{
    public function run(): void
    {
        $tables = config('lookups.tables', []);
        if (empty($tables)) {
            $this->command?->warn('No lookups configured.');
        }
        if (! empty($tables)) {
            DB::transaction(function () use ($tables) {
                foreach ($tables as $table => $rows) {
                    $this->seedTable($table, $rows);
                }
            });
        }

        DB::table('countries')->upsert([
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'PK', 'name' => 'Pakistan'],
        ], ['code'], ['name']);

        DB::table('states')->upsert([
            ['code' => 'CA', 'name' => 'California', 'country_code' => 'US'],
            ['code' => 'NY', 'name' => 'New York',   'country_code' => 'US'],
        ], ['code'], ['name', 'country_code']);

        DB::table('currencies')->upsert([
            ['code' => 'USD', 'name' => 'US Dollar'],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee'],
        ], ['code'], ['name']);

        DB::table('customer_groups')->upsert([
            ['code' => 'standard', 'name' => 'Standard'],
            ['code' => 'b2b', 'name' => 'B2B'],
            ['code' => 'vip', 'name' => 'VIP'],
        ], ['code'], ['name']);

        $this->seedCatalogAttributes();
        $this->seedCatalogCategories();
        $this->seedMerchSections();

        if (Schema::hasTable('shipment_rates')) {
            DB::table('shipment_rates')->upsert([
                // PKR rates
                ['shipment_method_code' => 'pickup', 'currency_code' => 'PKR', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 10],
                ['shipment_method_code' => 'self', 'currency_code' => 'PKR', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 250, 'active' => true, 'sort_order' => 20],
                ['shipment_method_code' => 'courier', 'currency_code' => 'PKR', 'min_subtotal' => null, 'max_subtotal' => 14999, 'price' => 350, 'active' => true, 'sort_order' => 30],
                ['shipment_method_code' => 'courier', 'currency_code' => 'PKR', 'min_subtotal' => 15000, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 31],

                // USD rates
                ['shipment_method_code' => 'pickup', 'currency_code' => 'USD', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 10],
                ['shipment_method_code' => 'self', 'currency_code' => 'USD', 'min_subtotal' => null, 'max_subtotal' => null, 'price' => 5, 'active' => true, 'sort_order' => 20],
                ['shipment_method_code' => 'courier', 'currency_code' => 'USD', 'min_subtotal' => null, 'max_subtotal' => 99.99, 'price' => 8, 'active' => true, 'sort_order' => 30],
                ['shipment_method_code' => 'courier', 'currency_code' => 'USD', 'min_subtotal' => 100, 'max_subtotal' => null, 'price' => 0, 'active' => true, 'sort_order' => 31],
            ], ['shipment_method_code', 'currency_code', 'sort_order'], ['min_subtotal', 'max_subtotal', 'price', 'active']);
        }
    }

    private function seedTable(string $table, array $rows): void
    {
        foreach ($rows as $row) {
            // Normalize + defaults
            if (isset($row['code'])) {
                $row['code'] = strtolower(trim($row['code']));
            }

            $payload = array_merge([
                'name' => $row['name'] ?? $row['code'] ?? 'Unnamed',
                'active' => $row['active'] ?? true,
                'sort_order' => $row['sort_order'] ?? 0,
            ], $row);

            // Ensure we don’t try to update the match key itself in the update set
            $match = ['code' => $payload['code'] ?? $payload['name']];
            unset($payload['code']);

            DB::table($table)->updateOrInsert($match, $payload);
        }
    }

    private function seedCatalogAttributes(): void
    {
        $timestamp = now();

        $definitions = [
            'piece_type' => ['label' => 'Piece Type', 'type' => 'select'],
            'fabric' => ['label' => 'Fabric', 'type' => 'select'],
            'season' => ['label' => 'Season', 'type' => 'select'],
            'color_family' => ['label' => 'Color Family', 'type' => 'select'],
            'stock_status' => ['label' => 'Stock Status', 'type' => 'select'],
            'occasion' => ['label' => 'Occasion', 'type' => 'multiselect'],
            'embellishment' => ['label' => 'Embellishment', 'type' => 'multiselect'],
            'material_notes' => ['label' => 'Material Notes', 'type' => 'text'],
            'what_included' => ['label' => 'What Included', 'type' => 'text'],
        ];

        $rows = [];
        foreach ($definitions as $code => $def) {
            $rows[] = [
                'code' => $code,
                'label' => $def['label'],
                'type' => $def['type'],
                'active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('attributes')->upsert(
            $rows,
            ['code'],
            ['label', 'type', 'active', 'updated_at']
        );

        $attributeIds = DB::table('attributes')
            ->whereIn('code', array_keys($definitions))
            ->pluck('id', 'code')
            ->map(fn ($id) => (int) $id)
            ->all();

        $optionDefinitions = [
            'piece_type' => ['2 Piece', '3 Piece'],
            'fabric' => ['Lawn', 'Khaddar', 'Cotton', 'Silk', 'Chiffon', 'Linen', 'Georgette'],
            'occasion' => ['Everyday', 'Festive', 'Wedding', 'Party'],
            'season' => ['Summer', 'Winter', 'Mid-Season'],
            'embellishment' => ['Printed', 'Digital Print', 'Embroidered', 'Handwork', 'Plain', 'Sequins', 'Mirror Work'],
            'color_family' => ['Black', 'White', 'Blue', 'Green', 'Red', 'Maroon', 'Pink', 'Purple', 'Yellow', 'Beige', 'Brown', 'Grey', 'Multi'],
            'stock_status' => ['In Stock', 'Out of Stock', 'Coming Soon'],
        ];

        foreach ($optionDefinitions as $code => $values) {
            $attributeId = $attributeIds[$code] ?? null;
            if (! $attributeId) {
                continue;
            }

            $optionRows = [];
            foreach ($values as $index => $value) {
                $optionRows[] = [
                    'attribute_id' => $attributeId,
                    'value' => $value,
                    'sort' => $index + 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('attribute_options')->upsert(
                $optionRows,
                ['attribute_id', 'value'],
                ['sort', 'updated_at']
            );
        }
    }

    private function seedCatalogCategories(): void
    {
        $typeId = $this->upsertCategory('Type', null, 10);

        $typeLeaves = ['Suit', 'Saree', 'Dupatta', 'Shawl'];
        foreach ($typeLeaves as $index => $name) {
            $this->upsertCategory($name, $typeId, ($index + 1) * 10);
        }
    }

    private function upsertCategory(string $name, ?int $parentId, int $sort): int
    {
        $slug = Str::slug($name);

        $existing = DB::table('categories')->where('slug', $slug)->first();
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
    }

    private function seedMerchSections(): void
    {
        foreach ($this->merchSectionDefinitions() as $section) {
            $this->upsertMerchSection($section);
        }
    }

    private function merchSectionDefinitions(): array
    {
        return [
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
                'item_type' => 'attribute', // item_id in merch_section_items refers to attribute_options.id
                'mode' => 'curated',
                'default_limit' => 6,
                'country_code' => null,
                'sort' => 30,
                'active' => true,
                'query_payload' => null,
            ],
            [
                'code' => 'home_shop_by_fabric',
                'name' => 'Shop by Fabric',
                'surface' => 'home',
                'item_type' => 'attribute', // item_id in merch_section_items refers to attribute_options.id
                'mode' => 'curated',
                'default_limit' => 6,
                'country_code' => null,
                'sort' => 40,
                'active' => true,
                'query_payload' => null,
            ],
            [
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
                'query_payload' => [
                    'sort' => 'newest',
                    'filter' => [
                        'collection.slug.eq' => 'best-sellers',
                    ],
                ],
            ],
        ];
    }

    private function upsertMerchSection(array $section): int
    {
        $existing = DB::table('merch_sections')->where('code', $section['code'])->first();

        $payload = $this->normalizeMerchQueryPayload($section['query_payload'] ?? null);
        $data = [
            'name' => $section['name'],
            'surface' => $section['surface'],
            'item_type' => $section['item_type'],
            'mode' => $section['mode'],
            'default_limit' => (int) $section['default_limit'],
            'country_code' => $section['country_code'],
            'starts_at' => $section['starts_at'] ?? null,
            'ends_at' => $section['ends_at'] ?? null,
            'sort' => (int) $section['sort'],
            'active' => (bool) $section['active'],
            'query_payload' => $payload,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('merch_sections')->where('id', $existing->id)->update($data);

            return (int) $existing->id;
        }

        $data['code'] = $section['code'];
        $data['created_at'] = now();

        return (int) DB::table('merch_sections')->insertGetId($data);
    }

    private function normalizeMerchQueryPayload($payload): ?string
    {
        if (is_array($payload)) {
            return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return json_last_error() === JSON_ERROR_NONE
                ? json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : null;
        }

        if ($payload !== null) {
            return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return null;
    }
}
