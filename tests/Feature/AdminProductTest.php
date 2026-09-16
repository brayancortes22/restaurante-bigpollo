<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_admin_menu_blade_view(): void
    {
        $tenant = Tenant::create(['name' => 'Big Pollo Admin Test', 'slug' => 'bp-admin-test']);
        $admin = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin_view@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/menu');

        $response->assertStatus(200);
        $response->assertViewIs('admin.menu');
        $response->assertSee('Catálogo');
        $response->assertSee('Recetario Big Pollo');
    }

    public function test_can_list_admin_products_and_categories(): void
    {
        $tenant = Tenant::create(['name' => 'Big Pollo Admin Test', 'slug' => 'bp-admin-test']);
        $category = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Broaster',
            'slug' => 'pollo-broaster',
        ]);

        Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Combo Familiar Broaster',
            'price' => 45000,
            'tax_percentage' => 8.0,
            'is_available' => true,
        ]);

        Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Combo Agotado',
            'price' => 30000,
            'tax_percentage' => 8.0,
            'is_available' => false,
        ]);

        $response = $this->withHeader('X-Tenant-ID', (string) $tenant->id)
            ->getJson('/api/admin/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'products',
                'categories',
            ],
        ]);

        $this->assertCount(2, $response->json('data.products'));
    }

    public function test_can_create_product_with_recipe_ingredients_via_api(): void
    {
        $tenant = Tenant::create(['name' => 'Big Pollo Admin Test', 'slug' => 'bp-admin-test']);
        $category = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Especialidades Asado',
            'slug' => 'especialidades-asado',
        ]);

        $ingPollo = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Crudo',
            'unit' => 'unit',
            'current_stock' => 50,
            'minimum_stock' => 5,
        ]);

        $ingPapas = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Papas Congeladas',
            'unit' => 'portion',
            'current_stock' => 100,
            'minimum_stock' => 10,
        ]);

        $payload = [
            'tenant_id' => $tenant->id,
            'name' => 'Medio Pollo Asado con Papas',
            'category_id' => $category->id,
            'description' => 'Medio pollo dorado al carbón con papas a la francesa crocantes',
            'price' => 24000,
            'tax_percentage' => 8.0,
            'is_available' => true,
            'ingredients' => [
                [
                    'ingredient_id' => $ingPollo->id,
                    'quantity_required' => 0.5,
                ],
                [
                    'ingredient_id' => $ingPapas->id,
                    'quantity_required' => 1.0,
                ],
            ],
        ];

        $response = $this->withHeader('X-Tenant-ID', (string) $tenant->id)
            ->postJson('/api/admin/products', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('product.name', 'Medio Pollo Asado con Papas');

        $this->assertDatabaseHas('products', [
            'tenant_id' => $tenant->id,
            'name' => 'Medio Pollo Asado con Papas',
            'price' => 24000,
        ]);

        $this->assertDatabaseHas('product_ingredients', [
            'ingredient_id' => $ingPollo->id,
            'quantity_required' => 0.5,
        ]);

        $this->assertDatabaseHas('product_ingredients', [
            'ingredient_id' => $ingPapas->id,
            'quantity_required' => 1.0,
        ]);
    }

    public function test_can_toggle_product_availability(): void
    {
        $tenant = Tenant::create(['name' => 'Big Pollo Admin Test', 'slug' => 'bp-admin-test']);
        $category = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bebidas',
            'slug' => 'bebidas',
        ]);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Gaseosa Colombiana 1.5L',
            'price' => 7000,
            'tax_percentage' => 8.0,
            'is_available' => true,
        ]);

        // Toggle to unavailable
        $toggle1 = $this->patchJson("/api/admin/products/{$product->id}/toggle");
        $toggle1->assertStatus(200);
        $toggle1->assertJsonPath('is_available', false);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_available' => false,
        ]);

        // Toggle back to available
        $toggle2 = $this->patchJson("/api/admin/products/{$product->id}/toggle");
        $toggle2->assertStatus(200);
        $toggle2->assertJsonPath('is_available', true);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_available' => true,
        ]);
    }

    public function test_can_fetch_tenant_ingredients_list(): void
    {
        $tenant = Tenant::create(['name' => 'Big Pollo Admin Test', 'slug' => 'bp-admin-test']);

        Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Arepas de Maíz',
            'unit' => 'unit',
            'current_stock' => 80,
            'minimum_stock' => 15,
        ]);

        $response = $this->withHeader('X-Tenant-ID', (string) $tenant->id)
            ->getJson('/api/admin/ingredients');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Arepas de Maíz');
    }
}
