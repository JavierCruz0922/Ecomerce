<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('/catalogo', 'catalog.index')->name('catalog.index');
Route::view('/creditos-imagenes', 'catalog.image-credits')->name('images.credits');

Route::get('/catalogo/{product:sku}', function (Product $product) {
    return view('catalog.show', ['product' => $product->loadMissing('category')]);
})->name('catalog.show');

Route::get('/demo/livewire', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);

    return view('livewire-demo');
})->name('demo.livewire');
