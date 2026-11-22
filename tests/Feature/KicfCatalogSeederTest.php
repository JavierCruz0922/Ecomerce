<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\KicfCatalogSeeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KicfCatalogSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateIsolatedSqlite();
    }

    public function test_seeder_loads_twenty_exact_products_with_integral_inventory_and_valid_categories(): void
    {
        $source = $this->catalog();

        $this->artisan('db:seed', ['--class' => KicfCatalogSeeder::class])
            ->expectsOutput('Catálogo KICF. Insertados: 20. Existentes: 0. Rechazados: 0.')
            ->assertSuccessful();

        $products = Product::query()->with('category')->orderBy('sku')->get();
        $this->assertSame(
            array_map(static fn (int $index): string => sprintf('PAP%03d', $index), range(1, 20)),
            $products->pluck('sku')->all(),
        );
        $this->assertDatabaseCount('categories', 4);
        $this->assertSame([], DB::select('pragma foreign_key_check'));

        foreach ($products as $index => $product) {
            $expected = $source[$index];
            $this->assertSame($expected['category'], $product->category->name);
            $this->assertIsInt($product->price_cop);
            $this->assertGreaterThan(0, $product->price_cop);
            $this->assertIsInt($product->stock);
            $this->assertGreaterThanOrEqual(0, $product->stock);
            unset($expected['category']);
            $this->assertSame($expected, $product->only(array_keys($expected)), $product->sku);
        }
    }

    public function test_repeat_run_preserves_managed_values_and_does_not_restore_retired_products(): void
    {
        $this->seed(KicfCatalogSeeder::class);

        $product = Product::query()->where('sku', 'PAP001')->sole();
        $manualCategory = Category::create(['name' => 'Categoría administrada']);
        $product->update([
            'category_id' => $manualCategory->id,
            'name' => 'Nombre administrado',
            'color' => 'Color administrado',
            'material' => 'Material administrado',
            'description' => 'Descripción administrada',
            'price_cop' => 4321,
            'stock' => 0,
            'image_path' => 'imagen-administrada.png',
        ]);
        $managed = $product->refresh()->getRawOriginal();
        $retired = Product::query()->where('sku', 'PAP002')->sole();
        $retired->delete();
        $retiredFields = $retired->refresh()->getRawOriginal();

        $this->artisan('db:seed', ['--class' => KicfCatalogSeeder::class])
            ->expectsOutput('Catálogo KICF. Insertados: 0. Existentes: 20. Rechazados: 0.')
            ->assertSuccessful();

        $this->assertDatabaseCount('products', 20);
        $this->assertDatabaseCount('categories', 5);
        $this->assertSame(20, Product::withTrashed()->distinct()->count('sku'));
        $this->assertSame($managed, $product->refresh()->getRawOriginal());
        $this->assertSame($retiredFields, $retired->refresh()->getRawOriginal());
        $this->assertTrue($retired->trashed());
        $this->assertSame([], DB::select('pragma foreign_key_check'));
    }

    #[DataProvider('invalidData')]
    public function test_invalid_rows_reject_the_entire_import_without_writing(string $field, mixed $value): void
    {
        $records = $this->catalog();
        $records[0][$field] = $value;
        $file = tmpfile();
        $this->assertIsResource($file);

        try {
            fwrite($file, json_encode($records, JSON_THROW_ON_ERROR));
            $this->app->make(KicfCatalogSeeder::class)->run(stream_get_meta_data($file)['uri']);
            $this->fail('An invalid row must reject the import.');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString('Insertados: 0. Existentes procesados: 0. Rechazados: 1.', $error->getMessage());
        } finally {
            fclose($file);
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
    }

    public static function invalidData(): array
    {
        return [
            'duplicate SKU' => ['sku', 'PAP002'],
            'empty name' => ['name', ''],
            'empty category' => ['category', ' '],
            'fractional price' => ['price_cop', 10.5],
            'negative stock' => ['stock', -1],
            'wrong image' => ['image_path', 'PAP002.png'],
        ];
    }

    private function catalog(): array
    {
        return json_decode(file_get_contents(database_path('data/kicf-catalog.json')), true, flags: JSON_THROW_ON_ERROR);
    }
}
