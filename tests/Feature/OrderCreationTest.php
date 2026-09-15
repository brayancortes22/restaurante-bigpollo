<?php

namespace Tests\Feature;

use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\UpdateOrderStatusAction;
use App\DTOs\CreateOrderDTO;
use App\DTOs\OrderItemDTO;
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

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_dynamically_and_deduct_recipe_inventory(): void
    {
        // 1. Arrange: Crear tenant, mesa, categoría, insumos y producto
        $tenant = Tenant::create(['name' => 'Big Pollo Test', 'slug' => 'big-pollo-test']);
        $table = RestaurantTable::create([
            'tenant_id' => $tenant->id,
            'table_number' => 'Mesa 1',
            'status' => TableStatus::Available->value,
        ]);
        $category = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Broaster',
            'slug' => 'broaster',
        ]);

        $ingPollo = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Crudo',
            'unit' => 'kg',
            'current_stock' => 10.0,
            'minimum_stock' => 2.0,
        ]);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Combo Cuarto',
            'price' => 20000.00,
            'tax_percentage' => 8.00, // Impoconsumo
        ]);
        $product->ingredients()->attach($ingPollo->id, ['quantity_required' => 0.500]);

        // 2. Act: Ejecutar CreateOrderAction vía DTO para 2 combos (1.000 kg de pollo a descontar)
        $dto = new CreateOrderDTO(
            tenantId: $tenant->id,
            tableId: $table->id,
            userId: null,
            type: OrderType::DineIn,
            items: [
                new OrderItemDTO(productId: $product->id, quantity: 2, notes: 'Bien frito'),
            ]
        );

        $action = new CreateOrderAction();
        $order = $action->execute($dto);

        // 3. Assert
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 40000.00,
            'tax' => 3200.00, // 8% de 40.000
            'total' => 43200.00,
            'status' => OrderStatus::Pending->value,
        ]);

        // Mesa debe pasar a ocupada
        $this->assertEquals(TableStatus::Occupied->value, $table->fresh()->status);

        // Inventario de pollo debe descontar 1.0kg (10.0 - 1.0 = 9.0)
        $this->assertEquals(9.000, $ingPollo->fresh()->current_stock);

        // 4. Probar transición a Pagado y liberación de mesa
        $updateAction = new UpdateOrderStatusAction();
        $updateAction->execute($order, OrderStatus::Paid);

        $this->assertEquals(OrderStatus::Paid->value, $order->fresh()->status);
        $this->assertEquals(TableStatus::Available->value, $table->fresh()->status);
    }
}
