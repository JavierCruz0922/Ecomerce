<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CatalogSchemaTest extends TestCase
{
    private const MIGRATION = 'database/migrations/2026_11_21_000001_create_catalog_and_orders_tables.php';

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateIsolatedSqlite();
    }

    public function test_order_relations_and_snapshots_survive_catalog_changes_and_retirement(): void
    {
        $product = $this->createProduct();
        $user = User::factory()->create();
        $order = Order::create(['user_id' => $user->id]);
        $item = $order->items()->create($this->itemAttributes($product));

        $this->assertTrue($user->is($order->user));
        $this->assertTrue($order->is($item->order));
        $this->assertTrue($product->is($product->category->products()->sole()));
        $this->assertTrue($item->is($order->items()->sole()));

        $product->update(['sku' => 'PAP999', 'name' => 'Nuevo nombre', 'price_cop' => 1500, 'stock' => 0]);
        $this->assertSame(0, $product->fresh()->stock);
        $product->delete();

        $this->assertNull(Product::find($product->id));
        $item->refresh();
        $this->assertTrue($item->product->trashed());
        $this->assertSame('PAP001', $item->product_sku);
        $this->assertSame('Cuaderno', $item->product_name);
        $this->assertSame(800, $item->unit_price_cop);
        $this->assertSame(2, $item->quantity);
    }

    public function test_new_products_default_to_zero_stock(): void
    {
        $this->assertSame(0, $this->createProduct()->refresh()->stock);
    }

    #[DataProvider('invalidCatalogValues')]
    public function test_numeric_constraints_reject_invalid_values(string $table, string $column, int|float|string $value, string $constraint): void
    {
        $product = $this->createProduct();
        $order = Order::create(['user_id' => User::factory()->create()->id]);
        $item = $order->items()->create($this->itemAttributes($product));

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage($constraint);

        DB::table($table)->where('id', $table === 'products' ? $product->id : $item->id)
            ->update([$column => $value]);
    }

    public static function invalidCatalogValues(): array
    {
        return [
            'zero price' => ['products', 'price_cop', 0, 'products_price_positive'],
            'fractional price' => ['products', 'price_cop', 800.5, 'products_price_positive'],
            'negative stock' => ['products', 'stock', -1, 'products_stock_range'],
            'fractional stock' => ['products', 'stock', 2.5, 'products_stock_range'],
            'text stock' => ['products', 'stock', 'agotado', 'products_stock_range'],
            'stock outside integer range' => ['products', 'stock', 2147483648, 'products_stock_range'],
            'zero quantity' => ['order_items', 'quantity', 0, 'order_items_quantity_range'],
            'fractional quantity' => ['order_items', 'quantity', 1.5, 'order_items_quantity_range'],
            'negative order price' => ['order_items', 'unit_price_cop', -1, 'order_items_price_positive'],
            'fractional order price' => ['order_items', 'unit_price_cop', 800.5, 'order_items_price_positive'],
        ];
    }

    #[DataProvider('invalidRelationships')]
    public function test_schema_protects_keys_and_purchase_history(string $violation, string $constraint): void
    {
        $product = $this->createProduct();
        $order = Order::create(['user_id' => User::factory()->create()->id]);
        $attributes = $this->itemAttributes($product);
        $item = $order->items()->create($attributes);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage($constraint);

        match ($violation) {
            'category name' => Category::create(['name' => $product->category->name]),
            'product sku' => $product->replicate()->save(),
            'order product pair' => $order->items()->create($attributes),
            'missing category' => $product->update(['category_id' => $product->category_id + 1]),
            'missing user' => $order->update(['user_id' => $order->user_id + 1]),
            'missing order' => $item->update(['order_id' => $order->id + 1]),
            'missing product' => $item->update(['product_id' => $product->id + 1]),
            'category deletion' => $product->category->delete(),
            'product deletion' => $product->forceDelete(),
            'user deletion' => $order->user->delete(),
        };
    }

    public static function invalidRelationships(): array
    {
        return [
            'category name' => ['category name', 'UNIQUE constraint failed: categories.name'],
            'product sku' => ['product sku', 'UNIQUE constraint failed: products.sku'],
            'order product pair' => ['order product pair', 'UNIQUE constraint failed: order_items.order_id, order_items.product_id'],
            'missing category' => ['missing category', 'FOREIGN KEY constraint failed'],
            'missing user' => ['missing user', 'FOREIGN KEY constraint failed'],
            'missing order' => ['missing order', 'FOREIGN KEY constraint failed'],
            'missing product' => ['missing product', 'FOREIGN KEY constraint failed'],
            'category deletion' => ['category deletion', 'FOREIGN KEY constraint failed'],
            'product deletion' => ['product deletion', 'FOREIGN KEY constraint failed'],
            'user deletion' => ['user deletion', 'FOREIGN KEY constraint failed'],
        ];
    }

    public function test_scoped_rollback_and_reapply_preserve_native_tables_and_users(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();
        $order = Order::create(['user_id' => $user->id]);
        $order->items()->create($this->itemAttributes($product));

        $this->artisan('migrate:rollback', ['--step' => 1, '--path' => [self::MIGRATION]])->assertSuccessful();

        $catalogTables = ['categories', 'products', 'orders', 'order_items'];
        foreach ($catalogTables as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }

        foreach (['users', 'password_reset_tokens', 'sessions', 'cache', 'jobs'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
        $this->assertTrue($user->is(User::find($user->id)));

        $this->artisan('migrate', ['--path' => [self::MIGRATION]])->assertSuccessful();

        foreach ($catalogTables as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
        $this->assertTrue($user->is(User::find($user->id)));
    }

    private function createProduct(): Product
    {
        return Category::create(['name' => 'Papel'])->products()->create([
            'sku' => 'PAP001',
            'name' => 'Cuaderno',
            'color' => 'Azul',
            'material' => 'Papel',
            'description' => 'Cuaderno de prueba',
            'image_path' => 'PAP001.png',
            'price_cop' => 800,
        ]);
    }

    private function itemAttributes(Product $product): array
    {
        return [
            'product_id' => $product->id,
            'product_sku' => $product->sku,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price_cop' => $product->price_cop,
        ];
    }
}
