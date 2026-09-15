<?php

namespace App\Actions\Orders;

use App\DTOs\CreateOrderDTO;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $subtotal = 0;
            $taxTotal = 0;
            $itemsToCreate = [];

            // 1. Cargar productos involucrados para calcular precios e impuestos dinámicos
            $productIds = array_map(fn($item) => $item->productId, $dto->items);
            $products = Product::with('ingredients')
                ->where('tenant_id', $dto->tenantId)
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            foreach ($dto->items as $itemDto) {
                $product = $products->get($itemDto->productId);
                if (!$product) {
                    continue;
                }

                $itemSubtotal = $product->price * $itemDto->quantity;
                $itemTax = ($itemSubtotal * ($product->tax_percentage / 100));

                $subtotal += $itemSubtotal;
                $taxTotal += $itemTax;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemDto->quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $itemSubtotal,
                    'notes' => $itemDto->notes,
                    'kitchen_status' => 'pending',
                ];

                // 2. Descuento automático de inventario según la receta
                foreach ($product->ingredients as $ingredient) {
                    $deductAmount = $ingredient->pivot->quantity_required * $itemDto->quantity;
                    Ingredient::where('id', $ingredient->id)->decrement('current_stock', $deductAmount);
                }
            }

            // 3. Generar número consecutivo dinámico de comanda para el inquilino
            $dailyCount = Order::where('tenant_id', $dto->tenantId)
                ->whereDate('created_at', today())
                ->count() + 1;
            $orderNumber = sprintf('ORD-%s-%04d', date('Ymd'), $dailyCount);

            // 4. Crear la Comanda principal
            $order = Order::create([
                'tenant_id' => $dto->tenantId,
                'restaurant_table_id' => $dto->tableId,
                'user_id' => $dto->userId,
                'order_number' => $orderNumber,
                'type' => $dto->type->value,
                'status' => OrderStatus::Pending->value,
                'customer_name' => $dto->customerName,
                'customer_nit_cedula' => $dto->customerNitCedula,
                'customer_email' => $dto->customerEmail,
                'customer_phone' => $dto->customerPhone,
                'delivery_address' => $dto->deliveryAddress,
                'subtotal' => $subtotal,
                'tax' => $taxTotal,
                'total' => $subtotal + $taxTotal,
                'notes' => $dto->notes,
            ]);

            // 5. Registrar las líneas de la comanda
            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            // 6. Actualizar estado de la mesa a Ocupada
            if ($dto->tableId) {
                RestaurantTable::where('id', $dto->tableId)
                    ->update(['status' => TableStatus::Occupied->value]);
            }

            return $order->load(['items.product', 'table']);
        });
    }
}
