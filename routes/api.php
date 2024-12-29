<?php


use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/test', function () {
  return response()->json(['message' => 'API routes are working']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
});
