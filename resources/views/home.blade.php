<x-store-layout title="Inicio">
    <section class="grid gap-8 sm:gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center" aria-labelledby="home-title">
        <div class="py-5 sm:py-9">
            <h1 id="home-title" class="max-w-xl text-5xl leading-[1.04] font-semibold tracking-[-0.06em] sm:text-6xl lg:text-7xl">Papelería para todos los días</h1>
            <p class="mt-5 max-w-lg text-lg leading-relaxed text-muted-ink">Encuentra artículos de escritura, papel, archivo y oficina. Consulta sus precios y existencias antes de elegir.</p>
            <a href="{{ route('catalog.index') }}" class="mt-7 inline-flex min-h-12 items-center rounded-lg bg-pine px-5 font-semibold text-white hover:bg-pine-dark">Explorar catálogo <span aria-hidden="true" class="ml-4">→</span></a>
        </div>

        <div class="home-paper-art rounded-3xl" aria-hidden="true">
            <span></span>
        </div>
    </section>
</x-store-layout>
