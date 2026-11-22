@props(['product', 'detail' => false])

@php
    $credit = config('catalog_images')[$product->sku] ?? null;
    $assetPath = $credit === null ? null : 'images/catalogo/'.$credit['file'];
@endphp

@if ($credit !== null && $product->image_path === $credit['file'] && is_file(public_path($assetPath)))
    <figure>
        <div @class(['overflow-hidden rounded-xl bg-paper-shade', 'aspect-video' => ! $detail, 'aspect-[4/3]' => $detail])>
            <img src="{{ asset($assetPath) }}" alt="Imagen de {{ $product->name }}"
                class="h-full w-full object-contain p-2"
                @if (! $detail) loading="lazy" @endif>
        </div>
        @if ($detail)
            <figcaption class="mt-2 text-xs leading-relaxed text-muted-ink">
                <a href="{{ $credit['source'] }}" class="underline">{{ $credit['title'] }}</a>
                · {{ $credit['author'] }} · {{ $credit['source_label'] }}
                · <a href="{{ $credit['license_url'] }}" class="underline">{{ $credit['license'] }}</a>
                · <a href="{{ route('images.credits') }}#{{ $product->sku }}" class="underline">Créditos de la imagen</a>
            </figcaption>
        @endif
    </figure>
@else
    <x-product-placeholder :category="$product->category->name" />
@endif
