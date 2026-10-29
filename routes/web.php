<?php

use App\Http\Controllers\UcsController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/', [UcsController::class, 'index'])->name('ucs');
