<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashShiftTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Restaurante Big Pollo Test',
            'slug' => 'big-pollo-test',
            'nit' => '900.111.222-3',
            'email' => 'caja@testpollo.com',
            'address' => 'Avenida Principal 123',
            'currency' => 'COP',
            'tax_percentage' => 8.00,
            'suggested_tip_percentage' => 10.00,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cajero Turno Manana',
            'email' => 'cajero@bigpollo.com',
            'password' => bcrypt('secret123'),
            'role' => 'cajero',
        ]);
    }

    public function test_can_open_cash_shift(): void
    {
        $response = $this->postJson('/api/cash-shifts/open', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 150000.00,
            'notes' => 'Apertura con billetes de 10k y 20k',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('shift.status', 'open')
            ->assertJsonPath('shift.opening_amount', '150000.00')
            ->assertJsonPath('shift.expected_cash', '150000.00');

        $this->assertDatabaseHas('cash_shifts', [
            'tenant_id' => $this->tenant->id,
            'status' => 'open',
            'opening_amount' => 150000.00,
        ]);
    }

    public function test_can_get_current_open_shift(): void
    {
        CashShift::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opened_at' => now(),
            'opening_amount' => 200000.00,
            'expected_cash' => 200000.00,
            'status' => 'open',
        ]);

        $response = $this->getJson('/api/cash-shifts/current?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
            ->assertJsonPath('is_open', true)
            ->assertJsonPath('shift.opening_amount', '200000.00')
            ->assertJsonPath('shift.user.name', 'Cajero Turno Manana');
    }

    public function test_can_close_shift_and_perform_accurate_arqueo_z(): void
    {
        // 1. Abrir caja con base de $100.000
        $shift = CashShift::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opened_at' => now()->subHours(4),
            'opening_amount' => 100000.00,
            'expected_cash' => 100000.00,
            'status' => 'open',
        ]);

        // 2. Simular venta pagada en efectivo de $50.000
        Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-TEST-001',
            'type' => OrderType::DineIn->value,
            'status' => OrderStatus::Paid->value,
            'payment_method' => 'cash',
            'subtotal' => 46296.30,
            'tax' => 3703.70,
            'total' => 50000.00,
            'created_at' => now()->subHours(2),
        ]);

        // 3. Simular venta pagada por transferencia Nequi de $35.000
        Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-TEST-002',
            'type' => OrderType::DineIn->value,
            'status' => OrderStatus::Paid->value,
            'payment_method' => 'transfer',
            'subtotal' => 32407.41,
            'tax' => 2592.59,
            'total' => 35000.00,
            'created_at' => now()->subHours(1),
        ]);

        // 4. Cerrar turno con conteo físico en efectivo de $150.000 ($100k base + $50k ventas)
        $response = $this->postJson('/api/cash-shifts/close', [
            'tenant_id' => $this->tenant->id,
            'actual_cash_counted' => 150000.00,
            'notes' => 'Arqueo Z cuadrado exacto sin descuadre',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('shift.status', 'closed')
            ->assertJsonPath('shift.cash_sales', '50000.00')
            ->assertJsonPath('shift.transfer_sales', '35000.00')
            ->assertJsonPath('shift.total_sales', '85000.00')
            ->assertJsonPath('shift.expected_cash', '150000.00')
            ->assertJsonPath('shift.actual_cash_counted', '150000.00')
            ->assertJsonPath('shift.difference', '0.00');

        $this->assertEquals('closed', $shift->fresh()->status);
        $this->assertNotNull($shift->fresh()->closed_at);
    }
}
