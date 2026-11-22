@props(['category'])

<div class="catalog-placeholder {{ match ($category) {
    'Escritura' => 'catalog-placeholder--writing',
    'Papel' => 'catalog-placeholder--paper',
    'Archivo' => 'catalog-placeholder--filing',
    default => 'catalog-placeholder--office',
} }}">
    <span class="relative z-10 rounded bg-white/90 px-2 py-1 text-xs font-medium text-ink">Imagen provisional</span>
</div>
