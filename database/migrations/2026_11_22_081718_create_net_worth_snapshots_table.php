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
        Schema::create('net_worth_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('net_worth_idr', 16, 4)->default(0);
            $table->decimal('cash_idr', 16, 4)->default(0);
            $table->decimal('investment_idr', 16, 4)->default(0);
            $table->decimal('receivable_idr', 16, 4)->default(0);
            $table->decimal('payable_idr', 16, 4)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_date']);
            $table->index(['user_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('net_worth_snapshots');
    }
};
