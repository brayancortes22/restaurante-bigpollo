<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebViewsTest extends TestCase
{
    public function test_root_redirects_to_waiter_module(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/waiter');
    }

    public function test_waiter_screen_renders_successfully(): void
    {
        $response = $this->get('/waiter');
        $response->assertStatus(200)
            ->assertSee('Comandero Mesero')
            ->assertSee('Selecciona Mesa del Salón');
    }

    public function test_kds_screen_renders_successfully(): void
    {
        $response = $this->get('/kds');
        $response->assertStatus(200)
            ->assertSee('Cocina KDS en Tiempo Real')
            ->assertSee('comandas activas');
    }

    public function test_pos_screen_renders_successfully(): void
    {
        $response = $this->get('/pos');
        $response->assertStatus(200)
            ->assertSee('Turno de Caja POS')
            ->assertSee('Comandas Pendientes de Pago');
    }

    public function test_privacy_screen_renders_successfully(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200)
            ->assertSee('Ley 1581 de 2012')
            ->assertSee('Derechos del Titular (Derechos ARCO)');
    }

    public function test_admin_menu_screen_renders_successfully(): void
    {
        $response = $this->get('/admin/menu');
        $response->assertStatus(200)
            ->assertSee('Catálogo')
            ->assertSee('Recetario Big Pollo');
    }
}
