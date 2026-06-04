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
        Schema::create('coinpayment_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coinpayment_transaction_id')->index();
            $table->string('description');
            $table->decimal('price', 10, 2);
            $table->decimal('qty', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('currency_code')->nullable();
            $table->string('type')->nullable();
            $table->string('state')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coinpayment_transaction_items');
    }
};
