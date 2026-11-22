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

    public $category = '';

    public $minPrice = '';

    public $maxPrice = '';

    #[Locked]
    public array $filters = [];

    public function applyFilters(): void
    {
        $validated = $this->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['bail', 'nullable', 'integer', 'min:1', Rule::exists(Category::class, 'id')],
            'minPrice' => ['bail', 'nullable', 'integer', 'min:0', 'max:2147483647'],
            'maxPrice' => ['bail', 'nullable', 'integer', 'min:0', 'max:2147483647', Rule::when(filled($this->minPrice), 'gte:minPrice')],
        ], [
            'search.string' => 'Escribe un nombre o SKU válido.',
            'search.max' => 'Usa hasta 100 caracteres.',
            'category.integer' => 'Selecciona una categoría de la lista.',
            'category.min' => 'Selecciona una categoría de la lista.',
            'category.exists' => 'Selecciona una categoría de la lista.',
            'minPrice.integer' => 'Introduce un valor entero en COP.',
            'maxPrice.integer' => 'Introduce un valor entero en COP.',
            'minPrice.min' => 'El precio mínimo debe ser mayor o igual a cero.',
            'maxPrice.min' => 'El precio máximo debe ser mayor o igual a cero.',
            'minPrice.max' => 'El precio mínimo supera el límite permitido.',
            'maxPrice.max' => 'El precio máximo supera el límite permitido.',
            'maxPrice.gte' => 'El precio máximo debe ser mayor o igual al mínimo.',
        ]);

        $this->filters = array_filter([
            'search' => trim($validated['search'] ?? ''),
            'category_id' => $validated['category'] ?? null,
            'min_price' => $validated['minPrice'] ?? null,
            'max_price' => $validated['maxPrice'] ?? null,
        ], static fn ($value): bool => filled($value));

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'minPrice', 'maxPrice', 'filters');
        $this->resetValidation();
        $this->resetPage();
    }

    public function render(): View
    {
        $products = Product::query()->with('category:id,name')->orderBy('sku');

        if (isset($this->filters['search'])) {
            $pattern = '%'.$this->filters['search'].'%';

            $products->where(fn (Builder $query) => $query
                ->whereLike('name', $pattern)
                ->orWhereLike('sku', $pattern));
        }

        foreach (['category_id' => ['category_id', '='], 'min_price' => ['price_cop', '>='], 'max_price' => ['price_cop', '<=']] as $filter => [$column, $operator]) {
            if (isset($this->filters[$filter])) {
                $products->where($column, $operator, $this->filters[$filter]);
            }
        }

        return view('livewire.product-catalog', [
            'products' => $products->paginate(20),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
