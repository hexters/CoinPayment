<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinpaymentTransactionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coinpayment_transaction_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coinpayment_transaction_id');
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coinpayment_transaction_items');
    }
}
