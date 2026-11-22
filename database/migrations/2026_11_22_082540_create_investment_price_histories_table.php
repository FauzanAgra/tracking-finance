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
        Schema::create('investment_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 18, 8);
            $table->date('recorded_date');
            $table->timestamps();

            $table->unique(['investment_asset_id', 'recorded_date'], 'inv_price_hist_asset_date_unique');
            $table->index(['investment_asset_id', 'recorded_date'], 'inv_price_hist_asset_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_price_histories');
    }
};
