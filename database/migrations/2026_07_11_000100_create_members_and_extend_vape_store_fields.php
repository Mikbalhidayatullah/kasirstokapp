<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('storage_location')->nullable()->after('description');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('cashier_id')->constrained('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('storage_location');
        });

        Schema::dropIfExists('members');
    }
};
