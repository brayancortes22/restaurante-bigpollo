<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Cajero = 'cajero';
    case Mesero = 'mesero';
    case Cocina = 'cocina';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrador SaaS',
            self::Admin => 'Administrador de Restaurante',
            self::Cajero => 'Cajero / POS',
            self::Mesero => 'Mesero / Salonero',
            self::Cocina => 'Personal de Cocina / KDS',
        };
    }
}
