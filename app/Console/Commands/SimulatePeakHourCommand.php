<?php

namespace App\Console\Commands;

use App\Actions\Cash\OpenCashShiftAction;
use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\UpdateOrderStatusAction;
use App\DTOs\CreateOrderDTO;
use App\DTOs\OrderItemDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CashShift;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;

class SimulatePeakHourCommand extends Command
{
    protected $signature = 'simulate:peak-hour';
    protected $description = 'Simula una ráfaga de pedidos en hora pico con mesas ocupadas, cocina KDS y caja';

    public function handle(
        OpenCashShiftAction $openShift,
        CreateOrderAction $createOrder,
        UpdateOrderStatusAction $updateStatus
    ): int {
        $this->info('🚀 Iniciando Simulación de Hora Pico para Restaurante Big Pollo...');

        $tenant = Tenant::first();
        $user = User::first();

        // 1. Apertura de caja si está cerrada
        if (!CashShift::where('tenant_id', $tenant->id)->where('status', 'open')->exists()) {
            $openShift->execute($tenant->id, $user->id, 250000.0, 'Base de apertura automática hora pico');
            $this->info('💵 Turno de Caja aperturado con base de $250.000 COP.');
        }

        $tables = RestaurantTable::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)->get();

        if ($tables->isEmpty() || $products->isEmpty()) {
            $this->error('Faltan mesas o productos para simular.');
            return 1;
        }

        $diners = ['Juan Pablo Montoya', 'Sofía Vergara', 'Falcao García', 'Karol G', 'Carlos Vives'];

        foreach ([0, 1, 2, 3, 4] as $i) {
            if (!isset($tables[$i])) continue;
            $table = $tables[$i];

            $dto = new CreateOrderDTO(
                tenantId: $tenant->id,
                tableId: $table->id,
                userId: $user->id,
                type: OrderType::DineIn,
                items: [
                    new OrderItemDTO(productId: $products[$i % $products->count()]->id, quantity: 2, notes: 'Bien crocante y doradito'),
                    new OrderItemDTO(productId: $products[($i + 1) % $products->count()]->id, quantity: 1, notes: 'Sin salsas')
                ],
                customerName: $diners[$i],
                customerNitCedula: '10987654' . $i,
                customerConsent: true
            );

            $order = $createOrder->execute($dto);
            $this->line("🍗 Mesa {$table->table_number}: Comanda #{$order->order_number} creada por {$diners[$i]} (Total: \${$order->total}).");

            // Escalonar estados para la demostración visual
            if ($i === 0) {
                $updateStatus->execute($order, OrderStatus::Ready);
                $this->info("   ↳ ¡Comanda #{$order->order_number} en estado LISTO PARA SERVIR!");
            } elseif ($i === 1 || $i === 2) {
                $updateStatus->execute($order, OrderStatus::InKitchen);
                $this->warn("   ↳ Comanda #{$order->order_number} en estado EN PREPARACIÓN en Cocina KDS.");
            } else {
                $this->comment("   ↳ Comanda #{$order->order_number} en estado PENDIENTE en Cocina KDS.");
            }
        }

        $this->info('🎉 ¡Simulación completada con éxito! Revisa las pantallas en tu navegador.');
        return 0;
    }
}
