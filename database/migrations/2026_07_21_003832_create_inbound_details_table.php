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
        Schema::create('inbound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained('inbound_transactions')->references('id');
            $table->foreignId('product_id')->constrained('products')->references('id');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_details');
    }
};
