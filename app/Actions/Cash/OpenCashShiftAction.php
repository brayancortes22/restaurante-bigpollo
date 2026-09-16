<?php

namespace App\Actions\Cash;

use App\Models\CashShift;
use InvalidArgumentException;

class OpenCashShiftAction
{
    public function execute(int $tenantId, int $userId, float $openingAmount, ?string $notes = null): CashShift
    {
        $existing = CashShift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            throw new InvalidArgumentException('Ya existe una caja abierta para este restaurante. Debe cerrarla antes de abrir un nuevo turno.');
        }

        return CashShift::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_amount' => $openingAmount,
            'expected_cash' => $openingAmount,
            'status' => 'open',
            'notes' => $notes,
        ]);
    }
}
