<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private RestaurantTable $table;
    private Product $product;
    private Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Restaurante Big Pollo Test',
            'slug' => 'big-pollo-test',
            'nit' => '900.999.888-1',
            'email' => 'admin@testpollo.com',
            'address' => 'Calle 100 # 15-20',
            'currency' => 'COP',
            'tax_percentage' => 8.00,
            'suggested_tip_percentage' => 10.00,
            'is_active' => true,
        ]);

        $this->table = RestaurantTable::create([
            'tenant_id' => $this->tenant->id,
            'table_number' => 'Mesa 1',
            'capacity' => 4,
            'status' => TableStatus::Available->value,
        ]);

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Combos Especiales',
            'slug' => 'combos-especiales',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Combo Pollo Crujiente',
            'price' => 35000.00,
            'tax_percentage' => 8.00,
            'is_available' => true,
        ]);

        $this->ingredient = Ingredient::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pollo Fresco Entero',
            'unit' => 'kg',
            'current_stock' => 50.00,
            'minimum_stock' => 10.00,
        ]);

        $this->product->ingredients()->attach($this->ingredient->id, [
            'quantity_required' => 1.5,
        ]);
    }

    public function test_can_get_legal_privacy_policy(): void
    {
        $response = $this->getJson('/api/legal/privacy-policy?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('controller.business_name', 'Restaurante Big Pollo Test')
            ->assertJsonStructure([
                'status',
                'legal_framework' => ['country', 'laws'],
                'controller' => ['business_name', 'nit', 'contact_email', 'address'],
                'purposes',
                'rights_arco',
                'contact_channel',
            ]);
    }

    public function test_can_list_restaurant_tables(): void
    {
        $response = $this->getJson('/api/tables?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.available', 1)
            ->assertJsonPath('data.0.table_number', 'Mesa 1');
    }

    public function test_can_list_menu_catalog(): void
    {
        $response = $this->getJson('/api/menu?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Combos Especiales')
            ->assertJsonPath('data.0.products.0.name', 'Combo Pollo Crujiente');
    }

    public function test_waiter_can_create_order_via_api_and_deduct_inventory(): void
    {
        $payload = [
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table->id,
            'type' => OrderType::DineIn->value,
            'customer_name' => 'Carlos Cliente',
            'customer_nit_cedula' => '1020304050',
            'customer_consent' => true,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Bien dorado',
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', OrderType::DineIn->value)
            ->assertJsonPath('data.status', OrderStatus::Pending->value)
            ->assertJsonPath('data.subtotal', 70000)
            ->assertJsonPath('data.tax', 5600)
            ->assertJsonPath('data.total', 75600)
            ->assertJsonPath('data.customer_name', 'Carlos Cliente');

        // Verificar que la mesa pasó a ocupada
        $this->assertEquals(TableStatus::Occupied->value, $this->table->fresh()->status);

        // Verificar descuento de stock de inventario: 50.00 - (1.5 * 2) = 47.00
        $this->assertEquals(47.00, $this->ingredient->fresh()->current_stock);
    }

    public function test_kds_can_update_order_status_and_release_table_on_payment(): void
    {
        // 1. Crear orden
        $payload = [
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table->id,
            'type' => OrderType::DineIn->value,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $createResponse = $this->postJson('/api/orders', $payload);
        $orderId = $createResponse->json('data.id');

        // 2. Cocina KDS avanza a InKitchen
        $patchResponse = $this->patchJson("/api/orders/{$orderId}/status", [
            'status' => OrderStatus::InKitchen->value,
        ]);

        $patchResponse->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::InKitchen->value);

        // 3. Mesero/Caja marca como Pagado -> La mesa se libera automáticamente
        $paidResponse = $this->patchJson("/api/orders/{$orderId}/status", [
            'status' => OrderStatus::Paid->value,
        ]);

        $paidResponse->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::Paid->value);

        $this->assertEquals(TableStatus::Available->value, $this->table->fresh()->status);
    }
}
