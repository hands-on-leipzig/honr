<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminFirstProgramController;
use App\Http\Controllers\Api\AdminSeasonController;
use App\Http\Controllers\Api\AdminLevelController;
use App\Http\Controllers\Api\AdminCountryController;
use App\Http\Controllers\Api\AdminLocationController;
use App\Http\Controllers\Api\AdminRegionalPartnerController;
use App\Http\Controllers\Api\AdminRoleController;
use App\Http\Controllers\Api\AdminEventController;
use App\Http\Controllers\Api\AdminEngagementController;
use App\Http\Controllers\Api\AdminStatisticsController;
use App\Http\Controllers\Api\EngagementController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\HeatmapController;
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
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/verify-email-change', [AuthController::class, 'verifyEmailChange']);
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

    // Users - List and view other users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::get('/{id}/engagements', [UserController::class, 'getUserEngagements']);
        Route::get('/{id}/badges', [BadgeController::class, 'getUserBadges']);
    });

    // Engagements - Current user's engagements
    Route::prefix('engagements')->group(function () {
        Route::get('/', [EngagementController::class, 'index']);
        Route::get('/options', [EngagementController::class, 'options']);
        Route::post('/', [EngagementController::class, 'store']);
        Route::delete('/{engagement}', [EngagementController::class, 'destroy']);
        Route::post('/propose-role', [EngagementController::class, 'proposeRole']);
        Route::post('/propose-event', [EngagementController::class, 'proposeEvent']);
        Route::post('/propose-country', [EngagementController::class, 'proposeCountry']);
        Route::post('/propose-location', [EngagementController::class, 'proposeLocation']);
    });

    // Leaderboards
    Route::prefix('leaderboard')->group(function () {
        Route::get('/options', [LeaderboardController::class, 'options']);
        Route::get('/volunteers', [LeaderboardController::class, 'volunteers']);
        Route::get('/regional-partners', [LeaderboardController::class, 'regionalPartners']);
        Route::get('/coaches', [LeaderboardController::class, 'coaches']);
    });

    // Heatmap
    Route::get('/heatmap', [HeatmapController::class, 'index']);
    Route::get('/heatmap/options', [HeatmapController::class, 'options']);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Users
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        // First Programs
        Route::get('/first-programs', [AdminFirstProgramController::class, 'index']);
        Route::post('/first-programs', [AdminFirstProgramController::class, 'store']);
        Route::put('/first-programs/reorder', [AdminFirstProgramController::class, 'reorder']);
        Route::put('/first-programs/{firstProgram}', [AdminFirstProgramController::class, 'update']);
        Route::delete('/first-programs/{firstProgram}', [AdminFirstProgramController::class, 'destroy']);
        Route::post('/first-programs/{firstProgram}/logo', [AdminFirstProgramController::class, 'uploadLogo']);
        Route::delete('/first-programs/{firstProgram}/logo', [AdminFirstProgramController::class, 'deleteLogo']);

        // Seasons
        Route::get('/seasons', [AdminSeasonController::class, 'index']);
        Route::post('/seasons', [AdminSeasonController::class, 'store']);
        Route::put('/seasons/{season}', [AdminSeasonController::class, 'update']);
        Route::delete('/seasons/{season}', [AdminSeasonController::class, 'destroy']);
        Route::post('/seasons/{season}/logo', [AdminSeasonController::class, 'uploadLogo']);
        Route::delete('/seasons/{season}/logo', [AdminSeasonController::class, 'deleteLogo']);

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

        // Regional Partners
        Route::get('/regional-partners', [AdminRegionalPartnerController::class, 'index']);
        Route::post('/regional-partners', [AdminRegionalPartnerController::class, 'store']);
        Route::put('/regional-partners/{regionalPartner}', [AdminRegionalPartnerController::class, 'update']);
        Route::delete('/regional-partners/{regionalPartner}', [AdminRegionalPartnerController::class, 'destroy']);

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
        Route::post('/roles/{role}/logo', [AdminRoleController::class, 'uploadLogo']);
        Route::delete('/roles/{role}/logo', [AdminRoleController::class, 'deleteLogo']);

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

        // Statistics
        Route::get('/statistics', [AdminStatisticsController::class, 'index']);
    });
});



