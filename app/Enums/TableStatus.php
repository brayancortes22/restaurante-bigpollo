<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Billed = 'billed';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Occupied => 'Ocupada',
            self::Billed => 'Por Cobrar',
            self::Reserved => 'Reservada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'green',
            self::Occupied => 'red',
            self::Billed => 'yellow',
            self::Reserved => 'blue',
        };
    }
}
