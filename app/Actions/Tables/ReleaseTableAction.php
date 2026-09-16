<?php

namespace App\Actions\Tables;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class ReleaseTableAction
{
    /**
     * Libera una mesa forzosamente o cancela una orden activa de salón.
     * Revierte los insumos de inventario si los ítems no habían sido servidos.
     */
    public function execute(RestaurantTable $table, ?string $reason = null, ?int $cancelledByUserId = null): RestaurantTable
    {
        return DB::transaction(function () use ($table, $reason, $cancelledByUserId) {
            $activeOrder = Order::where('restaurant_table_id', $table->id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->latest()
                ->first();

            if ($activeOrder) {
                // Revertir inventario de ítems que aún no se consumieron
                foreach ($activeOrder->items as $item) {
                    $product = Product::with('ingredients')->find($item->product_id);
                    if ($product) {
                        foreach ($product->ingredients as $ingredient) {
                            $revertQty = $ingredient->pivot->quantity_required * $item->quantity;
                            $ingredient->increment('current_stock', $revertQty);
                        }
                    }
                }

                $activeOrder->update([
                    'status' => OrderStatus::Cancelled->value,
                    'cancellation_reason' => $reason ?? 'Mesa liberada por mesero/administrador',
                    'cancelled_by' => $cancelledByUserId,
                ]);
            }

            RestaurantTable::where('merged_with_table_id', $table->id)
                ->update(['merged_with_table_id' => null]);

            $table->update([
                'status' => TableStatus::Available->value,
                'merged_with_table_id' => null,
            ]);

            return $table;
        });
    }
}
