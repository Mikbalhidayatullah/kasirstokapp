<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'member_only',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'member_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'fixed') {
            return min($subtotal, (float) $this->discount_value);
        }

        return min($subtotal, $subtotal * ((float) $this->discount_value / 100));
    }

    public function label(): string
    {
        if ($this->discount_type === 'fixed') {
            return 'Rp '.number_format((float) $this->discount_value, 0, ',', '.');
        }

        return rtrim(rtrim(number_format((float) $this->discount_value, 2, ',', '.'), '0'), ',').'%';
    }
}
