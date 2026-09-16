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

// Rutas Públicas (Cumplimiento Legal y Transparencia Ley 1581 & Rastreo de Domicilios)
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/track/{order_number}', function ($order_number) {
    return view('tracking', ['order_number' => $order_number]);
})->name('order.track');

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
            'admin', 'superadmin' => redirect('/admin/dashboard'),
            default => redirect('/waiter'),
        };
    });

    // Módulo Mesero / Salonero y Punto de Caja (Mesero, Cajero, Admin y SuperAdmin)
    Route::middleware('role:mesero,cajero,admin,superadmin')->group(function () {
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

    // Módulo Administrador: Dashboard Ejecutivo, Menú y Editor de Plano (Admin y SuperAdmin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/menu', function () {
            return view('admin.menu');
        })->name('admin.menu');
        Route::get('/admin/floor-plan', function () {
            return view('admin.floor_plan');
        })->name('admin.floor-plan');
    });
});
