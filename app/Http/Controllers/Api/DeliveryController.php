<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\DTOs\CreateOrderDTO;
use App\DTOs\OrderItemDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', Tenant::first()?->id ?? 1);

        $deliveries = Order::where('tenant_id', $tenantId)
            ->where('type', OrderType::Delivery->value)
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }

    public function store(Request $request, CreateOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'customer_nit_cedula' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $tenantId = (int) $request->header('X-Tenant-ID', Tenant::first()?->id ?? 1);

        $itemDTOs = array_map(
            fn ($item) => new OrderItemDTO(
                productId: (int) $item['product_id'],
                quantity: (int) $item['quantity'],
                notes: $item['notes'] ?? null
            ),
            $validated['items']
        );

        $dto = new CreateOrderDTO(
            tenantId: $tenantId,
            tableId: null, // Pedido a domicilio no tiene mesa física
            userId: $request->user()?->id,
            type: OrderType::Delivery,
            customerName: $validated['customer_name'],
            customerNitCedula: $validated['customer_nit_cedula'] ?? null,
            customerEmail: null,
            customerPhone: $validated['customer_phone'],
            deliveryAddress: $validated['delivery_address'],
            notes: $validated['notes'] ?? 'Pedido a Domicilio Big Pollo',
            items: $itemDTOs
        );

        $order = $action->execute($dto);

        return response()->json([
            'message' => '¡Pedido a domicilio registrado exitosamente!',
            'order' => $order->load(['items.product']),
            'tracking_url' => url("/track/{$order->order_number}"),
        ], 201);
    }

    public function track(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'No se encontró ningún pedido con ese número.'], 404);
        }

        $minutesElapsed = (int) now()->diffInMinutes($order->created_at);

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => match ($order->status) {
                'pending' => 'Recibido en Restaurante',
                'in_kitchen' => 'Preparando en Cocina (Dorado & Broaster)',
                'ready' => 'Empacado y Listo para Entrega',
                'served' => 'En Camino con Repartidor Big Pollo',
                'paid' => 'Entregado y Pagado',
                'cancelled' => 'Cancelado',
                default => ucfirst($order->status),
            },
            'customer_name' => $order->customer_name,
            'delivery_address' => $order->delivery_address,
            'customer_phone' => $order->customer_phone,
            'total' => $order->total,
            'items' => $order->items->map(fn ($it) => [
                'name' => $it->product->name,
                'quantity' => $it->quantity,
                'subtotal' => $it->subtotal,
            ]),
            'minutes_elapsed' => $minutesElapsed,
            'created_at' => $order->created_at->format('h:i A'),
        ]);
    }
}
