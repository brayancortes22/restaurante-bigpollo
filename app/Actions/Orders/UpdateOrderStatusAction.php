<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $newStatus, ?string $reason = null, ?int $cancelledByUserId = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $reason, $cancelledByUserId) {
            $updateData = ['status' => $newStatus->value];

            if ($newStatus === OrderStatus::Cancelled) {
                $updateData['cancellation_reason'] = $reason;
                $updateData['cancelled_by'] = $cancelledByUserId;
            }

            if ($newStatus === OrderStatus::Paid) {
                $updateData['paid_at'] = now();
            }

            $order->update($updateData);

            // Si la comanda se paga o se cancela, liberar la mesa si no hay más pedidos activos
            if (in_array($newStatus, [OrderStatus::Paid, OrderStatus::Cancelled]) && $order->restaurant_table_id) {
                $hasOtherActiveOrders = Order::where('restaurant_table_id', $order->restaurant_table_id)
                    ->where('id', '!=', $order->id)
                    ->whereIn('status', [OrderStatus::Pending->value, OrderStatus::InKitchen->value, OrderStatus::Ready->value, OrderStatus::Served->value])
                    ->exists();

                if (!$hasOtherActiveOrders) {
                    RestaurantTable::where('id', $order->restaurant_table_id)
                        ->update(['status' => TableStatus::Available->value]);
                }
            }

            return $order->fresh(['items', 'table']);
        });
    }
}
