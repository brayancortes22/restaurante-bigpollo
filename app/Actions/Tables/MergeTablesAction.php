<?php

namespace App\Actions\Tables;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MergeTablesAction
{
    /**
     * Une dos mesas trasladando todos los platos de la mesa origen a la mesa destino.
     * Libera la mesa origen y consolida una única cuenta para el grupo familiar.
     */
    public function execute(RestaurantTable $sourceTable, RestaurantTable $targetTable): Order
    {
        return DB::transaction(function () use ($sourceTable, $targetTable) {
            if ($sourceTable->id === $targetTable->id) {
                throw new InvalidArgumentException('No se puede unir una mesa consigo misma.');
            }

            $sourceOrder = Order::where('restaurant_table_id', $sourceTable->id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->latest()
                ->first();

            if (!$sourceOrder) {
                throw new InvalidArgumentException("La mesa {$sourceTable->table_number} no tiene comanda activa para unir.");
            }

            $targetOrder = Order::where('restaurant_table_id', $targetTable->id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->latest()
                ->first();

            if ($targetOrder) {
                // Si la mesa destino ya tiene comanda, migrar todos los ítems de la mesa origen a la destino
                OrderItem::where('order_id', $sourceOrder->id)
                    ->update(['order_id' => $targetOrder->id]);

                // Recalcular orden destino
                $newSubtotal = (float) $targetOrder->items()->sum('subtotal');
                $targetOrder->subtotal = $newSubtotal;
                $targetOrder->tax = round($newSubtotal * 0.08, 2);
                $targetOrder->total = $targetOrder->subtotal + $targetOrder->tax;
                $targetOrder->notes = ($targetOrder->notes ? $targetOrder->notes . ' | ' : '') . "Mesa unida con {$sourceTable->table_number}";
                $targetOrder->save();

                // Cancelar la orden origen con motivo de auditoría
                $sourceOrder->update([
                    'status' => OrderStatus::Cancelled->value,
                    'cancellation_reason' => "Cuenta consolidada y unida a la mesa {$targetTable->table_number}",
                ]);
            } else {
                // Si la mesa destino estaba vacía, simplemente transferir la orden completa
                $sourceOrder->update([
                    'restaurant_table_id' => $targetTable->id,
                    'notes' => ($sourceOrder->notes ? $sourceOrder->notes . ' | ' : '') . "Unida a mesa {$targetTable->table_number}",
                ]);
                $targetOrder = $sourceOrder;
            }

            // Actualizar estados: liberar mesa origen y ocupar mesa destino
            $sourceTable->update(['status' => TableStatus::Available->value]);
            $targetTable->update(['status' => TableStatus::Occupied->value]);

            return $targetOrder->load(['table', 'items.product']);
        });
    }
}
