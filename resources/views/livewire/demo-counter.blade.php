<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="demo-counter-title">
    <h2 id="demo-counter-title" class="text-lg font-medium">Contador</h2>
    <output class="mt-3 block text-5xl font-semibold tabular-nums" aria-live="polite" data-testid="demo-count">{{ $count }}</output>

    <div class="mt-6 flex flex-wrap gap-3">
        <button type="button" class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" wire:click="increment" data-testid="demo-increment">
            Incrementar
        </button>
        <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 font-medium hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" wire:click="clearCount" data-testid="demo-reset">
            Reiniciar
        </button>
    </div>
</section>
