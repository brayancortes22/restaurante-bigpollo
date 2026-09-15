<?php

namespace App\DTOs;

readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity = 1,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            quantity: (int) ($data['quantity'] ?? 1),
            notes: $data['notes'] ?? null,
        );
    }
}
