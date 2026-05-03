<?php

namespace App\Enums;

enum StockMovementType: string
{
    case StockIn = 'stock_in';
    case Sale = 'sale';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::StockIn => 'Stok Masuk',
            self::Sale => 'Penjualan',
            self::Adjustment => 'Penyesuaian',
        };
    }
}
