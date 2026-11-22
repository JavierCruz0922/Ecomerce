<?php

namespace Tests\Feature;

use Database\Seeders\KicfCatalogSeeder;
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
}
