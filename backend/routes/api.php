<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminFirstProgramController;
use App\Http\Controllers\Api\AdminSeasonController;
use App\Http\Controllers\Api\AdminLevelController;
use App\Http\Controllers\Api\AdminCountryController;
use App\Http\Controllers\Api\AdminLocationController;
use App\Http\Controllers\Api\AdminRoleController;
use App\Http\Controllers\Api\AdminEventController;
use App\Http\Controllers\Api\AdminEngagementController;
use App\Http\Controllers\Api\AdminBadgeController;
use App\Http\Controllers\Api\AdminEarnedBadgeController;
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

        // Countries (crowdsourced)
        Route::get('/countries', [AdminCountryController::class, 'index']);
        Route::post('/countries', [AdminCountryController::class, 'store']);
        Route::put('/countries/{country}', [AdminCountryController::class, 'update']);
        Route::delete('/countries/{country}', [AdminCountryController::class, 'destroy']);

        // Locations (crowdsourced)
        Route::get('/locations', [AdminLocationController::class, 'index']);
        Route::get('/locations/countries', [AdminLocationController::class, 'countries']);
        Route::post('/locations', [AdminLocationController::class, 'store']);
        Route::put('/locations/{location}', [AdminLocationController::class, 'update']);
        Route::delete('/locations/{location}', [AdminLocationController::class, 'destroy']);

        // Roles (crowdsourced)
        Route::get('/roles', [AdminRoleController::class, 'index']);
        Route::get('/roles/programs', [AdminRoleController::class, 'programs']);
        Route::post('/roles', [AdminRoleController::class, 'store']);
        Route::put('/roles/reorder', [AdminRoleController::class, 'reorder']);
        Route::put('/roles/{role}', [AdminRoleController::class, 'update']);
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy']);

        // Events (crowdsourced)
        Route::get('/events', [AdminEventController::class, 'index']);
        Route::get('/events/options', [AdminEventController::class, 'options']);
        Route::post('/events', [AdminEventController::class, 'store']);
        Route::put('/events/{event}', [AdminEventController::class, 'update']);
        Route::delete('/events/{event}', [AdminEventController::class, 'destroy']);

        // Engagements
        Route::get('/engagements', [AdminEngagementController::class, 'index']);
        Route::get('/engagements/options', [AdminEngagementController::class, 'options']);
        Route::post('/engagements', [AdminEngagementController::class, 'store']);
        Route::put('/engagements/{engagement}', [AdminEngagementController::class, 'update']);
        Route::delete('/engagements/{engagement}', [AdminEngagementController::class, 'destroy']);

        // Badges
        Route::get('/badges', [AdminBadgeController::class, 'index']);
        Route::get('/badges/options', [AdminBadgeController::class, 'options']);
        Route::post('/badges', [AdminBadgeController::class, 'store']);
        Route::put('/badges/{badge}', [AdminBadgeController::class, 'update']);
        Route::delete('/badges/{badge}', [AdminBadgeController::class, 'destroy']);

        // Earned Badges (read-only)
        Route::get('/earned-badges', [AdminEarnedBadgeController::class, 'index']);
    });
});



