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
        Schema::create('outbond_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->references('id');
            $table->foreignId('user_id')->constrained('users')->references('id');
            $table->date('transactions_date');
            $table->string('expedition')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbond_transactions');
    }
};
