<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EntryGateController;
//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/entry-gate', [EntryGateController::class, 'entryGate'])->name('entryGate');
Route::post('/exit-gate', [EntryGateController::class, 'exitGate'])->name('exitGate');
Route::get('/capacity', [EntryGateController::class, 'getCapcity'])->name('capacity');
