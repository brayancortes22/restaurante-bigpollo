<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Product $comboFamiliar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Big Pollo Delivery Test', 'slug' => 'bp-delivery-test']);

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Combos',
            'slug' => 'combos',
        ]);

        $ingPollo = Ingredient::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pollo Crudo',
            'unit' => 'unit',
            'current_stock' => 50,
            'minimum_stock' => 5,
        ]);

        $this->comboFamiliar = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Combo Familiar Asado',
            'price' => 45000,
            'tax_percentage' => 8.0,
            'is_available' => true,
        ]);
        $this->comboFamiliar->ingredients()->attach($ingPollo->id, ['quantity_required' => 1.0]);
    }

    public function test_can_create_delivery_order_via_api(): void
    {
        $payload = [
            'customer_name' => 'Brayan Cortés',
            'customer_phone' => '3101234567',
            'delivery_address' => 'Carrera 5 # 10 - 20, Barrio El Centro',
            'items' => [
                [
                    'product_id' => $this->comboFamiliar->id,
                    'quantity' => 1,
                    'notes' => 'Bien dorado con arepas extras',
                ],
            ],
        ];

        $response = $this->withHeader('X-Tenant-ID', (string) $this->tenant->id)
            ->postJson('/api/deliveries', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('order.customer_name', 'Brayan Cortés');
        $response->assertJsonPath('order.type', OrderType::Delivery->value);
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Brayan Cortés',
            'type' => 'delivery',
            'delivery_address' => 'Carrera 5 # 10 - 20, Barrio El Centro',
        ]);
    }

    public function test_can_track_order_status_timeline_via_api(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'BP-9999',
            'type' => OrderType::Delivery->value,
            'status' => OrderStatus::InKitchen->value,
            'customer_name' => 'Carlos Cliente',
            'delivery_address' => 'Calle 8 # 4 - 12',
            'customer_phone' => '3209876543',
            'subtotal' => 45000,
            'tax' => 3600,
            'total' => 48600,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->comboFamiliar->id,
            'quantity' => 1,
            'unit_price' => 45000,
            'subtotal' => 45000,
        ]);

        $response = $this->getJson("/api/tracking/{$order->order_number}");

        $response->assertStatus(200);
        $response->assertJsonPath('order_number', 'BP-9999');
        $response->assertJsonPath('status', 'in_kitchen');
        $response->assertJsonPath('status_label', 'Preparando en Cocina (Dorado & Broaster)');
    }

    public function test_can_render_tracking_blade_view(): void
    {
        $response = $this->get('/track/BP-9999');

        $response->assertStatus(200);
        $response->assertViewIs('tracking');
        $response->assertSee('BP-9999');
        $response->assertSee('Rastreo de Pedido');
    }
}
