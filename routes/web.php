<?php

use App\Http\Controllers\MergeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MergeController::class, 'index'])->name('merge.index');
Route::post('/merge', [MergeController::class, 'store'])->name('merge.store');
Route::post('/merge/preview', [MergeController::class, 'preview'])->name('merge.preview');
