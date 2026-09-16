<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TableStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FactusInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Restaurante Big Pollo Test',
            'slug' => 'big-pollo-test',
            'nit' => '900.123.456-1',
            'email' => 'facturacion@testpollo.com',
            'address' => 'Calle 100 # 15-20',
            'currency' => 'COP',
            'tax_percentage' => 8.00,
            'suggested_tip_percentage' => 10.00,
            'is_active' => true,
        ]);

        $table = RestaurantTable::create([
            'tenant_id' => $this->tenant->id,
            'table_number' => 'Mesa 1',
            'capacity' => 4,
            'status' => TableStatus::Occupied->value,
        ]);

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Combos Especiales',
            'slug' => 'combos-especiales',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Combo Familiar Big Pollo',
            'price' => 50000.00,
            'tax_percentage' => 8.00,
            'is_available' => true,
        ]);

        $this->order = Order::create([
            'tenant_id' => $this->tenant->id,
            'restaurant_table_id' => $table->id,
            'order_number' => 'ORD-20260915-0001',
            'type' => OrderType::DineIn->value,
            'status' => OrderStatus::Paid->value,
            'customer_name' => 'Comensal Feliz',
            'customer_nit_cedula' => '1010202030',
            'customer_email' => 'comensal@gmail.com',
            'customer_phone' => '3109876543',
            'subtotal' => 50000.00,
            'tax' => 4000.00,
            'total' => 54000.00,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50000.00,
            'subtotal' => 50000.00,
            'kitchen_status' => 'ready',
        ]);
    }

    public function test_can_emit_electronic_invoice_via_factus_api(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'token_type' => 'Bearer',
                'access_token' => 'fake_oauth_token_12345',
                'expires_in' => 3600,
            ], 200),
            '*/v1/bills/validate' => Http::response([
                'status' => 'Created',
                'message' => 'Factura creada y validada por la DIAN',
                'data' => [
                    'bill' => [
                        'number' => 'SETP-9900001',
                        'cufe' => '7d9a8c12e34fa56b78cd90ef123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef',
                        'qr' => 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=7d9a8c12e34f',
                        'public_url' => 'https://api-sandbox.factus.com.co/v1/bills/SETP-9900001/pdf',
                        'status' => 'validada_dian',
                    ],
                ],
            ], 201),
        ]);

        $response = $this->postJson("/api/orders/{$this->order->id}/invoice");

        $response->assertStatus(200)
            ->assertJsonPath('data.electronic_invoice.status', 'sent_valid')
            ->assertJsonPath('data.electronic_invoice.bill_number', 'SETP-9900001')
            ->assertJsonPath('data.electronic_invoice.cufe', '7d9a8c12e34fa56b78cd90ef123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef')
            ->assertJsonPath('data.electronic_invoice.qr_url', 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=7d9a8c12e34f');

        $this->assertEquals('sent_valid', $this->order->fresh()->factus_status);
        $this->assertEquals('SETP-9900001', $this->order->fresh()->factus_bill_number);
    }

    public function test_handles_factus_api_error_without_crashing(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'fake_oauth_token_12345',
            ], 200),
            '*/v1/bills/validate' => Http::response([
                'message' => 'Error de validación DIAN: NIT de emisor no habilitado en rango',
            ], 422),
        ]);

        $response = $this->postJson("/api/orders/{$this->order->id}/invoice");

        $response->assertStatus(200)
            ->assertJsonPath('data.electronic_invoice.status', 'error')
            ->assertJsonPath('data.electronic_invoice.error_message', 'Error de validación DIAN: NIT de emisor no habilitado en rango');

        $this->assertEquals('error', $this->order->fresh()->factus_status);
    }
}
