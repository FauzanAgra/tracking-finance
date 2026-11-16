<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 30);
            $table->string('name', 150);
            $table->enum('asset_type', ['idx_stock', 'us_stock', 'crypto']);
            $table->string('currency', 10);
            $table->decimal('current_price', 18, 6)->default(0);
            $table->boolean('is_manual_price')->default(false);
            $table->timestamp('last_price_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'symbol', 'asset_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_assets');
    }
};
