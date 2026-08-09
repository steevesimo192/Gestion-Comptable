<?php

use App\Http\Controllers\SchemaExplorerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SchemaExplorerController::class, 'index'])->name('schema.index');
Route::get('/api/schema', [SchemaExplorerController::class, 'data'])->name('schema.data');
