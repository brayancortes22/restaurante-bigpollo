<?php

namespace App\DTOs;

use App\Enums\OrderType;

readonly class CreateOrderDTO
{
    /**
     * @param array<OrderItemDTO> $items
     */
    public function __construct(
        public int $tenantId,
        public ?int $tableId,
        public ?int $userId,
        public OrderType $type,
        public array $items,
        public ?string $customerName = null,
        public ?string $customerNitCedula = null,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
        public ?string $deliveryAddress = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn(array $item) => OrderItemDTO::fromArray($item),
            $data['items'] ?? []
        );

        $orderType = isset($data['type']) 
            ? ($data['type'] instanceof OrderType ? $data['type'] : OrderType::from($data['type']))
            : OrderType::DineIn;

        return new self(
            tenantId: (int) $data['tenant_id'],
            tableId: isset($data['restaurant_table_id']) ? (int) $data['restaurant_table_id'] : null,
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            type: $orderType,
            items: $items,
            customerName: $data['customer_name'] ?? null,
            customerNitCedula: $data['customer_nit_cedula'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
