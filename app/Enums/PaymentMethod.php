<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Card => 'Tarjeta Débito/Crédito',
            self::Transfer => 'Transferencia (Nequi/Daviplata/Bancolombia)',
            self::Mixed => 'Pago Mixto',
        };
    }
}
