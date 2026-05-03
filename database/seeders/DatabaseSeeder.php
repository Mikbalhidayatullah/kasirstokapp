<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@kasirstok.test'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'stok@kasirstok.test'],
            [
                'name' => 'Petugas Stok',
                'password' => Hash::make('password'),
                'role' => UserRole::Stock,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'kasir@kasirstok.test'],
            [
                'name' => 'Kasir Utama',
                'password' => Hash::make('password'),
                'role' => UserRole::Cashier,
                'email_verified_at' => now(),
            ],
        );

        $minuman = Category::query()->updateOrCreate(
            ['name' => 'Minuman'],
            ['description' => 'Produk minuman dingin dan hangat.'],
        );

        $makanan = Category::query()->updateOrCreate(
            ['name' => 'Makanan Ringan'],
            ['description' => 'Snack cepat saji untuk area kasir.'],
        );

        Product::query()->updateOrCreate(
            ['sku' => 'MNM-001'],
            [
                'category_id' => $minuman->id,
                'barcode' => '8991000000011',
                'name' => 'Es Teh Manis',
                'description' => 'Minuman segar siap jual.',
                'unit' => 'gelas',
                'cost_price' => 3000,
                'sale_price' => 6000,
                'stock' => 48,
                'minimum_stock' => 10,
                'is_active' => true,
            ],
        );

        Product::query()->updateOrCreate(
            ['sku' => 'MKN-001'],
            [
                'category_id' => $makanan->id,
                'barcode' => '8991000000028',
                'name' => 'Keripik Singkong',
                'description' => 'Snack kemasan untuk etalase depan.',
                'unit' => 'bungkus',
                'cost_price' => 5500,
                'sale_price' => 9000,
                'stock' => 32,
                'minimum_stock' => 8,
                'is_active' => true,
            ],
        );
    }
}
