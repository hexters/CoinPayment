<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinpaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coinpayment_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('txn_id')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('amount')->nullable();
            $table->string('amountf')->nullable();
            $table->string('coin')->nullable();
            $table->integer('confirms_needed')->nullable();
            $table->string('payment_address')->nullable();
            $table->string('qrcode_url')->nullable();
            $table->string('received')->nullable();
            $table->string('receivedf')->nullable();
            $table->string('recv_confirms')->nullable();
            $table->string('status')->nullable();
            $table->string('status_text')->nullable();
            $table->string('status_url')->nullable();
            $table->string('timeout')->nullable();
            $table->string('type')->nullable();
            $table->text('payload')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('coinpayment_transactions');
    }
}
