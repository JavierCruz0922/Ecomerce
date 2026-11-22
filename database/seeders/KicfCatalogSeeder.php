<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class KicfCatalogSeeder extends Seeder
{
    public function run(?string $manifest = null): void
    {
        $products = $this->readCatalog($manifest ?? database_path('data/kicf-catalog.json'));

        $inserted = DB::transaction(function () use ($products): int {
            $names = array_values(array_unique(array_column($products, 'category')));
            $now = now();

            Category::query()->insertOrIgnore(array_map(
                static fn (string $name): array => ['name' => $name, 'created_at' => $now, 'updated_at' => $now],
                $names,
            ));
            $categories = Category::query()->whereIn('name', $names)->pluck('id', 'name');
            if ($categories->count() !== count($names)) {
                throw new RuntimeException('Importación revertida. No se pudieron asociar todas las categorías.');
            }

            $rows = array_map(static function (array $product) use ($categories, $now): array {
                $category = $product['category'];
                unset($product['category']);

                return $product + ['category_id' => $categories[$category], 'created_at' => $now, 'updated_at' => $now];
            }, $products);

            // The SKU constraint preserves all existing rows, including retired products.
            $inserted = Product::query()->insertOrIgnore($rows);
            if (Product::withTrashed()->whereIn('sku', array_column($products, 'sku'))->count() !== count($products)) {
                throw new RuntimeException('Importación revertida. No se encontraron todos los SKU de la fuente.');
            }

            return $inserted;
        }, 3);

        $this->command?->info(sprintf(
            'Catálogo KICF. Insertados: %d. Existentes: %d. Rechazados: 0.',
            $inserted,
            count($products) - $inserted,
        ));
    }

    private function readCatalog(string $manifest): array
    {
        if (! is_file($manifest) || ! is_readable($manifest) || ($json = file_get_contents($manifest)) === false) {
            throw new InvalidArgumentException('Importación cancelada. No se pudo leer el manifiesto del catálogo KICF.');
        }

        try {
            $records = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new InvalidArgumentException('Importación cancelada. El manifiesto del catálogo KICF no es JSON válido.', previous: $error);
        }

        if (! is_array($records) || ! array_is_list($records) || count($records) !== 20) {
            throw new InvalidArgumentException('Importación cancelada. Se requieren exactamente 20 productos.');
        }

        $expected = array_map(static fn (int $index): string => sprintf('PAP%03d', $index), range(1, 20));
        $products = [];
        $rejected = [];
        foreach ($records as $index => $record) {
            $sku = $record['sku'] ?? null;
            if (! is_string($sku) || ! in_array($sku, $expected, true) || isset($products[$sku])) {
                $rejected[] = 'Fila '.($index + 1).': SKU inválido o duplicado.';

                continue;
            }

            $product = $this->validateProduct($record, $sku);
            if ($product === null) {
                $rejected[] = 'Fila '.($index + 1).': datos, precio, existencias o imagen inválidos.';

                continue;
            }

            $products[$sku] = $product;
        }

        if ($rejected !== []) {
            throw new InvalidArgumentException(sprintf(
                'Importación cancelada. Insertados: 0. Existentes procesados: 0. Rechazados: %d. %s',
                count($rejected),
                implode(' ', $rejected),
            ));
        }

        return array_values($products);
    }

    private function validateProduct(array $record, string $sku): ?array
    {
        $product = ['sku' => $sku];
        foreach (['name', 'category', 'color', 'material', 'description'] as $field) {
            $value = $record[$field] ?? null;
            if (! is_string($value) || ($value = trim($value)) === '' || ($field !== 'description' && mb_strlen($value) > 255)) {
                return null;
            }
            $product[$field] = $value;
        }

        $price = $record['price_cop'] ?? null;
        $stock = $record['stock'] ?? null;
        $image = $record['image_path'] ?? null;
        if (! is_int($price) || $price <= 0 || ! is_int($stock) || $stock < 0 || $stock > 2147483647
            || ! is_string($image) || preg_match('/^'.preg_quote($sku, '/').'\.(?:png|jpe?g)$/iD', $image) !== 1) {
            return null;
        }

        return $product + ['price_cop' => $price, 'stock' => $stock, 'image_path' => $image];
    }
}
