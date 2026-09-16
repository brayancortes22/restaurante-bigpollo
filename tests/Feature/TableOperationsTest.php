<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableOperationsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $mesero;
    private RestaurantTable $table1;
    private RestaurantTable $table2;
    private Product $polloBroaster;
    private Product $papas;
    private Ingredient $ingPollo;
    private Ingredient $ingPapas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Big Pollo Ops Test', 'slug' => 'bp-ops-test']);

        $this->mesero = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mesero Test',
            'email' => 'mesero@ops.com',
            'password' => bcrypt('secret123'),
            'role' => 'mesero',
            'is_active' => true,
        ]);

        $this->table1 = RestaurantTable::create([
            'tenant_id' => $this->tenant->id,
            'table_number' => 'Mesa 1',
            'capacity' => 4,
            'status' => TableStatus::Available->value,
        ]);

        $this->table2 = RestaurantTable::create([
            'tenant_id' => $this->tenant->id,
            'table_number' => 'Mesa 2',
            'capacity' => 4,
            'status' => TableStatus::Available->value,
        ]);

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Broaster',
            'slug' => 'broaster',
        ]);

        $this->ingPollo = Ingredient::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pollo Crudo',
            'unit' => 'unit',
            'current_stock' => 50,
            'minimum_stock' => 5,
        ]);

        $this->ingPapas = Ingredient::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Papas Congeladas',
            'unit' => 'portion',
            'current_stock' => 100,
            'minimum_stock' => 10,
        ]);

        $this->polloBroaster = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Medio Broaster',
            'price' => 20000,
            'tax_percentage' => 8.0,
            'is_available' => true,
        ]);
        $this->polloBroaster->ingredients()->attach($this->ingPollo->id, ['quantity_required' => 0.5]);

        $this->papas = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Porción de Papas',
            'price' => 6000,
            'tax_percentage' => 8.0,
            'is_available' => true,
        ]);
        $this->papas->ingredients()->attach($this->ingPapas->id, ['quantity_required' => 1.0]);
    }

    public function test_can_get_active_order_for_occupied_table(): void
    {
        $this->table1->update(['status' => TableStatus::Occupied->value]);
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table1->id,
            'order_number' => 'BP-1001',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 20000,
            'tax' => 1600,
            'total' => 21600,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->polloBroaster->id,
            'quantity' => 1,
            'unit_price' => 20000,
            'subtotal' => 20000,
            'kitchen_status' => 'pending',
        ]);

        $response = $this->actingAs($this->mesero)->getJson("/api/tables/{$this->table1->id}/active-order");

        $response->assertStatus(200);
        $response->assertJsonPath('has_active_order', true);
        $response->assertJsonPath('order.order_number', 'BP-1001');
    }

    public function test_can_add_items_to_active_order_and_deduct_inventory(): void
    {
        $this->table1->update(['status' => TableStatus::Occupied->value]);
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table1->id,
            'order_number' => 'BP-1002',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 20000,
            'tax' => 1600,
            'total' => 21600,
        ]);

        $payload = [
            'items' => [
                [
                    'product_id' => $this->papas->id,
                    'quantity' => 2,
                    'notes' => 'Bien crocantes',
                ],
            ],
        ];

        $response = $this->actingAs($this->mesero)->postJson("/api/tables/{$this->table1->id}/add-items", $payload);

        $response->assertStatus(200);
        // Subtotal: 20000 + (6000 * 2) = 32000
        $this->assertEquals(32000, (float) $response->json('order.subtotal'));

        // Inventario de papas descontado: 100 - (1.0 * 2) = 98
        $this->assertEquals(98, $this->ingPapas->fresh()->current_stock);
    }

    public function test_can_modify_pending_order_item_and_revert_inventory(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table1->id,
            'order_number' => 'BP-1003',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 20000,
            'tax' => 1600,
            'total' => 21600,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->papas->id,
            'quantity' => 1,
            'unit_price' => 6000,
            'subtotal' => 6000,
            'kitchen_status' => 'pending',
        ]);

        // Simular que el stock de papas ya se había descontado a 99
        $this->ingPapas->update(['current_stock' => 99]);

        // Cancelar el ítem
        $response = $this->actingAs($this->mesero)->patchJson("/api/order-items/{$item->id}/modify", [
            'cancel_item' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // El stock de papas debe haberse revertido a 100
        $this->assertEquals(100, $this->ingPapas->fresh()->current_stock);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }

    public function test_can_transfer_order_to_available_table(): void
    {
        $this->table1->update(['status' => TableStatus::Occupied->value]);
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table1->id,
            'order_number' => 'BP-1004',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 20000,
            'tax' => 1600,
            'total' => 21600,
        ]);

        $response = $this->actingAs($this->mesero)->postJson("/api/tables/{$this->table1->id}/transfer", [
            'to_table_id' => $this->table2->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(TableStatus::Available->value, $this->table1->fresh()->status);
        $this->assertEquals(TableStatus::Occupied->value, $this->table2->fresh()->status);
        $this->assertEquals($this->table2->id, $order->fresh()->restaurant_table_id);
    }

    public function test_can_merge_two_tables(): void
    {
        $this->table1->update(['status' => TableStatus::Occupied->value]);
        $this->table2->update(['status' => TableStatus::Occupied->value]);

        $order1 = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table1->id,
            'order_number' => 'BP-1005',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 20000,
            'tax' => 1600,
            'total' => 21600,
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $this->polloBroaster->id,
            'quantity' => 1,
            'unit_price' => 20000,
            'subtotal' => 20000,
        ]);

        $order2 = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table2->id,
            'order_number' => 'BP-1006',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 6000,
            'tax' => 480,
            'total' => 6480,
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $this->papas->id,
            'quantity' => 1,
            'unit_price' => 6000,
            'subtotal' => 6000,
        ]);

        $response = $this->actingAs($this->mesero)->postJson("/api/tables/{$this->table1->id}/merge", [
            'target_table_id' => $this->table2->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(TableStatus::Available->value, $this->table1->fresh()->status);
        $this->assertEquals(TableStatus::Occupied->value, $this->table2->fresh()->status);
        $this->assertEquals(OrderStatus::Cancelled->value, $order1->fresh()->status);
        // La mesa 2 consolida 20000 + 6000 = 26000
        $this->assertEquals(26000, $order2->fresh()->subtotal);
    }

    public function test_can_release_table_and_revert_stock(): void
    {
        $this->table1->update(['status' => TableStatus::Occupied->value]);
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $this->table1->id,
            'order_number' => 'BP-1007',
            'status' => OrderStatus::InKitchen->value,
            'subtotal' => 6000,
            'tax' => 480,
            'total' => 6480,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->papas->id,
            'quantity' => 1,
            'unit_price' => 6000,
            'subtotal' => 6000,
        ]);

        $this->ingPapas->update(['current_stock' => 99]);

        $response = $this->actingAs($this->mesero)->postJson("/api/tables/{$this->table1->id}/release", [
            'reason' => 'Clientes salieron por urgencia',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(TableStatus::Available->value, $this->table1->fresh()->status);
        $this->assertEquals(OrderStatus::Cancelled->value, $order->fresh()->status);
        // Stock de papas restaurado a 100
        $this->assertEquals(100, $this->ingPapas->fresh()->current_stock);
    }
}
