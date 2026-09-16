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
Route::post('/orders/{order}/invoice', [OrderController::class, 'emitInvoice']);

// Control de Caja, Turnos y Arqueo Z (POS)
Route::get('/cash-shifts/current', [\App\Http\Controllers\Api\CashShiftController::class, 'current']);
Route::post('/cash-shifts/open', [\App\Http\Controllers\Api\CashShiftController::class, 'open']);
Route::post('/cash-shifts/close', [\App\Http\Controllers\Api\CashShiftController::class, 'close']);

// Administración de Productos, Platos y Recetas (Inventario)
Route::get('/admin/products', [\App\Http\Controllers\Api\AdminProductController::class, 'index']);
Route::post('/admin/products', [\App\Http\Controllers\Api\AdminProductController::class, 'store']);
Route::patch('/admin/products/{product}/toggle', [\App\Http\Controllers\Api\AdminProductController::class, 'toggleAvailability']);
Route::get('/admin/ingredients', [\App\Http\Controllers\Api\AdminProductController::class, 'ingredients']);
