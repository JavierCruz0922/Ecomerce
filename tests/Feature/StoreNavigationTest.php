<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\KicfCatalogSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_has_kicf_navigation_and_live_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Papelería KICF')
            ->assertSee('Explorar catálogo')
            ->assertSeeHtml('aria-label="Navegación principal"')
            ->assertSeeHtml('href="'.route('catalog.index').'"')
            ->assertSeeHtml('href="'.route('demo.livewire').'"')
            ->assertSeeInOrder([route('home'), 'aria-current="page"', 'Inicio'], false)
            ->assertDontSee('Carrito')
            ->assertDontSee('Iniciar sesión')
            ->assertDontSee('Mis pedidos')
            ->assertDontSee('Administración');
    }

    public function test_catalog_and_detail_link_back_and_mark_the_catalog_active(): void
    {
        $this->seed(KicfCatalogSeeder::class);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSeeHtml('href="'.route('home').'"')
            ->assertSeeInOrder([route('catalog.index'), 'aria-current="page"', 'Catálogo'], false)
            ->assertSeeHtml('href="'.route('catalog.show', 'PAP006').'"');

        $this->get(route('catalog.show', 'PAP006'))
            ->assertOk()
            ->assertSee('Cuaderno cuadriculado')
            ->assertSee('PAP006')
            ->assertSee('Volver al catálogo')
            ->assertSeeInOrder([route('catalog.index'), 'aria-current="location"', 'Catálogo'], false);

        $this->get(route('catalog.show', 'SKU-INEXISTENTE'))->assertNotFound();
    }

    public function test_production_has_no_demo_link_and_the_demo_returns_not_found(): void
    {
        $this->app->instance('env', 'production');

        foreach (['home', 'catalog.index'] as $name) {
            $this->get(route($name))->assertOk()->assertDontSee('Demo Livewire')
                ->assertDontSeeHtml('href="'.route('demo.livewire').'"');
        }

        $this->get(route('demo.livewire'))->assertNotFound();
    }

    public function test_each_visual_and_title_link_opens_its_own_detail_without_changing_stock(): void
    {
        $this->seed(KicfCatalogSeeder::class);
        $products = Product::query()->orderBy('sku')->get();
        $stockBefore = $products->pluck('stock', 'sku')->all();

        $page = $this->get(route('catalog.index'))->assertOk();
        $document = new DOMDocument;
        $document->loadHTML($page->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $xpath = new DOMXPath($document);
        $cards = $xpath->query('//li[@data-testid="catalog-product"]');
        $this->assertCount(20, $cards);

        foreach ($cards as $index => $card) {
            $product = $products[$index];
            $url = route('catalog.show', $product->sku);
            $link = $xpath->query('.//a[@data-testid="product-visual-link"]', $card)->item(0);
            $title = $xpath->query('.//h2/a', $card)->item(0);

            $this->assertNotNull($link);
            $this->assertNotNull($title);
            $this->assertSame($url, $link->getAttribute('href'));
            $this->assertSame($url, $title->getAttribute('href'));
            $this->assertSame("Ver detalle de {$product->name} ({$product->sku})", $link->getAttribute('aria-label'));
            $this->assertFalse($link->hasAttribute('tabindex'));
            $this->assertStringContainsString('Imagen provisional', $link->textContent);

            $this->get($link->getAttribute('href'))
                ->assertOk()
                ->assertSee($product->name)
                ->assertSee($product->sku);
        }

        $this->assertSame($stockBefore, Product::query()->orderBy('sku')->pluck('stock', 'sku')->all());
    }

    public function test_retired_product_has_no_catalog_link_and_cannot_be_opened(): void
    {
        $this->seed(KicfCatalogSeeder::class);
        Product::query()->where('sku', 'PAP001')->sole()->delete();

        $url = route('catalog.show', 'PAP001');

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('19 productos')
            ->assertDontSee('Bolígrafo azul')
            ->assertDontSeeHtml('href="'.$url.'"');

        $this->get($url)->assertNotFound();
    }
}
