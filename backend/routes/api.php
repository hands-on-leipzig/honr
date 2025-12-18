<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminFirstProgramController;
use App\Http\Controllers\Api\AdminSeasonController;
use App\Http\Controllers\Api\AdminLevelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes Convention
|--------------------------------------------------------------------------
|
| /auth/*              - Authentication (public + authenticated)
| /user                - Current user profile
| /user/*              - Current user actions (password, email)
| /admin/{resource}    - Admin CRUD (RESTful: index, store, update, destroy)
| /admin/{resource}/reorder - Batch reorder for sortable resources
|
*/

// Auth - Public
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/check-nickname', [AuthController::class, 'checkNickname']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth - Authenticated
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User - Current user profile and actions
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'me']);
        Route::put('/', [UserController::class, 'updateProfile']);
        Route::delete('/', [UserController::class, 'destroy']);
        Route::put('/password', [UserController::class, 'updatePassword']);
        Route::post('/email-change', [UserController::class, 'requestEmailChange']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Users
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        // First Programs
        Route::get('/first-programs', [AdminFirstProgramController::class, 'index']);
        Route::post('/first-programs', [AdminFirstProgramController::class, 'store']);
        Route::put('/first-programs/reorder', [AdminFirstProgramController::class, 'reorder']);
        Route::put('/first-programs/{firstProgram}', [AdminFirstProgramController::class, 'update']);
        Route::delete('/first-programs/{firstProgram}', [AdminFirstProgramController::class, 'destroy']);

        // Seasons
        Route::get('/seasons', [AdminSeasonController::class, 'index']);
        Route::post('/seasons', [AdminSeasonController::class, 'store']);
        Route::put('/seasons/{season}', [AdminSeasonController::class, 'update']);
        Route::delete('/seasons/{season}', [AdminSeasonController::class, 'destroy']);

        // Levels (crowdsourced)
        Route::get('/levels', [AdminLevelController::class, 'index']);
        Route::post('/levels', [AdminLevelController::class, 'store']);
        Route::put('/levels/reorder', [AdminLevelController::class, 'reorder']);
        Route::put('/levels/{level}', [AdminLevelController::class, 'update']);
        Route::delete('/levels/{level}', [AdminLevelController::class, 'destroy']);
    });
});



