<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRbacTest extends TestCase
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
            'name' => 'Big Pollo Auth Test',
            'slug' => 'bp-auth-test',
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Brayan Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->mesero = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Carlos Mesero',
            'email' => 'mesero@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'mesero',
            'is_active' => true,
        ]);

        $this->cocina = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mario Cocinero',
            'email' => 'cocina@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'cocina',
            'is_active' => true,
        ]);

        $this->cajero = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Diana Cajera',
            'email' => 'caja@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'cajero',
            'is_active' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login_when_accessing_protected_routes(): void
    {
        $this->get('/waiter')->assertRedirect('/login');
        $this->get('/kds')->assertRedirect('/login');
        $this->get('/pos')->assertRedirect('/login');
        $this->get('/admin/menu')->assertRedirect('/login');
    }

    public function test_privacy_page_remains_publicly_accessible_without_authentication(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);
        $response->assertSee('Ley 1581 de 2012');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/menu');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_mesero_cannot_access_pos_or_admin_menu(): void
    {
        $this->actingAs($this->mesero);

        // Mesero puede entrar al comandero
        $this->get('/waiter')->assertStatus(200);

        // Mesero NO puede entrar a caja ni administración de menú
        $this->get('/pos')->assertStatus(403);
        $this->get('/admin/menu')->assertStatus(403);
    }

    public function test_cocina_cannot_access_pos_or_waiter(): void
    {
        $this->actingAs($this->cocina);

        // Cocinero puede ver la pantalla KDS
        $this->get('/kds')->assertStatus(200);

        // Cocinero NO puede cobrar ni tomar comandas de mesa
        $this->get('/pos')->assertStatus(403);
        $this->get('/waiter')->assertStatus(403);
    }

    public function test_cajero_can_access_pos_and_waiter_but_not_admin_menu(): void
    {
        $this->actingAs($this->cajero);

        // Cajero puede cobrar en POS y ver comandero de mesas
        $this->get('/pos')->assertStatus(200);
        $this->get('/waiter')->assertStatus(200);

        // Cajero NO puede alterar el menú ni recetas
        $this->get('/admin/menu')->assertStatus(403);
    }

    public function test_admin_has_full_access_to_all_modules(): void
    {
        $this->actingAs($this->admin);

        $this->get('/waiter')->assertStatus(200);
        $this->get('/kds')->assertStatus(200);
        $this->get('/pos')->assertStatus(200);
        $this->get('/admin/menu')->assertStatus(200);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
