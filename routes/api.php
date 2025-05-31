<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');;
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->get('/users', [UserController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')
    ->get('/medicines', [MedicineController::class, 'index']);

Route::middleware(['auth:sanctum', 'permission:medicine.create'])->group(function () {
    Route::post('/medicines', [MedicineController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'permission:medicine.update'])->group(function () {
    Route::put('/medicines/{medicine}', [MedicineController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'permission:medicine.delete'])->group(function () {
    Route::delete('/medicines/{medicine}', [MedicineController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'permission:medicine.update'])->group(function () {
    Route::post('/medicines/{medicine}/images', [MedicineController::class, 'uploadImages']);
});

Route::middleware(['auth:sanctum', 'permission:medicine.export'])->group(function () {
    Route::get('/medicines/export', [MedicineController::class, 'export']);
});

Route::middleware(['auth:sanctum', 'permission:stock.in'])->group(function () {
    Route::post('/medicines/{medicine}/stock-in', [StockController::class, 'stockIn']);
});

Route::middleware(['auth:sanctum', 'permission:stock.out'])->group(function () {
    Route::post('/medicines/{medicine}/stock-out', [StockController::class, 'stockOut']);
});

Route::middleware(['auth:sanctum', 'permission:medicine.view'])->group(function () {
    Route::get('/medicines/{medicine}/history', [StockController::class, 'history']);
    Route::get('/medicines/{medicine}/history/export', [StockController::class, 'exportHistory']);
});

Route::get('/medicines/{medicine}', [MedicineController::class, 'show'])
    ->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->group(function () {
    Route::get('/users/{user}', [UserController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->group(function () {
    Route::put('/users/{user}', [UserController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->group(function () {
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/users/me/password', [UserController::class, 'changeOwnPassword']);
});

Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->group(function () {
    Route::put('/users/{user}/password', [UserController::class, 'updatePassword']);
});

Route::delete('/medicines/images/{image}', [MedicineController::class, 'deleteImage'])
    ->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->get('/dashboard/summary', [DashboardController::class, 'summary']);
Route::middleware(['auth:sanctum'])->get('/dashboard/stock-chart', [DashboardController::class, 'stockChart']);
Route::middleware(['auth:sanctum'])->get('/dashboard/stock-chart/export', [DashboardController::class, 'exportStockChart']);

Route::middleware('auth:sanctum')->get('/medicines/images/{image}', [MedicineController::class, 'viewImage']);

Route::middleware(['auth:sanctum'])->get('/stock-histories', [StockController::class, 'indexAll']);
Route::get('/stock-histories/export', [StockController::class, 'exportAllHistory']);
