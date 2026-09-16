<?php

namespace App\Actions\Dashboard;

use App\Models\CashShift;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetBusinessMetricsAction
{
    /**
     * Calcula las métricas ejecutivas de negocio para el dueño del restaurante.
     */
    public function execute(int $tenantId): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // 1. Órdenes Pagadas Hoy
        $paidOrdersToday = Order::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->get();

        $totalSalesToday = (float) $paidOrdersToday->sum('total');
        $ordersCountToday = $paidOrdersToday->count();
        $avgTicket = $ordersCountToday > 0 ? ($totalSalesToday / $ordersCountToday) : 0.0;

        // 2. Ventas por Medio de Pago
        $cashSales = (float) $paidOrdersToday->where('payment_method', 'cash')->sum('total');
        $cardSales = (float) $paidOrdersToday->where('payment_method', 'card')->sum('total');
        $transferSales = (float) $paidOrdersToday->where('payment_method', 'transfer')->sum('total');

        // 3. Facturación DIAN con Factus
        $dianOrders = $paidOrdersToday->filter(fn ($o) => !empty($o->factus_bill_number) || $o->factus_status === 'sent_valid');
        $dianTotal = (float) $dianOrders->sum('total');
        $dianCount = $dianOrders->count();

        // 4. Ocupación de Mesas del Salón en Vivo
        $totalTables = RestaurantTable::where('tenant_id', $tenantId)->count();
        $occupiedTables = RestaurantTable::where('tenant_id', $tenantId)
            ->where('status', 'occupied')
            ->count();
        $occupancyRate = $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100, 1) : 0.0;

        // 5. Turno Actual de Caja
        $activeShift = CashShift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->with('user')
            ->latest('opened_at')
            ->first();

        // 6. Insumos Críticos de Inventario (Alerta Stock Bajo)
        $lowStockIngredients = Ingredient::where('tenant_id', $tenantId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock', 'asc')
            ->take(6)
            ->get();

        // 7. Top 5 Platos Más Vendidos Hoy
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$todayStart, $todayEnd])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        // 8. Total de Comandas Pendientes / En Mesa Actualmente
        $activeOrdersCount = Order::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_kitchen', 'ready', 'served'])
            ->count();

        return [
            'total_sales_today' => $totalSalesToday,
            'orders_count_today' => $ordersCountToday,
            'average_ticket' => $avgTicket,
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'transfer_sales' => $transferSales,
            'dian_sales_total' => $dianTotal,
            'dian_invoices_count' => $dianCount,
            'total_tables' => $totalTables,
            'occupied_tables' => $occupiedTables,
            'occupancy_rate' => $occupancyRate,
            'active_orders_count' => $activeOrdersCount,
            'active_shift' => $activeShift,
            'low_stock_ingredients' => $lowStockIngredients,
            'top_products' => $topProducts,
        ];
    }
}
