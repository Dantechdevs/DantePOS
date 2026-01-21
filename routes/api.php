<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\API\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [LoginController::class, 'login']);

// Protected routes (use sanctum, NOT api guard)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'user'    => $request->user()
        ]);
    });

    // Your Users API (protected)
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'viewUsers']);
        Route::get('/{id}', [UsersController::class, 'showUser']);
        Route::post('/', [UsersController::class, 'createUser']);
        Route::put('/{id}', [UsersController::class, 'updateUser']);
        Route::delete('/{id}', [UsersController::class, 'deleteUser']);
    });
});

// Remove this old line — it uses wrong guard!
// Route::middleware('auth:api')->get('/user', ...
