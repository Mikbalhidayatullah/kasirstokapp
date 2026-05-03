<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Stock = 'stock';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Stock => 'Petugas Stok',
            self::Cashier => 'Kasir',
        };
    }

    public function canAccessStock(): bool
    {
        return in_array($this, [self::Admin, self::Stock], true);
    }

    public function canAccessCashier(): bool
    {
        return in_array($this, [self::Admin, self::Cashier], true);
    }
}
