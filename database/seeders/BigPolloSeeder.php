<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BigPolloSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Restaurante Tenant Principal
        $tenant = Tenant::create([
            'name' => 'Restaurante Big Pollo',
            'slug' => 'big-pollo',
            'nit' => '901.789.456-2',
            'address' => 'Calle Principal # 4 - 20, Huila',
            'phone' => '+57 310 123 4567',
            'email' => 'contacto@bigpollo.com',
            'is_active' => true,
        ]);

        // 2. Crear Usuarios con distintos roles para el restaurante
        $users = [
            [
                'name' => 'Brayan Cortés (Admin)',
                'email' => 'admin@bigpollo.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Cajero Principal',
                'email' => 'cajero@bigpollo.com',
                'password' => Hash::make('caja123'),
                'role' => 'cajero',
            ],
            [
                'name' => 'Mesero Turno Día',
                'email' => 'mesero@bigpollo.com',
                'password' => Hash::make('mesero123'),
                'role' => 'mesero',
            ],
            [
                'name' => 'Jefe de Cocina',
                'email' => 'cocina@bigpollo.com',
                'password' => Hash::make('cocina123'),
                'role' => 'cocina',
            ],
        ];

        foreach ($users as $userData) {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'role' => $userData['role'],
                'is_active' => true,
            ]);
        }

        // 3. Crear Mesas del Restaurante
        for ($i = 1; $i <= 8; $i++) {
            RestaurantTable::create([
                'tenant_id' => $tenant->id,
                'table_number' => "Mesa {$i}",
                'capacity' => ($i % 2 === 0) ? 6 : 4,
                'location' => ($i <= 4) ? 'Salon Principal' : 'Terraza',
                'status' => 'available',
            ]);
        }

        // 4. Insumos de Inventario
        $ingPollo = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Crudo Fresco',
            'unit' => 'kg',
            'current_stock' => 80.0,
            'minimum_stock' => 15.0,
            'cost_per_unit' => 9500.00,
        ]);

        $ingPapas = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Papa Pastusa Seleccionada',
            'unit' => 'kg',
            'current_stock' => 50.0,
            'minimum_stock' => 10.0,
            'cost_per_unit' => 3200.00,
        ]);

        $ingAceite = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Aceite Vegetal para Freír',
            'unit' => 'lt',
            'current_stock' => 40.0,
            'minimum_stock' => 8.0,
            'cost_per_unit' => 7800.00,
        ]);

        $ingHarina = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mezcla Apanado Especial Broaster',
            'unit' => 'kg',
            'current_stock' => 25.0,
            'minimum_stock' => 5.0,
            'cost_per_unit' => 5500.00,
        ]);

        $ingGaseosa = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gaseosa Postobón Manzana 1.5L',
            'unit' => 'unit',
            'current_stock' => 30.0,
            'minimum_stock' => 6.0,
            'cost_per_unit' => 4200.00,
        ]);

        // 5. Categorías del Menú
        $catBroaster = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Broaster Crujiente',
            'slug' => 'pollo-broaster',
            'display_order' => 1,
        ]);

        $catComidas = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Comidas Rápidas & Salchipapas',
            'slug' => 'comidas-rapidas',
            'display_order' => 2,
        ]);

        $catBebidas = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bebidas Frías',
            'slug' => 'bebidas',
            'display_order' => 3,
        ]);

        // 6. Productos y Recetas (Descuento automático de inventario)
        $comboCuarto = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $catBroaster->id,
            'name' => 'Combo 1/4 Pollo Broaster + Papas',
            'description' => 'Presa grande de pollo broaster crujiente, porción de papas a la francesa y salsa de la casa.',
            'price' => 18000.00,
            'tax_percentage' => 8.00,
            'is_available' => true,
        ]);
        $comboCuarto->ingredients()->attach([
            $ingPollo->id => ['quantity_required' => 0.350],
            $ingPapas->id => ['quantity_required' => 0.200],
            $ingAceite->id => ['quantity_required' => 0.080],
            $ingHarina->id => ['quantity_required' => 0.060],
        ]);

        $comboFamiliar = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $catBroaster->id,
            'name' => 'Pollo Entero Familiar + Papas + Gaseosa',
            'description' => '8 presas de pollo crujiente, porción familiar de papas y gaseosa 1.5L.',
            'price' => 56000.00,
            'tax_percentage' => 8.00,
            'is_available' => true,
        ]);
        $comboFamiliar->ingredients()->attach([
            $ingPollo->id => ['quantity_required' => 1.400],
            $ingPapas->id => ['quantity_required' => 0.500],
            $ingAceite->id => ['quantity_required' => 0.250],
            $ingHarina->id => ['quantity_required' => 0.200],
            $ingGaseosa->id => ['quantity_required' => 1.000],
        ]);

        $salchipapa = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $catComidas->id,
            'name' => 'Super Salchipapa Big Pollo',
            'description' => 'Papas crujientes, salchicha manguera, trozos de pechuga desmechada y queso fundido.',
            'price' => 24000.00,
            'tax_percentage' => 8.00,
            'is_available' => true,
        ]);
        $salchipapa->ingredients()->attach([
            $ingPapas->id => ['quantity_required' => 0.350],
            $ingPollo->id => ['quantity_required' => 0.150],
            $ingAceite->id => ['quantity_required' => 0.070],
        ]);

        $gaseosa = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $catBebidas->id,
            'name' => 'Gaseosa Postobón 1.5L',
            'description' => 'Botella familiar bien fría.',
            'price' => 8000.00,
            'tax_percentage' => 8.00,
            'is_available' => true,
        ]);
        $gaseosa->ingredients()->attach([
            $ingGaseosa->id => ['quantity_required' => 1.000],
        ]);
    }
}
