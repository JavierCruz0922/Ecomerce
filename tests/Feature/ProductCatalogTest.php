<?php

namespace Tests\Feature;

use App\Livewire\ProductCatalog;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\KicfCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KicfCatalogSeeder::class);
    }

    public function test_it_lists_twenty_seeded_products_with_price_and_stock(): void
    {
        $page = Livewire::test(ProductCatalog::class)
            ->assertSee('20 productos')
            ->assertSeeHtml('src="'.asset('images/catalogo/PAP001.jpg').'"')
            ->assertSee('$ 1.500')
            ->assertSee('COP')
            ->assertSee('Disponible · 50 uds.');

        foreach (range(1, 20) as $number) {
            $page->assertSeeHtml('href="'.route('catalog.show', sprintf('PAP%03d', $number)).'"');
        }
    }

    public function test_it_searches_names_and_skus_and_handles_no_matches(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('search', 'cuaderno')->call('applyFilters')
            ->assertSee('2 productos')
            ->assertSee('Cuaderno cuadriculado')
            ->assertSee('Cuaderno rayado')
            ->assertDontSee('Resma de papel carta')
            ->set('search', ' pap020 ')->call('applyFilters')
            ->assertSee('1 producto')
            ->assertSee('Cinta adhesiva transparente')
            ->assertDontSee('Cuaderno rayado')
            ->set('search', 'producto inexistente')->call('applyFilters')
            ->assertSee('0 productos')
            ->assertSee('No hay productos que coincidan');
    }

    public function test_it_filters_by_category_and_inclusive_price_and_can_clear_combined_filters(): void
    {
        $paper = Category::query()->where('name', 'Papel')->sole();

        Livewire::test(ProductCatalog::class)
            ->set('category', $paper->id)->call('applyFilters')->assertSee('5 productos')
            ->call('clearFilters')
            ->set('minPrice', '3500')->set('maxPrice', '8000')->call('applyFilters')
            ->assertSee('Marcador permanente negro')
            ->assertSee('Cuaderno cuadriculado')
            ->assertDontSee('Resma de papel carta')
            ->set('category', $paper->id)->call('applyFilters')->assertSee('4 productos')
            ->set('search', 'Cuaderno')->call('applyFilters')->assertSee('2 productos')
            ->call('clearFilters')->assertSee('20 productos')
            ->assertSet('search', '')->assertSet('category', '')
            ->assertSet('minPrice', '')->assertSet('maxPrice', '');
    }

    public function test_it_rejects_invalid_input_and_keeps_the_last_valid_results(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('search', 'PAP020')->call('applyFilters')
            ->set('search', ['wrong'])->set('category', 99999)
            ->set('minPrice', '-1')->set('maxPrice', '2.5')->call('applyFilters')
            ->assertHasErrors(['search' => 'string', 'category' => 'exists', 'minPrice' => 'min', 'maxPrice' => 'integer'])
            ->assertSee('Cinta adhesiva transparente')
            ->set('search', 'PAP020')->set('category', '')
            ->set('minPrice', '8000')->set('maxPrice', '5000')->call('applyFilters')
            ->assertHasErrors(['maxPrice' => 'gte'])
            ->call('clearFilters')->assertHasNoErrors()->assertSee('20 productos');
    }

    public function test_it_shows_sold_out_items_without_writing_stock(): void
    {
        $product = Product::query()->where('sku', 'PAP020')->sole();
        $product->update(['stock' => 0]);
        $before = Product::query()->orderBy('sku')->pluck('stock', 'sku')->all();

        Livewire::test(ProductCatalog::class)
            ->set('search', 'PAP020')->call('applyFilters')
            ->assertSee('Cinta adhesiva transparente')->assertSee('Agotado')->assertDontSee('Disponible')
            ->call('clearFilters');

        $this->assertSame($before, Product::query()->orderBy('sku')->pluck('stock', 'sku')->all());
    }

    public function test_it_paginates_more_than_twenty_products_and_resets_on_filtering(): void
    {
        $first = Product::query()->where('sku', 'PAP001')->sole();
        $first->replicate()->fill(['sku' => 'PAP021', 'name' => 'Producto de página dos'])->save();

        Livewire::test(ProductCatalog::class)
            ->assertSee('21 productos')->assertDontSee('Producto de página dos')
            ->call('gotoPage', 2)->assertSee('Producto de página dos')->assertDontSee('Bolígrafo azul')
            ->set('search', 'PAP001')->call('applyFilters')
            ->assertSee('1 producto')->assertSee('Bolígrafo azul');
    }
}
