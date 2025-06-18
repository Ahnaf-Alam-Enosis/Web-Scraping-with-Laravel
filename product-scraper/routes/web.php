<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('product');
})->name('product.scrape');

// Route::get('/scrape', [ProductController::class, 'scrape'])->name('scrape');
// Route::get('/scrape-AI', [ProductController::class, 'scrapeByAI'])->name('scrape');
Route::get('/scrape-ollama', [ProductController::class, 'scrapeByOllama'])->name('scrape');
