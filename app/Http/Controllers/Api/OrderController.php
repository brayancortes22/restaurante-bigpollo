<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateOrderRequest;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));

        $query = Order::where('tenant_id', $tenantId)
            ->with(['items.product', 'table', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('table_id')) {
            $query->where('restaurant_table_id', (int) $request->query('table_id'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return OrderResource::collection($orders);
    }

    public function store(CreateOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));
        $userId = $request->user()?->id ?? $request->input('user_id');

        $dto = $request->toDTO($tenantId, $userId ? (int) $userId : null);
        $order = $action->execute($dto);
        $order->load(['items.product', 'table', 'user']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(Order $order, UpdateOrderStatusRequest $request, UpdateOrderStatusAction $action): OrderResource
    {
        $updatedOrder = $action->execute(
            order: $order,
            newStatus: $request->getStatusEnum(),
            reason: $request->validated('reason'),
            cancelledByUserId: $request->validated('cancelled_by')
        );

        $updatedOrder->load(['items.product', 'table', 'user']);

        return new OrderResource($updatedOrder);
    }
}
