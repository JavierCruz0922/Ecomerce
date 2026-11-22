<x-store-layout :title="$product->name">
    <a href="{{ route('catalog.index') }}" class="inline-flex min-h-11 items-center gap-2 rounded text-sm font-semibold text-pine hover:underline">
        <span aria-hidden="true">←</span> Volver al catálogo
    </a>

    <article class="mt-4 grid gap-8 md:grid-cols-2 md:items-start">
        <x-product-image :product="$product" :detail="true" />

        <div>
            <p class="text-sm font-semibold text-muted-ink">{{ $product->category->name }} <span aria-hidden="true">·</span> {{ $product->sku }}</p>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight sm:text-5xl">{{ $product->name }}</h1>
            <div class="mt-5 flex flex-wrap items-center gap-4">
                <p class="text-2xl font-semibold tabular-nums">$ {{ number_format($product->price_cop, 0, ',', '.') }} <span class="text-sm">COP</span></p>
                <x-product-stock :stock="$product->stock" />
            </div>

            @if (filled($product->description))
                <p class="mt-5 max-w-prose text-lg text-muted-ink">{{ $product->description }}</p>
            @endif

            <dl class="mt-6 grid gap-3">
                @foreach (['Color' => $product->color, 'Material' => $product->material] as $label => $value)
                    @if (filled($value))
                        <div>
                            <dt class="text-sm font-semibold">{{ $label }}</dt>
                            <dd class="text-muted-ink">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>
    </article>
</x-store-layout>
