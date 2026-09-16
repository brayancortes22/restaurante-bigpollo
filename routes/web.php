<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Restaurante Big Pollo
|--------------------------------------------------------------------------
| Vistas interactivas de grado comercial con diseno UI/UX elite:
| Comandero Mesero, Pantalla Cocina KDS, Caja & Facturacion DIAN, y Portal Legal.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/waiter');
});

Route::get('/waiter', function () {
    return view('waiter');
});

Route::get('/kds', function () {
    return view('kds');
});

Route::get('/pos', function () {
    return view('pos');
});

Route::get('/privacy', function () {
    return view('privacy');
});

Route::get('/admin/menu', function () {
    return view('admin.menu');
});
