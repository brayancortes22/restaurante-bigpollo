<?php

namespace App\Http\Requests\Orders;

use App\DTOs\CreateOrderDTO;
use App\DTOs\OrderItemDTO;
use App\Enums\OrderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_table_id' => ['nullable', 'integer', 'exists:restaurant_tables,id'],
            'type' => ['required', Rule::enum(OrderType::class)],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_nit_cedula' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'customer_consent' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(int $tenantId, ?int $userId = null): CreateOrderDTO
    {
        $validated = $this->validated();

        $items = array_map(
            fn(array $item) => new OrderItemDTO(
                productId: (int) $item['product_id'],
                quantity: (int) $item['quantity'],
                notes: $item['notes'] ?? null
            ),
            $validated['items']
        );

        return new CreateOrderDTO(
            tenantId: $tenantId,
            tableId: isset($validated['restaurant_table_id']) ? (int) $validated['restaurant_table_id'] : null,
            userId: $userId,
            type: OrderType::from($validated['type']),
            items: $items,
            customerName: $validated['customer_name'] ?? null,
            customerNitCedula: $validated['customer_nit_cedula'] ?? null,
            customerEmail: $validated['customer_email'] ?? null,
            customerPhone: $validated['customer_phone'] ?? null,
            deliveryAddress: $validated['delivery_address'] ?? null,
            notes: $validated['notes'] ?? null,
            customerConsent: (bool) ($validated['customer_consent'] ?? false),
            consentIp: $this->ip(),
        );
    }
}
