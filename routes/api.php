<?php

use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\TableLayoutController;
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

// Operaciones Dinámicas de Salón (Comanda Activa, Adiciones, Transferir, Unir, Liberar)
Route::get('/tables/{table}/active-order', [\App\Http\Controllers\Api\TableOperationController::class, 'activeOrder']);
Route::post('/tables/{table}/add-items', [\App\Http\Controllers\Api\TableOperationController::class, 'addItems']);
Route::post('/tables/{table}/transfer', [\App\Http\Controllers\Api\TableOperationController::class, 'transfer']);
Route::post('/tables/{table}/merge', [\App\Http\Controllers\Api\TableOperationController::class, 'merge']);
Route::post('/tables/{table}/release', [\App\Http\Controllers\Api\TableOperationController::class, 'release']);
Route::patch('/order-items/{item}/modify', [\App\Http\Controllers\Api\TableOperationController::class, 'modifyItem']);

// Módulo de Domicilios y Rastreo Público
Route::get('/deliveries', [\App\Http\Controllers\Api\DeliveryController::class, 'index']);
Route::post('/deliveries', [\App\Http\Controllers\Api\DeliveryController::class, 'store']);
Route::get('/tracking/{order_number}', [\App\Http\Controllers\Api\DeliveryController::class, 'track']);

// ── Editor de Plano del Salón (Admin) ──────────────────────────────────────
// Guardar distribución de coordenadas del mapa (POST /api/tables/layout)
Route::post('/tables/layout', [\App\Http\Controllers\Api\TableLayoutController::class, 'updateLayout']);
// Restaurar distribución original del plano Big Pollo
Route::post('/tables/layout/reset', [\App\Http\Controllers\Api\TableLayoutController::class, 'resetToDefault']);
// CRUD de mesas desde el editor admin
Route::post('/admin/tables', [\App\Http\Controllers\Api\TableLayoutController::class, 'storeTable']);
Route::delete('/admin/tables/{table}', [\App\Http\Controllers\Api\TableLayoutController::class, 'destroyTable']);
