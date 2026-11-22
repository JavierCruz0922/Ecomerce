<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\KicfCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogImageCreditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_original_files_match_the_catalog_and_the_public_credit_records(): void
    {
        $this->seed(KicfCatalogSeeder::class);
        $credits = config('catalog_images');
        $products = Product::query()->orderBy('sku')->get();
        $page = $this->get(route('images.credits'))->assertOk();
        $this->assertSame($products->pluck('sku')->all(), array_keys($credits));
        $this->assertCount(20, $credits);
        $this->assertSame([
            'CC BY 3.0' => 1,
            'CC0 1.0' => 12,
            'CC BY-SA 4.0' => 4,
            'CC BY-SA 2.5' => 1,
            'Dominio público' => 1,
            'CC BY-SA 3.0' => 1,
        ], array_count_values(array_column($credits, 'license')));

        foreach ($products as $product) {
            $credit = $credits[$product->sku];
            $this->assertSame($product->name, $credit['product']);
            $this->assertSame($product->image_path, $credit['file']);
            $this->assertMatchesRegularExpression('/^'.preg_quote($product->sku, '/').'\.(png|jpg)$/', $credit['file']);
            $file = public_path('images/catalogo/'.$credit['file']);
            $this->assertFileExists($file);
            $this->assertSame(str_ends_with($file, '.png') ? 'image/png' : 'image/jpeg', getimagesize($file)['mime']);
            $page->assertSeeHtml('id="'.$product->sku.'"')
                ->assertSee($credit['title'])
                ->assertSee($credit['author'])
                ->assertSeeHtml('href="'.e($credit['source']).'"')
                ->assertSee($credit['license'])
                ->assertSeeHtml('href="'.e($credit['license_url']).'"')
                ->assertSee($credit['changes']);
        }

        $this->assertSame('Coyau', $credits['PAP017']['author']);
        $this->assertSame('Jonatan Svensson Glad', $credits['PAP020']['author']);
        $this->assertSame('Tape Roll isolated on White Background.jpg', $credits['PAP020']['title']);
    }

    public function test_credits_are_linked_from_home_catalog_and_details_in_production(): void
    {
        $this->seed(KicfCatalogSeeder::class);
        $this->app->instance('env', 'production');
        $credits = route('images.credits');

        foreach ([route('home'), route('catalog.index'), route('catalog.show', 'PAP020')] as $url) {
            $this->get($url)->assertOk()
                ->assertSeeHtml('<footer')
                ->assertSeeHtml('href="'.$credits.'"');
        }

        $this->get(route('catalog.show', 'PAP020'))->assertOk()
            ->assertSee('Jonatan Svensson Glad')
            ->assertSee('Tape Roll isolated on White Background.jpg')
            ->assertSeeHtml('href="'.$credits.'#PAP020"');
        $this->get($credits)->assertOk();
    }

    public function test_missing_image_falls_back_to_the_placeholder(): void
    {
        $this->seed(KicfCatalogSeeder::class);
        $this->app->usePublicPath(storage_path('missing-catalog-assets'));
        $this->get(route('catalog.show', 'PAP001'))->assertOk()
            ->assertSee('Imagen provisional')
            ->assertDontSee(asset('images/catalogo/PAP001.jpg'));
    }

    public function test_unapproved_references_never_render_an_image_url(): void
    {
        $this->seed(KicfCatalogSeeder::class);
        $product = Product::query()->where('sku', 'PAP001')->sole();

        foreach (['PAP002.png', 'robots.txt', 'PAP001.svg'] as $reference) {
            $product->update(['image_path' => $reference]);
            $this->get(route('catalog.show', $product->sku))->assertOk()
                ->assertSee('Imagen provisional')
                ->assertDontSee('/images/catalogo/');
        }
    }
}
