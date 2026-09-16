<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AddItemsToOrderAction
{
    /**
     * Adiciona nuevos platos o bebidas a una comanda ya abierta.
     * Descuenta únicamente los insumos de los nuevos ítems de la receta.
     */
    public function execute(Order $order, array $items): Order
    {
        return DB::transaction(function () use ($order, $items) {
            $addedSubtotal = 0;

            foreach ($items as $itemData) {
                $product = Product::with('ingredients')->findOrFail($itemData['product_id']);
                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
                $lineSubtotal = $product->price * $quantity;
                $addedSubtotal += $lineSubtotal;

                // 1. Crear el ítem con kitchen_status = 'pending' (para que cocina sepa que es nuevo)
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $lineSubtotal,
                    'notes' => $itemData['notes'] ?? 'Adición a la mesa',
                    'kitchen_status' => 'pending',
                ]);

                // 2. Descontar insumos de inventario correspondientes a la adición
                foreach ($product->ingredients as $ingredient) {
                    $requiredQty = $ingredient->pivot->quantity_required * $quantity;
                    $ingredient->decrement('current_stock', $requiredQty);
                }
            }

            // 3. Recalcular subtotal, impuesto (8% Impoconsumo) y total de la comanda
            $order->subtotal += $addedSubtotal;
            $order->tax = round($order->subtotal * 0.08, 2);
            $order->total = $order->subtotal + $order->tax;

            // Si la orden estaba en 'ready' o 'served', al agregar platos nuevos vuelve a 'in_kitchen'
            if (in_array($order->status, ['ready', 'served'], true)) {
                $order->status = 'in_kitchen';
            }

            $order->save();

            return $order->load(['table', 'items.product', 'user']);
        });
    }
}
