<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/students/{nim}/')->group(function () {
    Route::prefix('krs')->group(function () {
        Route::get('current', [App\Http\Controllers\Api\V1\Krs::class, 'currentKrs'])->name('api.v1.krs.current');
        Route::post('courses', [App\Http\Controllers\Api\V1\Krs::class, 'courseRegistration'])->name('api.v1.krs.add-matakuliah');
    });
    Route::prefix('courses')->group(function () {
        Route::get('available', [App\Http\Controllers\Api\V1\Course::class, 'courseAvailable'])->name('api.v1.courses.available');
    });
});