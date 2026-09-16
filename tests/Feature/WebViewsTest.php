<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebViewsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private User $mesero;
    private User $cocina;
    private User $cajero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Big Pollo Views Test',
            'slug' => 'bp-views-test',
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@viewstest.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->mesero = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mesero User',
            'email' => 'mesero@viewstest.com',
            'password' => bcrypt('secret123'),
            'role' => 'mesero',
            'is_active' => true,
        ]);

        $this->cocina = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cocina User',
            'email' => 'cocina@viewstest.com',
            'password' => bcrypt('secret123'),
            'role' => 'cocina',
            'is_active' => true,
        ]);

        $this->cajero = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cajero User',
            'email' => 'cajero@viewstest.com',
            'password' => bcrypt('secret123'),
            'role' => 'cajero',
            'is_active' => true,
        ]);
    }

    public function test_root_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertSee('Acceso de Personal')
            ->assertSee('Acceso Rápido por Perfil');
    }

    public function test_waiter_screen_renders_for_authenticated_mesero(): void
    {
        $response = $this->actingAs($this->mesero)->get('/waiter');
        $response->assertStatus(200)
            ->assertSee('Comandero Mesero')
            ->assertSee('Selecciona Mesa del Salón');
    }

    public function test_kds_screen_renders_for_authenticated_cocina(): void
    {
        $response = $this->actingAs($this->cocina)->get('/kds');
        $response->assertStatus(200)
            ->assertSee('Cocina KDS en Tiempo Real')
            ->assertSee('comandas activas');
    }

    public function test_pos_screen_renders_for_authenticated_cajero(): void
    {
        $response = $this->actingAs($this->cajero)->get('/pos');
        $response->assertStatus(200)
            ->assertSee('Turno de Caja POS')
            ->assertSee('Comandas Pendientes de Pago');
    }

    public function test_admin_menu_screen_renders_for_authenticated_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/menu');
        $response->assertStatus(200)
            ->assertSee('Catálogo')
            ->assertSee('Recetario Big Pollo');
    }

    public function test_privacy_screen_renders_publicly(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200)
            ->assertSee('Ley 1581 de 2012')
            ->assertSee('Derechos del Titular (Derechos ARCO)');
    }
}
