<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedInteger('points_balance')->default(0)->after('notes');
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('discount_type')->default('percentage');
            $table->decimal('discount_value', 12, 2);
            $table->boolean('member_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('point_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('points_cost');
            $table->decimal('discount_amount', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('member_id')->constrained('promotions')->nullOnDelete();
            $table->foreignId('point_reward_id')->nullable()->after('promotion_id')->constrained('point_rewards')->nullOnDelete();
            $table->decimal('promo_discount_amount', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('point_discount_amount', 12, 2)->default(0)->after('promo_discount_amount');
            $table->unsignedInteger('points_earned')->default(0)->after('change_amount');
            $table->unsignedInteger('points_redeemed')->default(0)->after('points_earned');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropConstrainedForeignId('point_reward_id');
            $table->dropColumn([
                'promo_discount_amount',
                'point_discount_amount',
                'points_earned',
                'points_redeemed',
            ]);
        });

        Schema::dropIfExists('point_rewards');
        Schema::dropIfExists('promotions');

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('points_balance');
        });
    }
};
