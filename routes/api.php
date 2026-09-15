<?php

use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Restaurante Big Pollo
|--------------------------------------------------------------------------
| Rutas RESTful modulares para el comanderó de meseros, cocina KDS,
| menú, mesas y cumplimiento legal de protección de datos (Habeas Data).
|--------------------------------------------------------------------------
*/

// Cumplimiento Legal y Transparencia (Ley 1581 / SIC)
Route::get('/legal/privacy-policy', [LegalController::class, 'privacyPolicy']);

// Mesas y Disponibilidad (Comandero Mesero)
Route::get('/tables', [TableController::class, 'index']);

// Menú y Catálogo (Categorías, Productos, Precios y Recetas)
Route::get('/menu', [MenuController::class, 'index']);

// Gestión de Comandas y Pedidos
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
