<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Prueba de Livewire · Papelería KICF</title>
        @if (is_file(public_path('build/manifest.json')) || is_file(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <main class="mx-auto max-w-lg px-6 py-16">
            <h1 class="mb-8 text-3xl font-semibold tracking-tight">Prueba de Livewire</h1>
            <livewire:demo-counter />
        </main>
        @livewireScripts
    </body>
</html>
