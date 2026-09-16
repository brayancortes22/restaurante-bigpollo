<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function execute(int $tenantId, array $data): Product
    {
        return DB::transaction(function () use ($tenantId, $data) {
            $product = Product::create([
                'tenant_id' => $tenantId,
                'category_id' => (int) $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => (float) $data['price'],
                'tax_percentage' => isset($data['tax_percentage']) ? (float) $data['tax_percentage'] : 8.00,
                'is_available' => isset($data['is_available']) ? (bool) $data['is_available'] : true,
            ]);

            if (!empty($data['ingredients']) && is_array($data['ingredients'])) {
                foreach ($data['ingredients'] as $ing) {
                    $product->ingredients()->attach($ing['ingredient_id'], [
                        'quantity_required' => (float) $ing['quantity_required'],
                    ]);
                }
            }

            return $product->load(['category', 'ingredients']);
        });
    }
}
