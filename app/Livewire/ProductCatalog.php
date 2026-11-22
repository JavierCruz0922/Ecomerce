<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public $search = '';

    public $searchMode = 'partial';

    public $category = '';

    public $color = '';

    public $material = '';

    public $minPrice = '';

    public $maxPrice = '';

    #[Locked]
    public array $filters = [];

    public function applyFilters(): void
    {
        $validated = $this->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'searchMode' => ['required', 'string', Rule::in(['partial', 'exact'])],
            'category' => ['bail', 'nullable', 'integer', 'min:1', Rule::exists(Category::class, 'id')],
            'color' => ['bail', 'nullable', 'string', 'max:255', Rule::exists(Product::class, 'color')->withoutTrashed()],
            'material' => ['bail', 'nullable', 'string', 'max:255', Rule::exists(Product::class, 'material')->withoutTrashed()],
            'minPrice' => ['bail', 'nullable', 'integer', 'min:0', 'max:2147483647'],
            'maxPrice' => ['bail', 'nullable', 'integer', 'min:0', 'max:2147483647', Rule::when(filled($this->minPrice), 'gte:minPrice')],
        ], [
            'search.string' => 'Escribe un nombre o SKU válido.',
            'search.max' => 'Usa hasta 100 caracteres.',
            'searchMode.*' => 'Selecciona una búsqueda parcial o exacta.',
            'category.integer' => 'Selecciona una categoría de la lista.',
            'category.min' => 'Selecciona una categoría de la lista.',
            'category.exists' => 'Selecciona una categoría de la lista.',
            'color.*' => 'Selecciona un color de la lista.',
            'material.*' => 'Selecciona un material de la lista.',
            'minPrice.integer' => 'Introduce un valor entero en COP.',
            'maxPrice.integer' => 'Introduce un valor entero en COP.',
            'minPrice.min' => 'El precio mínimo debe ser mayor o igual a cero.',
            'maxPrice.min' => 'El precio máximo debe ser mayor o igual a cero.',
            'minPrice.max' => 'El precio mínimo supera el límite permitido.',
            'maxPrice.max' => 'El precio máximo supera el límite permitido.',
            'maxPrice.gte' => 'El precio máximo debe ser mayor o igual al mínimo.',
        ]);

        $search = trim($validated['search'] ?? '');
        $this->filters = array_filter([
            'search' => $search,
            'search_mode' => $search === '' ? null : $validated['searchMode'],
            'category_id' => $validated['category'] ?? null,
            'color' => $validated['color'] ?? null,
            'material' => $validated['material'] ?? null,
            'min_price' => $validated['minPrice'] ?? null,
            'max_price' => $validated['maxPrice'] ?? null,
        ], static fn ($value): bool => filled($value));

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'searchMode', 'category', 'color', 'material', 'minPrice', 'maxPrice', 'filters');
        $this->resetValidation();
        $this->resetPage();
    }

    public function render(): View
    {
        $products = Product::query()->with('category:id,name')->orderBy('sku');

        if (isset($this->filters['search'])) {
            $search = $this->filters['search'];
            if ($this->filters['search_mode'] === 'exact') {
                $products->where(fn (Builder $query) => $query
                    ->whereRaw('LOWER(name) = LOWER(?)', [$search])
                    ->orWhereRaw('LOWER(sku) = LOWER(?)', [$search]));
            } else {
                // Using ! as the escape character leaves backslashes literal on both engines.
                $pattern = '%'.strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
                $products->where(fn (Builder $query) => $query
                    ->whereRaw("LOWER(name) LIKE LOWER(?) ESCAPE '!'", [$pattern])
                    ->orWhereRaw("LOWER(sku) LIKE LOWER(?) ESCAPE '!'", [$pattern]));
            }
        }

        foreach ([
            'category_id' => ['category_id', '='],
            'color' => ['color', '='],
            'material' => ['material', '='],
            'min_price' => ['price_cop', '>='],
            'max_price' => ['price_cop', '<='],
        ] as $filter => [$column, $operator]) {
            if (isset($this->filters[$filter])) {
                $products->where($column, $operator, $this->filters[$filter]);
            }
        }

        return view('livewire.product-catalog', [
            'products' => $products->paginate(20),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'colors' => Product::query()->where('color', '<>', '')->distinct()->orderBy('color')->pluck('color'),
            'materials' => Product::query()->where('material', '<>', '')->distinct()->orderBy('material')->pluck('material'),
        ]);
    }
}
