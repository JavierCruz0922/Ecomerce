@props(['title'])

<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} · Papelería KICF</title>
        @if (is_file(public_path('build/manifest.json')) || is_file(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @livewireStyles
    </head>
    <body class="min-h-screen bg-paper text-ink antialiased">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-10 focus:m-3 focus:rounded-md focus:bg-white focus:p-3">Saltar al contenido</a>
        <x-store-navigation />

        <main id="contenido" tabindex="-1" class="mx-auto max-w-7xl px-5 pt-8 pb-14 sm:px-8 sm:pt-12 lg:px-12">
            {{ $slot }}
        </main>

        <footer class="border-t border-paper-line">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 px-5 py-3 sm:px-8 lg:px-12">
                <span class="text-sm font-semibold">Papelería KICF</span>
                <a href="{{ route('images.credits') }}" class="inline-flex min-h-11 items-center rounded text-sm font-semibold text-pine underline underline-offset-4">Créditos y licencias de imágenes</a>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
