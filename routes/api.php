<?php

use App\Http\Controllers\Api\v1\SectionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('sections/{section}', SectionController::class);
});
