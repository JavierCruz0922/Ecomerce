@props(['stock'])

@if ($stock > 0)
    <p class="rounded-full bg-[#e4eee1] px-2.5 py-1 text-xs font-semibold text-pine-dark">Disponible · {{ $stock }} uds.</p>
@else
    <p class="rounded-full bg-[#f7e8db] px-2.5 py-1 text-xs font-semibold text-[#783e29]">Agotado</p>
@endif
