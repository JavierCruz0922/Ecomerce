<x-store-layout title="Créditos y licencias de imágenes">
    <header class="mb-7 max-w-3xl">
        <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">Créditos y licencias de imágenes <span class="block text-xl tracking-normal text-muted-ink">Papelería KICF</span></h1>
        <p class="mt-3 text-base leading-relaxed text-muted-ink">Las imágenes ilustran el tipo de artículo. Las marcas y características visibles pueden diferir de los datos del catálogo académico. Cada archivo conserva su licencia individual.</p>
    </header>

    <ul class="grid gap-4 md:grid-cols-2" aria-label="Atribuciones por producto">
        @foreach (config('catalog_images') as $sku => $credit)
            <li id="{{ $sku }}" class="min-w-0 scroll-mt-5 rounded-2xl border border-paper-line bg-white p-5 target:border-pine target:ring-2 target:ring-pine">
                <p class="mb-1 text-xs font-semibold text-muted-ink">{{ $sku }}</p>
                <h2 class="text-lg font-semibold">{{ $credit['product'] }}</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div>
                        <dt class="font-semibold">Título original y fuente</dt>
                        <dd><a href="{{ $credit['source'] }}" class="break-words text-pine underline underline-offset-2">{{ $credit['title'] }}</a> · {{ $credit['source_label'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">Autor</dt>
                        <dd>{{ $credit['author'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">Licencia</dt>
                        <dd><a href="{{ $credit['license_url'] }}" class="text-pine underline underline-offset-2">{{ $credit['license'] }}</a></dd>
                    </div>
                    <div>
                        <dt class="font-semibold">Modificaciones</dt>
                        <dd>{{ $credit['changes'] }}</dd>
                    </div>
                </dl>
            </li>
        @endforeach
    </ul>
</x-store-layout>
