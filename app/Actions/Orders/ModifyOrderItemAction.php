<?php

namespace App\Actions\Orders;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ModifyOrderItemAction
{
    /**
     * Modifica o cancela un plato de una comanda abierta.
     * Si el plato aún no se cocina ('pending'), revierte el inventario y realiza el cambio.
     */
    public function execute(
        OrderItem $orderItem,
        ?int $newProductId = null,
        ?int $newQuantity = null,
        bool $cancelItem = false,
        bool $force = false
    ): array {
        return DB::transaction(function () use ($orderItem, $newProductId, $newQuantity, $cancelItem, $force) {
            // Protección contra mermas: Si ya se cocina o está listo, no se puede cambiar sin forzado administrativo
            if ($orderItem->kitchen_status !== 'pending' && !$force) {
                return [
                    'success' => false,
                    'message' => 'El plato ya está en cocción o servido. No se puede modificar sin autorización de supervisor.',
                    'kitchen_status' => $orderItem->kitchen_status,
                ];
            }

            $order = $orderItem->order;

            // 1. Revertir insumos de inventario del plato anterior
            $oldProduct = Product::with('ingredients')->findOrFail($orderItem->product_id);
            foreach ($oldProduct->ingredients as $ingredient) {
                $revertQty = $ingredient->pivot->quantity_required * $orderItem->quantity;
                $ingredient->increment('current_stock', $revertQty);
            }

            if ($cancelItem) {
                // Cancelar y remover el ítem
                $orderItem->delete();
            } else {
                // Sustituir por nuevo producto o cantidad
                $targetProductId = $newProductId ?? $orderItem->product_id;
                $newProduct = Product::with('ingredients')->findOrFail($targetProductId);
                $targetQuantity = max(1, $newQuantity ?? $orderItem->quantity);

                // Descontar inventario del nuevo producto
                foreach ($newProduct->ingredients as $ingredient) {
                    $deductQty = $ingredient->pivot->quantity_required * $targetQuantity;
                    $ingredient->decrement('current_stock', $deductQty);
                }

                $orderItem->update([
                    'product_id' => $newProduct->id,
                    'quantity' => $targetQuantity,
                    'unit_price' => $newProduct->price,
                    'subtotal' => $newProduct->price * $targetQuantity,
                    'kitchen_status' => 'pending',
                ]);
            }

            // 2. Recalcular la orden completa a partir de los ítems vigentes
            $newSubtotal = (float) $order->items()->sum('subtotal');
            $order->subtotal = $newSubtotal;
            $order->tax = round($newSubtotal * 0.08, 2);
            $order->total = $order->subtotal + $order->tax;
            $order->save();

            return [
                'success' => true,
                'message' => $cancelItem ? 'Plato cancelado y stock de inventario restaurado.' : 'Plato modificado con éxito.',
                'order' => $order->load(['table', 'items.product']),
            ];
        });
    }
}
