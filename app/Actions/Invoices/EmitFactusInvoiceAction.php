<?php

namespace App\Actions\Invoices;

use App\Models\Order;
use App\Services\Factus\FactusClient;
use RuntimeException;

class EmitFactusInvoiceAction
{
    public function __construct(
        private readonly FactusClient $factusClient
    ) {}

    public function execute(Order $order): Order
    {
        $order->loadMissing(['items.product', 'tenant']);

        // 1. Preparar datos del adquirente (cliente o consumidor final según estándar DIAN)
        $customer = [
            'identification' => $order->customer_nit_cedula ?? '222222222222', // Consumidor Final DIAN
            'names' => $order->customer_name ?? 'Consumidor Final',
            'email' => $order->customer_email ?? ($order->tenant?->email ?? 'facturacion@bigpollo.com'),
            'phone' => $order->customer_phone ?? '3000000000',
            'legal_organization_id' => '2', // Persona Natural
            'tribute_id' => '21', // No responsable de IVA
            'identification_document_id' => '3', // Cédula de ciudadanía
        ];

        // 2. Mapear líneas de la factura
        $items = [];
        foreach ($order->items as $item) {
            $taxRate = $item->product?->tax_percentage ?? 8.00;
            $items[] = [
                'code_reference' => (string) $item->product_id,
                'name' => $item->product?->name ?? 'Producto de Restaurante',
                'quantity' => (int) $item->quantity,
                'price_amount' => (float) $item->unit_price,
                'tax_rate' => number_format((float) $taxRate, 2, '.', ''),
                'unit_measure_id' => '70', // Unidad estándar DIAN
                'standard_code_id' => '1',
                'is_excluded' => 0,
                'tribute_id' => '4', // Impuesto Nacional al Consumo (INC / Restaurantes)
            ];
        }

        // 3. Estructurar payload de Factus
        $payload = [
            'numbering_range_id' => 8, // Rango de habilitación en sandbox
            'reference_code' => $order->order_number,
            'observation' => 'Factura electrónica generada para Restaurante Big Pollo - Comanda ' . $order->order_number,
            'payment_method_code' => '10', // Efectivo / Inmediato
            'customer' => $customer,
            'items' => $items,
        ];

        // 4. Invocar API de Factus
        $result = $this->factusClient->emitBill($payload);

        if ($result['success']) {
            $order->update([
                'factus_bill_number' => $result['bill_number'],
                'factus_cufe' => $result['cufe'],
                'factus_qr_url' => $result['qr_url'],
                'factus_status' => 'sent_valid',
                'factus_error_message' => null,
            ]);
        } else {
            $order->update([
                'factus_status' => 'error',
                'factus_error_message' => $result['error'],
            ]);
        }

        return $order->fresh();
    }
}
