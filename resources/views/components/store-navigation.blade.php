@php
    $links = ['home' => 'Inicio', 'catalog.index' => 'Catálogo'];

    if (app()->environment(['local', 'testing'])) {
        $links['demo.livewire'] = 'Demo Livewire';
    }
@endphp

<header class="border-b border-paper-line bg-paper">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-8 gap-y-2 px-5 py-3 sm:px-8 lg:px-12">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-3 whitespace-nowrap text-base font-semibold">
            <span aria-hidden="true" class="grid size-9 place-items-center rounded-lg bg-pine text-xs font-bold tracking-wide text-white">K</span>
            Papelería KICF
        </a>

        <nav aria-label="Navegación principal" class="flex min-w-0 flex-wrap gap-1">
            @foreach ($links as $name => $label)
                @php($active = request()->routeIs($name) || ($name === 'catalog.index' && request()->routeIs('catalog.show')))
                <a href="{{ route($name) }}"
                    @if ($active) aria-current="{{ request()->routeIs($name) ? 'page' : 'location' }}" @endif
                    @class([
                        'inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-semibold',
                        'bg-pine text-white' => $active,
                        'text-ink hover:bg-paper-shade' => ! $active,
                    ])>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
