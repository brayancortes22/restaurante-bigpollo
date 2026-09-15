<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));

        $tables = RestaurantTable::where('tenant_id', $tenantId)
            ->orderBy('table_number')
            ->get();

        return response()->json([
            'data' => $tables,
            'meta' => [
                'total' => $tables->count(),
                'occupied' => $tables->where('status', 'occupied')->count(),
                'available' => $tables->where('status', 'available')->count(),
            ],
        ]);
    }
}
