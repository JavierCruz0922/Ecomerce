<?php

namespace Tests\Feature;

use App\Livewire\DemoCounter;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireDemoTest extends TestCase
{
    public function test_demo_route_renders_livewire_and_blade(): void
    {
        $this->get(route('demo.livewire'))
            ->assertOk()
            ->assertSee('Prueba de Livewire')
            ->assertSeeLivewire(DemoCounter::class)
            ->assertSeeHtml('data-testid="demo-count">0</output>');
    }

    public function test_counter_increments_and_resets_with_livewire_updates(): void
    {
        Livewire::test(DemoCounter::class)
            ->assertSet('count', 0)
            ->call('increment')
            ->assertSet('count', 1)
            ->assertSeeHtml('data-testid="demo-count">1</output>')
            ->call('increment')
            ->assertSet('count', 2)
            ->call('clearCount')
            ->assertSet('count', 0)
            ->assertSeeHtml('data-testid="demo-count">0</output>');
    }
}
