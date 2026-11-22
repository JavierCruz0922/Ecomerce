<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DemoCounter extends Component
{
    #[Locked]
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function clearCount(): void
    {
        $this->count = 0;
    }

    public function render(): View
    {
        return view('livewire.demo-counter');
    }
}
