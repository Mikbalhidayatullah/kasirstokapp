<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Member;
use App\Models\PointReward;
use App\Models\Product;
use App\Models\Promotion;
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

        $liquid = Category::query()->updateOrCreate(
            ['name' => 'Liquid'],
            ['description' => 'Liquid freebase, salt nic, dan varian rasa.'],
        );

        $device = Category::query()->updateOrCreate(
            ['name' => 'Device & Pod'],
            ['description' => 'Device, pod kit, cartridge, dan aksesoris utama.'],
        );

        $sparepart = Category::query()->updateOrCreate(
            ['name' => 'Coil & Sparepart'],
            ['description' => 'Coil, cartridge pengganti, kapas, baterai, dan sparepart.'],
        );

        Product::query()->updateOrCreate(
            ['barcode' => '8991000000011'],
            [
                'sku' => 'LQD-001',
                'category_id' => $liquid->id,
                'name' => 'Liquid Mango Ice 30ml',
                'description' => 'Freebase 3mg, rasa mangga dingin.',
                'storage_location' => 'Etalase A1 - Liquid Freebase',
                'unit' => 'botol',
                'cost_price' => 45000,
                'sale_price' => 65000,
                'stock' => 48,
                'minimum_stock' => 10,
                'is_active' => true,
            ],
        );

        Product::query()->updateOrCreate(
            ['barcode' => '8991000000028'],
            [
                'sku' => 'POD-001',
                'category_id' => $device->id,
                'name' => 'Pod Kit Compact 1000mAh',
                'description' => 'Device pod ringkas untuk pemakaian harian.',
                'storage_location' => 'Display Device B1',
                'unit' => 'pcs',
                'cost_price' => 135000,
                'sale_price' => 185000,
                'stock' => 12,
                'minimum_stock' => 3,
                'is_active' => true,
            ],
        );

        Product::query()->updateOrCreate(
            ['barcode' => '8991000000035'],
            [
                'sku' => 'COIL-001',
                'category_id' => $sparepart->id,
                'name' => 'Coil Mesh 0.8 Ohm',
                'description' => 'Coil pengganti untuk pod MTL.',
                'storage_location' => 'Laci Coil C2',
                'unit' => 'pcs',
                'cost_price' => 22000,
                'sale_price' => 35000,
                'stock' => 28,
                'minimum_stock' => 6,
                'is_active' => true,
            ],
        );

        Member::query()->updateOrCreate(
            ['phone_number' => '081234567890'],
            [
                'name' => 'Rizky Vapor',
                'birth_date' => '1998-08-17',
                'notes' => 'Sering beli liquid fruity dingin dan coil 0.8 ohm.',
                'points_balance' => 75,
                'is_active' => true,
            ],
        );

        Promotion::query()->updateOrCreate(
            ['name' => 'Member Weekend 10%'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'member_only' => true,
                'is_active' => true,
                'notes' => 'Contoh promo event akhir pekan khusus member.',
            ],
        );

        Promotion::query()->updateOrCreate(
            ['name' => 'Flash Sale Device 25K'],
            [
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'member_only' => false,
                'is_active' => false,
                'notes' => 'Aktifkan manual saat ada event device.',
            ],
        );

        PointReward::query()->updateOrCreate(
            ['name' => 'Tukar 50 Poin'],
            [
                'points_cost' => 50,
                'discount_amount' => 10000,
                'is_active' => true,
                'notes' => 'Reward dasar untuk member reguler.',
            ],
        );

        PointReward::query()->updateOrCreate(
            ['name' => 'Tukar 100 Poin'],
            [
                'points_cost' => 100,
                'discount_amount' => 25000,
                'is_active' => true,
                'notes' => 'Reward lebih besar untuk pelanggan loyal.',
            ],
        );
    }
}
