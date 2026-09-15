<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));

        $categories = Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['products' => function ($query) {
                $query->where('is_available', true)
                      ->with('ingredients');
            }])
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
