<?php

namespace App\Actions\Tables;

use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransferTableAction
{
    /**
     * Traslada una comanda activa de una mesa a otra mesa libre.
     * Libera la mesa origen y ocupa la mesa destino.
     */
    public function execute(RestaurantTable $fromTable, RestaurantTable $toTable): Order
    {
        return DB::transaction(function () use ($fromTable, $toTable) {
            if ($fromTable->id === $toTable->id) {
                throw new InvalidArgumentException('La mesa origen y destino no pueden ser la misma.');
            }

            if ($toTable->status !== TableStatus::Available->value) {
                throw new InvalidArgumentException("La mesa de destino ({$toTable->table_number}) ya se encuentra ocupada.");
            }

            $order = Order::where('restaurant_table_id', $fromTable->id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->latest()
                ->first();

            if (!$order) {
                throw new InvalidArgumentException("La mesa origen ({$fromTable->table_number}) no tiene ninguna comanda activa para transferir.");
            }

            // Transferir la comanda a la nueva mesa
            $order->restaurant_table_id = $toTable->id;
            $order->notes = ($order->notes ? $order->notes . ' | ' : '') . "Trasladado de {$fromTable->table_number} a {$toTable->table_number}";
            $order->save();

            // Actualizar estados de mesas
            $fromTable->update(['status' => TableStatus::Available->value]);
            $toTable->update(['status' => TableStatus::Occupied->value]);

            return $order->load(['table', 'items.product']);
        });
    }
}
