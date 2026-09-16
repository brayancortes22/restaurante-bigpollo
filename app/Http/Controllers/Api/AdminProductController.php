<?php

namespace App\Http\Controllers\Api;

use App\Actions\Products\CreateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));

        $products = Product::where('tenant_id', $tenantId)
            ->with(['category', 'ingredients'])
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $categories = Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'data' => [
                'products' => $products,
                'categories' => $categories,
            ],
        ]);
    }
    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));

        $product = $action->execute($tenantId, $request->validated());

        return response()->json([
            'message' => 'Plato creado exitosamente con su receta de inventario.',
            'product' => $product,
        ], 201);
    }

    public function toggleAvailability(Product $product): JsonResponse
    {
        $product->update([
            'is_available' => !$product->is_available,
        ]);

        return response()->json([
            'message' => 'Disponibilidad actualizada.',
            'is_available' => $product->is_available,
        ]);
    }

    public function ingredients(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));

        $ingredients = Ingredient::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $ingredients,
        ]);
    }
}
