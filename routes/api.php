<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return "Hola Mundo";
});

// Autenticacion

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);


    // CRUD Usuarios protegido
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // CRUD Sucursales
    Route::get('/sucursales', [SucursalController::class, 'index']);
    Route::get('/sucursales/{id}', [SucursalController::class, 'show']);
    Route::post('/sucursales', [SucursalController::class, 'store']);
    Route::put('/sucursales/{id}', [SucursalController::class, 'update']);
    Route::delete('/sucursales/{id}', [SucursalController::class, 'destroy']);
    Route::get('/sucursales-buscar', [SucursalController::class, 'buscar']);

    // CRUD Categorías
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);
    Route::get('/categorias-buscar', [CategoriaController::class, 'buscar']);
});
