<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Room routes
    Route::apiResource('rooms', RoomController::class);
    Route::post('/rooms/{room}/join', [RoomController::class, 'join']);
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave']);
    Route::get('/rooms/{room}/members', [RoomController::class, 'members']);

    // Message routes
    Route::apiResource('messages', MessageController::class);
    Route::get('/rooms/{room}/messages', [MessageController::class, 'roomMessages']);
    Route::post('/rooms/{room}/system-message', [MessageController::class, 'sendSystemMessage']);

    // User routes
    Route::apiResource('users', UserController::class);
    Route::get('/users/{user}/rooms', [UserController::class, 'rooms']);
    Route::get('/users/{user}/messages', [UserController::class, 'messages']);
    Route::get('/users/{user}/statistics', [UserController::class, 'statistics']);
    Route::get('/online-users', [UserController::class, 'onlineUsers']);
});

// Admin only routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Additional admin routes can be added here
}); 