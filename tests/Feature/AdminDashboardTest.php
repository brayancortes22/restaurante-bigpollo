<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $meseroUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Restaurante Big Pollo',
            'slug' => 'big-pollo',
            'nit' => '901.789.456-2',
            'address' => 'Calle Principal # 4 - 20',
            'phone' => '+57 310 123 4567',
            'email' => 'contacto@bigpollo.com',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Brayan',
            'email' => 'admin@bigpollo.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->meseroUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mesero Juan',
            'email' => 'mesero@bigpollo.com',
            'password' => bcrypt('mesero123'),
            'role' => 'mesero',
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_mesero_gets_forbidden_when_accessing_admin_dashboard(): void
    {
        $response = $this->actingAs($this->meseroUser)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_and_views_metrics(): void
    {
        $table = RestaurantTable::create([
            'tenant_id' => $this->tenant->id,
            'table_number' => 'Mesa 1',
            'capacity' => 4,
            'status' => 'occupied',
        ]);

        $cat = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pollo Asado',
            'slug' => 'pollo-asado',
        ]);

        $prod = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $cat->id,
            'name' => 'Pollo Asado Familiar',
            'price' => 42000,
            'tax_percentage' => 8,
            'is_available' => true,
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $table->id,
            'order_number' => 'TEST-DASH-001',
            'customer_name' => 'Cliente Test',
            'type' => 'dine_in',
            'status' => 'paid',
            'subtotal' => 42000,
            'tax' => 3360,
            'total' => 45360,
            'payment_method' => 'cash',
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $prod->id,
            'quantity' => 1,
            'unit_price' => 42000,
            'subtotal' => 42000,
            'kitchen_status' => 'ready',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('metrics');
        $response->assertSee('Panel Ejecutivo del Dueño');
        $response->assertSee('45.360'); // Total sales formatted
        $response->assertSee('Pollo Asado Familiar');
    }
}
