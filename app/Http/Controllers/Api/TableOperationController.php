<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\AddItemsToOrderAction;
use App\Actions\Orders\ModifyOrderItemAction;
use App\Actions\Tables\MergeTablesAction;
use App\Actions\Tables\ReleaseTableAction;
use App\Actions\Tables\TransferTableAction;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableOperationController extends Controller
{
    public function activeOrder(RestaurantTable $table): JsonResponse
    {
        $order = Order::where('restaurant_table_id', $table->id)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with(['items.product.category', 'user'])
            ->latest()
            ->first();

        if (!$order) {
            return response()->json([
                'has_active_order' => false,
                'table' => $table,
                'message' => 'No hay comanda activa para esta mesa.',
            ]);
        }

        $minutesElapsed = (int) now()->diffInMinutes($order->created_at);

        return response()->json([
            'has_active_order' => true,
            'table' => $table,
            'order' => $order,
            'minutes_elapsed' => $minutesElapsed,
            'is_delayed' => $minutesElapsed >= 20,
        ]);
    }

    public function addItems(Request $request, RestaurantTable $table, AddItemsToOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::where('restaurant_table_id', $table->id)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->latest()
            ->firstOrFail();

        $updatedOrder = $action->execute($order, $validated['items']);

        return response()->json([
            'message' => '¡Platos adicionales agregados a la comanda exitosamente!',
            'order' => $updatedOrder,
        ]);
    }

    public function modifyItem(Request $request, OrderItem $item, ModifyOrderItemAction $action): JsonResponse
    {
        $validated = $request->validate([
            'new_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'new_quantity' => ['nullable', 'integer', 'min:1'],
            'cancel_item' => ['nullable', 'boolean'],
            'force' => ['nullable', 'boolean'],
        ]);

        $result = $action->execute(
            orderItem: $item,
            newProductId: $validated['new_product_id'] ?? null,
            newQuantity: $validated['new_quantity'] ?? null,
            cancelItem: $validated['cancel_item'] ?? false,
            force: $validated['force'] ?? false
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function transfer(Request $request, RestaurantTable $table, TransferTableAction $action): JsonResponse
    {
        $validated = $request->validate([
            'to_table_id' => ['required', 'integer', 'exists:restaurant_tables,id'],
        ]);

        $toTable = RestaurantTable::findOrFail($validated['to_table_id']);
        $order = $action->execute($table, $toTable);

        return response()->json([
            'message' => "Comanda trasladada de {$table->table_number} a {$toTable->table_number}.",
            'order' => $order,
        ]);
    }

    public function merge(Request $request, RestaurantTable $table, MergeTablesAction $action): JsonResponse
    {
        $validated = $request->validate([
            'target_table_id' => ['required', 'integer', 'exists:restaurant_tables,id'],
        ]);

        $targetTable = RestaurantTable::findOrFail($validated['target_table_id']);
        $order = $action->execute($table, $targetTable);

        return response()->json([
            'message' => "Cuentas consolidadas en {$targetTable->table_number}.",
            'order' => $order,
        ]);
    }

    public function release(Request $request, RestaurantTable $table, ReleaseTableAction $action): JsonResponse
    {
        $reason = $request->input('reason', 'Liberada por personal de servicio');
        $cancelledBy = $request->user()?->id;

        $table = $action->execute($table, $reason, $cancelledBy);

        return response()->json([
            'message' => "Mesa {$table->table_number} liberada correctamente.",
            'table' => $table,
        ]);
    }
}
