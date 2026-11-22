<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo/livewire', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);

    return view('livewire-demo');
})->name('demo.livewire');
