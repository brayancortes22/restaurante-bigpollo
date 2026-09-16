<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CashShift;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
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

        $adminUser = null;
        $cajeroUser = null;

        foreach ($users as $userData) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'role' => $userData['role'],
                'is_active' => true,
            ]);
            if ($user->role === 'admin') $adminUser = $user;
            if ($user->role === 'cajero') $cajeroUser = $user;
        }

        // 3. Crear Mesas del Restaurante
        $createdTables = [];
        for ($i = 1; $i <= 10; $i++) {
            $createdTables[$i] = RestaurantTable::create([
                'tenant_id' => $tenant->id,
                'table_number' => "Mesa {$i}",
                'capacity' => ($i % 2 === 0) ? 6 : 4,
                'location' => ($i <= 6) ? 'Salon Principal' : 'Terraza Familiar',
                'status' => 'available',
            ]);
        }

        // 4. Insumos de Inventario Ampliados (Materia Prima)
        $ingPollo = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Crudo Fresco',
            'unit' => 'kg',
            'current_stock' => 120.0,
            'minimum_stock' => 20.0,
            'cost_per_unit' => 9500.00,
        ]);

        $ingPapas = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Papa Pastusa Seleccionada',
            'unit' => 'kg',
            'current_stock' => 80.0,
            'minimum_stock' => 15.0,
            'cost_per_unit' => 3200.00,
        ]);

        $ingPapaCriolla = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Papa Criolla Amarilla',
            'unit' => 'kg',
            'current_stock' => 35.0,
            'minimum_stock' => 10.0,
            'cost_per_unit' => 4500.00,
        ]);

        $ingAceite = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Aceite Vegetal para Freír',
            'unit' => 'lt',
            'current_stock' => 60.0,
            'minimum_stock' => 12.0,
            'cost_per_unit' => 7800.00,
        ]);

        $ingHarina = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mezcla Apanado Especial Broaster',
            'unit' => 'kg',
            'current_stock' => 40.0,
            'minimum_stock' => 8.0,
            'cost_per_unit' => 5500.00,
        ]);

        $ingCarbon = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Carbón Vegetal / Leña para Asar',
            'unit' => 'kg',
            'current_stock' => 70.0,
            'minimum_stock' => 15.0,
            'cost_per_unit' => 2500.00,
        ]);

        $ingQueso = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Queso Doble Crema Fundido',
            'unit' => 'kg',
            'current_stock' => 18.0,
            'minimum_stock' => 5.0,
            'cost_per_unit' => 16000.00,
        ]);

        $ingSalchicha = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Salchicha Manguera Especial',
            'unit' => 'kg',
            'current_stock' => 22.0,
            'minimum_stock' => 6.0,
            'cost_per_unit' => 11000.00,
        ]);

        $ingArepas = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Arepas Blancas de Maíz',
            'unit' => 'unit',
            'current_stock' => 150.0,
            'minimum_stock' => 30.0,
            'cost_per_unit' => 400.00,
        ]);

        $ingYuca = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Yuca Fresca Pelada',
            'unit' => 'kg',
            'current_stock' => 30.0,
            'minimum_stock' => 8.0,
            'cost_per_unit' => 2800.00,
        ]);

        $ingPlatano = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Plátano Maduro',
            'unit' => 'unit',
            'current_stock' => 40.0,
            'minimum_stock' => 10.0,
            'cost_per_unit' => 1200.00,
        ]);

        $ingMenudencias = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Menudencias y Vísceras Frescas',
            'unit' => 'kg',
            'current_stock' => 25.0,
            'minimum_stock' => 5.0,
            'cost_per_unit' => 4500.00,
        ]);

        $ingArroz = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Arroz Blanco Grano Entero',
            'unit' => 'kg',
            'current_stock' => 45.0,
            'minimum_stock' => 10.0,
            'cost_per_unit' => 3800.00,
        ]);

        $ingGaseosa = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gaseosa Postobón Manzana 1.5L',
            'unit' => 'unit',
            'current_stock' => 48.0,
            'minimum_stock' => 12.0,
            'cost_per_unit' => 4200.00,
        ]);

        $ingCerveza = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cerveza Club Colombia Dorada 330ml',
            'unit' => 'unit',
            'current_stock' => 72.0,
            'minimum_stock' => 24.0,
            'cost_per_unit' => 3200.00,
        ]);

        $ingLimon = Ingredient::create([
            'tenant_id' => $tenant->id,
            'name' => 'Limón Tahití Jugoso',
            'unit' => 'kg',
            'current_stock' => 20.0,
            'minimum_stock' => 5.0,
            'cost_per_unit' => 3500.00,
        ]);

        // 5. Categorías del Menú
        $catAsado = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Asado al Carbón',
            'slug' => 'pollo-asado',
            'display_order' => 1,
        ]);

        $catBroaster = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pollo Broaster Crujiente',
            'slug' => 'pollo-broaster',
            'display_order' => 2,
        ]);

        $catComidas = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Comidas Rápidas & Asados',
            'slug' => 'comidas-rapidas',
            'display_order' => 3,
        ]);

        $catSopas = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sopas & Tradición Criolla',
            'slug' => 'sopas-tradicion',
            'display_order' => 4,
        ]);

        $catAcomp = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Acompañamientos & Porciones',
            'slug' => 'acompanamientos',
            'display_order' => 5,
        ]);

        $catBebidas = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bebidas Frías & Cervezas',
            'slug' => 'bebidas',
            'display_order' => 6,
        ]);

        // Helper para crear plato y vincular receta
        $createDish = function ($catId, $name, $desc, $price, $recipe = []) use ($tenant) {
            $prod = Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $catId,
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'tax_percentage' => 8.00,
                'is_available' => true,
            ]);
            if (!empty($recipe)) {
                $prod->ingredients()->attach($recipe);
            }
            return $prod;
        };

        // --------------------------------------------------------------------------
        // CATEGORÍA 1: Pollo Asado al Carbón (5 platos)
        // --------------------------------------------------------------------------
        $p1 = $createDish($catAsado->id, 'Pollo Entero Asado Tradicional con Papas y Arepas', 'Pollo dorado a la brasa con adobo secreto, porción generosa de papas al vapor y arepas.', 42000, [
            $ingPollo->id => ['quantity_required' => 1.400],
            $ingPapas->id => ['quantity_required' => 0.450],
            $ingArepas->id => ['quantity_required' => 4.0],
            $ingCarbon->id => ['quantity_required' => 0.500],
        ]);

        $p2 = $createDish($catAsado->id, 'Medio Pollo Asado con Papas', '4 presas jugosas asadas al carbón acompañadas de papas y ají de la casa.', 24000, [
            $ingPollo->id => ['quantity_required' => 0.700],
            $ingPapas->id => ['quantity_required' => 0.250],
            $ingArepas->id => ['quantity_required' => 2.0],
            $ingCarbon->id => ['quantity_required' => 0.250],
        ]);

        $p3 = $createDish($catAsado->id, 'Combo 1/4 Pollo Asado (Pechuga y Ala)', 'Presa grande de pechuga dorada al carbón con papas francesas y arepa.', 14000, [
            $ingPollo->id => ['quantity_required' => 0.350],
            $ingPapas->id => ['quantity_required' => 0.200],
            $ingArepas->id => ['quantity_required' => 1.0],
        ]);

        $p4 = $createDish($catAsado->id, 'Combo 1/4 Pollo Asado (Pierna y Pernil)', 'Pierna pernil tierna y jugosa marinada con especias, papas y arepa.', 13000, [
            $ingPollo->id => ['quantity_required' => 0.350],
            $ingPapas->id => ['quantity_required' => 0.200],
            $ingArepas->id => ['quantity_required' => 1.0],
        ]);

        $p5 = $createDish($catAsado->id, 'Pollo Asado Especial Miel Mostaza', 'Pollo entero bañado en glaseado agridulce de miel de abejas y mostaza Dijon con papas criollas.', 45000, [
            $ingPollo->id => ['quantity_required' => 1.400],
            $ingPapaCriolla->id => ['quantity_required' => 0.400],
            $ingCarbon->id => ['quantity_required' => 0.500],
        ]);

        // --------------------------------------------------------------------------
        // CATEGORÍA 2: Pollo Broaster Crujiente (7 platos)
        // --------------------------------------------------------------------------
        $p6 = $createDish($catBroaster->id, 'Pollo Entero Broaster Crujiente Familiar', '8 presas apanadas con corteza dorada ultra crujiente, papas francesas y salsas.', 46000, [
            $ingPollo->id => ['quantity_required' => 1.400],
            $ingPapas->id => ['quantity_required' => 0.500],
            $ingHarina->id => ['quantity_required' => 0.200],
            $ingAceite->id => ['quantity_required' => 0.250],
        ]);

        $p7 = $createDish($catBroaster->id, 'Medio Pollo Broaster Crujiente', '4 presas de pollo broaster recién fritas con papas francesas.', 26000, [
            $ingPollo->id => ['quantity_required' => 0.700],
            $ingPapas->id => ['quantity_required' => 0.250],
            $ingHarina->id => ['quantity_required' => 0.100],
            $ingAceite->id => ['quantity_required' => 0.130],
        ]);

        $p8 = $createDish($catBroaster->id, 'Combo 1/4 Pollo Broaster + Francesa', 'Pechuga o pierna pernil apanada con papas francesas y miel.', 16000, [
            $ingPollo->id => ['quantity_required' => 0.350],
            $ingPapas->id => ['quantity_required' => 0.200],
            $ingHarina->id => ['quantity_required' => 0.060],
            $ingAceite->id => ['quantity_required' => 0.080],
        ]);

        $p9 = $createDish($catBroaster->id, 'Balde Familiar Big Pollo x 10 Presas', 'Mega balde para compartir con 10 presas broaster, papas gigantes y gaseosa 1.5L.', 58000, [
            $ingPollo->id => ['quantity_required' => 1.750],
            $ingPapas->id => ['quantity_required' => 0.600],
            $ingHarina->id => ['quantity_required' => 0.250],
            $ingAceite->id => ['quantity_required' => 0.300],
            $ingGaseosa->id => ['quantity_required' => 1.0],
        ]);

        $p10 = $createDish($catBroaster->id, 'Alitas Broaster BBQ x 8 con Papas', '8 alitas crujientes bañadas en salsa BBQ artesanal con papas francesas.', 22000, [
            $ingPollo->id => ['quantity_required' => 0.500],
            $ingPapas->id => ['quantity_required' => 0.200],
            $ingAceite->id => ['quantity_required' => 0.100],
        ]);

        $p11 = $createDish($catBroaster->id, 'Alitas Broaster Picantes / Buffalo x 8', '8 alitas crujientes con salsa picante picante de la casa y bastones de apio.', 23000, [
            $ingPollo->id => ['quantity_required' => 0.500],
            $ingPapas->id => ['quantity_required' => 0.200],
            $ingAceite->id => ['quantity_required' => 0.100],
        ]);

        $p12 = $createDish($catBroaster->id, 'Tenders de Pechuga Apanada x 6', 'Lomitos suaves de pechuga apanados en hojuelas de maíz con aderezo tártara.', 19000, [
            $ingPollo->id => ['quantity_required' => 0.400],
            $ingHarina->id => ['quantity_required' => 0.080],
            $ingAceite->id => ['quantity_required' => 0.080],
        ]);

        // --------------------------------------------------------------------------
        // CATEGORÍA 3: Comidas Rápidas & Asados (6 platos)
        // --------------------------------------------------------------------------
        $p13 = $createDish($catComidas->id, 'Hamburguesa de Pollo Crispy Doble Queso', 'Pechuga apanada extra crujiente, doble queso fundido, lechuga y tomate en pan brioche.', 18000, [
            $ingPollo->id => ['quantity_required' => 0.200],
            $ingQueso->id => ['quantity_required' => 0.060],
            $ingPapas->id => ['quantity_required' => 0.150],
        ]);

        $p14 = $createDish($catComidas->id, 'Hamburguesa de Pechuga Asada al Carbón', 'Filete de pechuga marinada al carbón, queso, cebolla caramelizada y salsa tártara.', 17000, [
            $ingPollo->id => ['quantity_required' => 0.200],
            $ingQueso->id => ['quantity_required' => 0.040],
            $ingCarbon->id => ['quantity_required' => 0.100],
        ]);

        $p15 = $createDish($catComidas->id, 'Super Salchipapa Big Pollo con Pechuga', 'Papas crujientes, salchicha manguera dorada, trozos de pechuga desmechada y queso fundido.', 24000, [
            $ingPapas->id => ['quantity_required' => 0.350],
            $ingSalchicha->id => ['quantity_required' => 0.150],
            $ingPollo->id => ['quantity_required' => 0.150],
            $ingQueso->id => ['quantity_required' => 0.080],
        ]);

        $p16 = $createDish($catComidas->id, 'Salchipapa Clásica Personal', 'Porción personal de papas a la francesa con salchicha frita y salsas al gusto.', 12000, [
            $ingPapas->id => ['quantity_required' => 0.250],
            $ingSalchicha->id => ['quantity_required' => 0.100],
            $ingAceite->id => ['quantity_required' => 0.050],
        ]);

        $p17 = $createDish($catComidas->id, 'Mazorcada Mixta Desgranada con Pollo', 'Maíz dulce tierno desgranado, pechuga de pollo, tocineta, queso gratinado y papas chip.', 22000, [
            $ingPollo->id => ['quantity_required' => 0.180],
            $ingQueso->id => ['quantity_required' => 0.080],
        ]);

        $p18 = $createDish($catComidas->id, 'Chuzo / Pincho de Pollo al Carbón con Arepa', 'Brocheta de pechuga y pimientos asados a la brasa con arepa y queso.', 12000, [
            $ingPollo->id => ['quantity_required' => 0.250],
            $ingArepas->id => ['quantity_required' => 1.0],
            $ingCarbon->id => ['quantity_required' => 0.100],
        ]);

        // --------------------------------------------------------------------------
        // CATEGORÍA 4: Sopas & Tradición Criolla (3 platos)
        // --------------------------------------------------------------------------
        $p19 = $createDish($catSopas->id, 'Consomé de Menudencias con Arroz y Arepa', 'Caldo caliente reconfortante con mollejas, pescuezo, arroz blanco y cilantro fresco.', 9000, [
            $ingMenudencias->id => ['quantity_required' => 0.200],
            $ingArroz->id => ['quantity_required' => 0.080],
            $ingArepas->id => ['quantity_required' => 1.0],
        ]);

        $p20 = $createDish($catSopas->id, 'Sancocho de Gallina Criolla Especial', 'Sancocho espeso tradicional cocinado en leña con mazorca, plátano, yuca y presa de gallina.', 20000, [
            $ingPollo->id => ['quantity_required' => 0.400],
            $ingPlatano->id => ['quantity_required' => 1.0],
            $ingYuca->id => ['quantity_required' => 0.200],
            $ingArroz->id => ['quantity_required' => 0.100],
        ]);

        $p21 = $createDish($catSopas->id, 'Arroz con Pollo Criollo Especial', 'Arroz desgranado con verduras frescas, pollo desmechado abundante y ensalada dulce.', 19000, [
            $ingPollo->id => ['quantity_required' => 0.250],
            $ingArroz->id => ['quantity_required' => 0.200],
            $ingPapas->id => ['quantity_required' => 0.150],
        ]);

        // --------------------------------------------------------------------------
        // CATEGORÍA 5: Acompañamientos & Porciones (6 platos)
        // --------------------------------------------------------------------------
        $p22 = $createDish($catAcomp->id, 'Porción de Papas a la Francesa Grande', 'Papas crujientes en bastón con sal sazonada.', 7000, [
            $ingPapas->id => ['quantity_required' => 0.350],
            $ingAceite->id => ['quantity_required' => 0.060],
        ]);

        $p23 = $createDish($catAcomp->id, 'Porción de Papa Criolla Frita', 'Papa criolla frita dorada por fuera y suave por dentro con sal de mar.', 8000, [
            $ingPapaCriolla->id => ['quantity_required' => 0.350],
            $ingAceite->id => ['quantity_required' => 0.060],
        ]);

        $p24 = $createDish($catAcomp->id, 'Porción de Yuca Frita con Suero', 'Trozos de yuca frita crocante con toque de suero costeño.', 6000, [
            $ingYuca->id => ['quantity_required' => 0.300],
            $ingAceite->id => ['quantity_required' => 0.050],
        ]);

        $p25 = $createDish($catAcomp->id, 'Porción de Arepas Asadas con Mantequilla x 4', '4 arepitas calientes con mantequilla dorada.', 4000, [
            $ingArepas->id => ['quantity_required' => 4.0],
        ]);

        $p26 = $createDish($catAcomp->id, 'Plátano Maduro Asado con Queso y Bocadillo', 'Plátano maduro al carbón relleno de queso fundido y dulce de guayaba.', 8000, [
            $ingPlatano->id => ['quantity_required' => 1.0],
            $ingQueso->id => ['quantity_required' => 0.060],
        ]);

        $p27 = $createDish($catAcomp->id, 'Ensalada Fresca de la Casa', 'Ensalada rallada de repollo morado, blanco, zanahoria y aderezo agridulce.', 5000);

        // --------------------------------------------------------------------------
        // CATEGORÍA 6: Bebidas Frías & Cervezas (7 bebidas)
        // --------------------------------------------------------------------------
        $p28 = $createDish($catBebidas->id, 'Gaseosa Postobón 1.5L Familiar', 'Gaseosa familiar (Manzana, Colombiana o Pepsi) bien helada.', 8000, [
            $ingGaseosa->id => ['quantity_required' => 1.0],
        ]);

        $p29 = $createDish($catBebidas->id, 'Gaseosa Personal 400ml', 'Botella personal Pet 400ml fría.', 4000);

        $p30 = $createDish($catBebidas->id, 'Jarra de Limonada Natural 1 Litro', 'Limonada recién exprimida con hielo picado.', 10000, [
            $ingLimon->id => ['quantity_required' => 0.300],
        ]);

        $p31 = $createDish($catBebidas->id, 'Jarra de Limonada de Coco 1 Litro', 'Refrescante limonada cremosa con leche de coco y azúcar morena.', 14000, [
            $ingLimon->id => ['quantity_required' => 0.300],
        ]);

        $p32 = $createDish($catBebidas->id, 'Cerveza Club Colombia Dorada 330ml', 'Cerveza premium bien fría.', 6000, [
            $ingCerveza->id => ['quantity_required' => 1.0],
        ]);

        $p33 = $createDish($catBebidas->id, 'Cerveza Águila Light 330ml', 'Cerveza ligera nacional.', 5000);

        $p34 = $createDish($catBebidas->id, 'Jugo Natural en Agua (Mora / Maracuyá / Mango)', 'Vaso de 16oz de fruta natural batida con hielo.', 6000);

        // --------------------------------------------------------------------------
        // 7. Simulación de Datos de Turno y Ventas de Hoy (Para el Dashboard del Dueño)
        // --------------------------------------------------------------------------
        $shift = CashShift::create([
            'tenant_id' => $tenant->id,
            'user_id' => $cajeroUser->id,
            'opened_at' => Carbon::today()->setTime(10, 0),
            'opening_amount' => 150000.00,
            'status' => 'open',
        ]);

        // Crear 3 órdenes pagadas hoy con diferentes métodos de pago y DIAN
        $createPaidOrder = function ($tableId, $orderNumber, $paymentMethod, $itemsData, $factusData = null) use ($tenant, $shift) {
            $subtotal = 0;
            foreach ($itemsData as $it) {
                $subtotal += ($it['product']->price * $it['qty']);
            }
            $tax = round($subtotal * 0.08);
            $total = $subtotal + $tax;

            $order = Order::create([
                'tenant_id' => $tenant->id,
                'restaurant_table_id' => $tableId,
                'order_number' => $orderNumber,
                'customer_name' => "Cliente Mesa {$tableId}",
                'customer_nit_cedula' => '1098765432',
                'customer_email' => 'cliente@correo.com',
                'type' => 'dine_in',
                'status' => 'paid',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'paid_at' => Carbon::now()->subMinutes(rand(10, 120)),
                'factus_bill_number' => $factusData['bill'] ?? null,
                'factus_cufe' => $factusData['cufe'] ?? null,
                'factus_qr_url' => $factusData['qr'] ?? null,
                'factus_status' => isset($factusData) ? 'sent_valid' : 'not_sent',
            ]);

            foreach ($itemsData as $it) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $it['product']->id,
                    'quantity' => $it['qty'],
                    'unit_price' => $it['product']->price,
                    'subtotal' => $it['product']->price * $it['qty'],
                    'kitchen_status' => 'ready',
                ]);
            }

            return $order;
        };

        // Orden 1: Pagada en Efectivo con DIAN Factus
        $createPaidOrder($createdTables[1]->id, 'ORD-HOY-001', 'cash', [
            ['product' => $p1, 'qty' => 1],
            ['product' => $p28, 'qty' => 1],
            ['product' => $p25, 'qty' => 1],
        ], [
            'bill' => 'SETP-990001',
            'cufe' => 'c8f39a02d8471b6e459021873491209384712039487120394812398471239847',
            'qr' => 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=SETP990001',
        ]);

        // Orden 2: Pagada con Tarjeta
        $createPaidOrder($createdTables[2]->id, 'ORD-HOY-002', 'card', [
            ['product' => $p6, 'qty' => 1],
            ['product' => $p32, 'qty' => 4],
        ]);

        // Orden 3: Pagada con Nequi / Transferencia
        $createPaidOrder($createdTables[3]->id, 'ORD-HOY-003', 'transfer', [
            ['product' => $p15, 'qty' => 2],
            ['product' => $p30, 'qty' => 1],
        ]);

        // Orden 4: Orden ACTIVA consumiendo en Mesa 4 (para prueba de caja)
        $table4 = $createdTables[4];
        $table4->update(['status' => 'occupied']);

        $activeOrder = Order::create([
            'tenant_id' => $tenant->id,
            'restaurant_table_id' => $table4->id,
            'order_number' => 'ORD-MESA-004',
            'customer_name' => 'Mesa 4',
            'customer_nit_cedula' => '222222222222',
            'customer_email' => 'mesa4@bigpollo.com',
            'type' => 'dine_in',
            'status' => 'pending',
            'subtotal' => 58000.00,
            'tax' => 4640.00,
            'total' => 62640.00,
        ]);

        OrderItem::create([
            'order_id' => $activeOrder->id,
            'product_id' => $p1->id, // Pollo Asado Entero
            'quantity' => 1,
            'unit_price' => 42000.00,
            'subtotal' => 42000.00,
            'kitchen_status' => 'cooking',
        ]);

        OrderItem::create([
            'order_id' => $activeOrder->id,
            'product_id' => $p8->id, // Combo 1/4 Broaster
            'quantity' => 1,
            'unit_price' => 16000.00,
            'subtotal' => 16000.00,
            'kitchen_status' => 'ready',
        ]);
    }
}
