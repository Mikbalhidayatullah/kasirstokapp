<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Qris => 'QRIS',
            self::BankTransfer => 'Rekening',
        };
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::Cash => 'warning',
            self::Qris => 'success',
            self::BankTransfer => 'dark',
        };
    }

    public function requiresExactPayment(): bool
    {
        return $this !== self::Cash;
    }
}
