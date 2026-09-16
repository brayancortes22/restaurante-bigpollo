<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Restaurante Big Pollo
|--------------------------------------------------------------------------
| Seguridad RBAC, Autenticación y Vistas de Grado Comercial.
|--------------------------------------------------------------------------
*/

// Rutas Públicas (Cumplimiento Legal y Transparencia Ley 1581)
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Autenticación de Personal (Login & Logout)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas Protegidas por Autenticación y Control de Acceso por Roles (RBAC)
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $user = auth()->user();
        return match ($user?->role) {
            'cocina' => redirect('/kds'),
            'cajero' => redirect('/pos'),
            'admin', 'superadmin' => redirect('/admin/menu'),
            default => redirect('/waiter'),
        };
    });

    // Módulo Mesero / Salonero y Punto de Caja (Mesero, Cajero, Admin y SuperAdmin)
    Route::middleware('role:mesero,cajero')->group(function () {
        Route::get('/waiter', function () {
            return view('waiter');
        })->name('waiter');
    });

    // Módulo Cocina KDS (Cocina, Admin y SuperAdmin)
    Route::middleware('role:cocina')->group(function () {
        Route::get('/kds', function () {
            return view('kds');
        })->name('kds');
    });

    // Módulo Caja POS & Factus DIAN (Cajero, Admin y SuperAdmin)
    Route::middleware('role:cajero')->group(function () {
        Route::get('/pos', function () {
            return view('pos');
        })->name('pos');
    });

    // Módulo Administrador de Menú, Precios y Recetas (Admin y SuperAdmin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/menu', function () {
            return view('admin.menu');
        })->name('admin.menu');
    });
});
