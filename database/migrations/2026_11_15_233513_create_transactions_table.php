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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['income', 'expense', 'transfer', 'currency_exchange']);
            $table->decimal('amount', 18, 4);
            $table->decimal('destination_amount', 18, 4)->nullable();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            $table->decimal('fee', 15, 2)->default(0);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->string('attachment_path', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
