<?php

use Illuminate\Support\Facades\Route;
use CarlVallory\KrayinNetValue\Http\Controllers\KrayinNetValueController;

Route::prefix('krayinnetvalue')->group(function () {
    Route::get('', [KrayinNetValueController::class, 'index'])->name('admin.krayinnetvalue.index');
});
