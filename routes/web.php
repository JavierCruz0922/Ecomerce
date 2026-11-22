<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/demo/livewire', 'livewire-demo')->name('demo.livewire');
