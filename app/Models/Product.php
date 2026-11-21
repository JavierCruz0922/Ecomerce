<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'sku', 'name', 'color', 'material', 'description', 'image_path', 'price_cop', 'stock'])]
class Product extends Model
{
    use SoftDeletes;

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'price_cop' => 'integer',
            'stock' => 'integer',
        ];
    }
}
