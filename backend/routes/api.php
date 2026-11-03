<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuestController;
use App\Http\Controllers\Api\AuthController;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'exolune API working!',
    ]);
});



Route::get('/quests', [QuestController::class, 'index']);

Route::middleware('auth::sanctum')->group(function () {
    Route::post('/quests/accept', [QuestController::class, 'accept']);
    Route::post('quests/{id}/complete', [QuestController::class, 'complete']);
});



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
