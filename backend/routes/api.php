<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExperienceLogController;
use App\Http\Controllers\Api\AchievementController;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'exolune API working!',
    ]);
});

Route::get('/quests', [QuestController::class, 'index']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/quests/accept', [QuestController::class, 'accept']);
    Route::post('/quests/{id}/complete', [QuestController::class, 'complete']);
    Route::get('/my-quests', [QuestController::class, 'myQuests']);

    Route::get('/experience-logs', [ExperienceLogController::class, 'index']);

    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::get('/my-achievements', [AchievementController::class, 'myAchievements']);
});
