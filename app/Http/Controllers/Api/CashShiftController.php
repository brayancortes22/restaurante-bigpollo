<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cash\CloseCashShiftAction;
use App\Actions\Cash\OpenCashShiftAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cash\CloseCashShiftRequest;
use App\Http\Requests\Cash\OpenCashShiftRequest;
use App\Models\CashShift;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashShiftController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));

        $shift = CashShift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->with('user:id,name,role')
            ->first();

        return response()->json([
            'is_open' => (bool) $shift,
            'shift' => $shift,
        ]);
    }

    public function open(OpenCashShiftRequest $request, OpenCashShiftAction $action): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));
        $userId = (int) ($request->user()?->id ?? $request->input('user_id', 1));

        $shift = $action->execute(
            tenantId: $tenantId,
            userId: $userId,
            openingAmount: (float) $request->validated('opening_amount'),
            notes: $request->validated('notes')
        );

        return response()->json([
            'message' => 'Caja aperturada exitosamente con base inicial.',
            'shift' => $shift,
        ], 201);
    }

    public function close(CloseCashShiftRequest $request, CloseCashShiftAction $action): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));

        $shift = CashShift::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->firstOrFail();

        $closedShift = $action->execute(
            shift: $shift,
            actualCashCounted: (float) $request->validated('actual_cash_counted'),
            notes: $request->validated('notes')
        );

        return response()->json([
            'message' => 'Cierre de turno y Arqueo Z completado exitosamente.',
            'shift' => $closedShift,
        ]);
    }
}
