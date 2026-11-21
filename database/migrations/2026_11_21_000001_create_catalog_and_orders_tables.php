<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        // SQLite permits fractional or text values in integer columns unless checked.
        $integerCheck = static fn (string $column): string => $sqlite ? "typeof($column) = 'integer' and " : '';

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) use ($integerCheck) {
            $table->id();
            $table->foreignId('category_id')->index()->constrained()->restrictOnDelete();
            $table->string('sku', 64)->unique();
            $table->string('name');
            $table->string('color');
            $table->string('material');
            $table->text('description');
            $table->string('image_path');
            $table->rawColumn('price_cop', 'bigint constraint products_price_positive check ('
                .$integerCheck('price_cop').'price_cop > 0)');
            $table->rawColumn('stock', 'integer constraint products_stock_range check ('
                .$integerCheck('stock').'stock between 0 and 2147483647)')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) use ($integerCheck) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->index()->constrained()->restrictOnDelete();
            $table->string('product_sku', 64);
            $table->string('product_name');
            $table->rawColumn('quantity', 'integer constraint order_items_quantity_range check ('
                .$integerCheck('quantity').'quantity between 1 and 2147483647)');
            $table->rawColumn('unit_price_cop', 'bigint constraint order_items_price_positive check ('
                .$integerCheck('unit_price_cop').'unit_price_cop > 0)');
            $table->timestamps();
            $table->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
