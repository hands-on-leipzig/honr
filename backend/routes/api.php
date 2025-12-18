<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminFirstProgramController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/check-nickname', [AuthController::class, 'checkNickname']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'me']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/request-email-change', [UserController::class, 'requestEmailChange']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    Route::delete('/user', [UserController::class, 'destroy']);

    // Admin routes
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy']);

    Route::get('/admin/first-programs', [AdminFirstProgramController::class, 'index']);
    Route::post('/admin/first-programs', [AdminFirstProgramController::class, 'store']);
    Route::put('/admin/first-programs/reorder', [AdminFirstProgramController::class, 'reorder']);
    Route::put('/admin/first-programs/{firstProgram}', [AdminFirstProgramController::class, 'update']);
    Route::delete('/admin/first-programs/{firstProgram}', [AdminFirstProgramController::class, 'destroy']);
});



