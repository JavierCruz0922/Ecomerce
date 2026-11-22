<div>
    <form wire:submit="applyFilters" aria-label="Filtrar productos" class="rounded-2xl border border-paper-line bg-white p-4 sm:p-6">
        @php($fieldClass = 'min-h-11 w-full min-w-0 rounded-lg border border-control-line bg-paper px-3 text-sm text-ink placeholder:text-muted-ink')
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-[1.4fr_1fr_0.85fr_0.85fr]">
            <div class="col-span-2 sm:col-span-1">
                <label for="catalog-search" class="mb-1.5 block text-sm font-semibold">Nombre o SKU</label>
                <input id="catalog-search" type="search" wire:model="search" maxlength="100" placeholder="Ej. Cuaderno o PAP006"
                    class="{{ $fieldClass }}" @error('search') aria-invalid="true" aria-describedby="search-error" @enderror>
                @error('search') <p id="search-error" role="alert" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label for="catalog-category" class="mb-1.5 block text-sm font-semibold">Categoría</label>
                <select id="catalog-category" wire:model="category" class="{{ $fieldClass }}" @error('category') aria-invalid="true" aria-describedby="category-error" @enderror>
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category') <p id="category-error" role="alert" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>
            @foreach (['minPrice' => 'Precio mínimo (COP)', 'maxPrice' => 'Precio máximo (COP)'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-1.5 block text-sm font-semibold">{{ $label }}</label>
                    <input id="{{ $field }}" type="number" inputmode="numeric" min="0" max="2147483647" step="1"
                        wire:model="{{ $field }}" class="{{ $fieldClass }}" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                    @error($field) <p id="{{ $field }}-error" role="alert" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button type="submit" class="min-h-11 rounded-lg bg-pine px-5 text-sm font-semibold text-white hover:bg-pine-dark">Aplicar filtros</button>
            <button type="button" wire:click="clearFilters" class="min-h-11 rounded-lg px-4 text-sm font-semibold text-pine hover:bg-paper-shade">Limpiar filtros</button>
        </div>
    </form>

    <p role="status" aria-live="polite" class="my-5 text-sm font-semibold text-muted-ink" data-testid="catalog-result">
        {{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }}
    </p>

    @if ($products->isNotEmpty())
        <ul class="grid grid-cols-1 gap-4 min-[540px]:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-label="Productos">
            @foreach ($products as $product)
                <li wire:key="product-{{ $product->id }}" data-testid="catalog-product" class="min-w-0 rounded-2xl border border-paper-line bg-white p-3">
                    @php($detailUrl = route('catalog.show', $product->sku))
                    <article class="flex h-full flex-col">
                        <a href="{{ $detailUrl }}" aria-label="Ver detalle de {{ $product->name }} ({{ $product->sku }})"
                            data-testid="product-visual-link" class="block rounded-xl hover:ring-2 hover:ring-pine">
                            <x-product-image :product="$product" />
                        </a>

                        <div class="flex grow flex-col p-2 pt-4">
                            <p class="text-xs font-semibold text-muted-ink">{{ $product->category->name }} <span aria-hidden="true">·</span> {{ $product->sku }}</p>
                            <h2 class="mt-1 text-lg leading-snug font-semibold">
                                <a href="{{ $detailUrl }}" class="rounded-sm hover:text-pine hover:underline">{{ $product->name }}</a>
                            </h2>
                            @if (filled($product->description))
                                <p class="mt-2 text-sm leading-relaxed text-muted-ink">{{ Str::limit($product->description, 100) }}</p>
                            @endif
                            <div class="mt-auto flex flex-wrap items-center gap-x-3 gap-y-2 pt-4">
                                <p class="font-semibold tabular-nums">$ {{ number_format($product->price_cop, 0, ',', '.') }} <span class="text-xs">COP</span></p>
                                <x-product-stock :stock="$product->stock" />
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">{{ $products->links() }}</div>
    @else
        <p class="rounded-xl border border-paper-line bg-white p-5 text-muted-ink">
            {{ $filters === [] ? 'Aún no hay productos en el catálogo.' : 'No hay productos que coincidan. Cambia la búsqueda o limpia los filtros.' }}
        </p>
    @endif
</div>
