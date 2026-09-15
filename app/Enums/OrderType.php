<?php

namespace App\Enums;

enum OrderType: string
{
    case DineIn = 'dine_in';
    case Takeout = 'takeout';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'En Mesa',
            self::Takeout => 'Para Llevar',
            self::Delivery => 'Domicilio',
        };
    }
}
