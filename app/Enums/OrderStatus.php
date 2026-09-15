<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case InKitchen = 'in_kitchen';
    case Ready = 'ready';
    case Served = 'served';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InKitchen => 'En Preparación',
            self::Ready => 'Listo para Servir',
            self::Served => 'Servido en Mesa',
            self::Paid => 'Pagado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function isKitchenVisible(): bool
    {
        return in_array($this, [self::Pending, self::InKitchen]);
    }
}
