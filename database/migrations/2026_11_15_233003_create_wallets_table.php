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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['bank', 'cash', 'e_wallet', 'investment_cash']);
            $table->string('currency', 10)->default('IDR');
            $table->decimal('balance', 18, 4)->default(0);
            $table->string('account_number', 50)->nullable();
            $table->string('color', 20)->default('#3B82F6');
            $table->string('icon', 50)->default('wallet');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
