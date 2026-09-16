<?php

namespace App\Actions\Cash;

use App\Models\CashShift;
use App\Models\Order;
use InvalidArgumentException;

class CloseCashShiftAction
{
    public function execute(CashShift $shift, float $actualCashCounted, ?string $notes = null): CashShift
    {
        if ($shift->status !== 'open') {
            throw new InvalidArgumentException('Este turno de caja ya se encuentra cerrado.');
        }

        $now = now();

        // 1. Totalizar ventas por método de pago generadas durante el turno
        $orders = Order::where('tenant_id', $shift->tenant_id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$shift->opened_at, $now])
            ->get();

        $cashSales = (float) $orders->where('payment_method', 'cash')->sum('total');
        $cardSales = (float) $orders->where('payment_method', 'card')->sum('total');
        $transferSales = (float) $orders->where('payment_method', 'transfer')->sum('total');
        $totalSales = $cashSales + $cardSales + $transferSales;

        // 2. Efectivo esperado = Base inicial + Ventas en efectivo
        $expectedCash = (float) ($shift->opening_amount + $cashSales);
        $difference = $actualCashCounted - $expectedCash;

        // 3. Registrar cierre y arqueo Z
        $shift->update([
            'closed_at' => $now,
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'transfer_sales' => $transferSales,
            'total_sales' => $totalSales,
            'expected_cash' => $expectedCash,
            'actual_cash_counted' => $actualCashCounted,
            'difference' => $difference,
            'status' => 'closed',
            'notes' => $notes ? ($shift->notes ? $shift->notes . "\n" . $notes : $notes) : $shift->notes,
        ]);

        return $shift->fresh();
    }
}
