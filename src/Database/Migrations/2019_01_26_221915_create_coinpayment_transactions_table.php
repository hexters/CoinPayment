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
            $table->uuid('uuid')->unique();
            $table->string('txn_id')->unique()->nullable();
            
            $table->string('order_id')->unique()->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('time_expires')->nullable();

            $table->string('address')->nullable();
            $table->decimal('amount_total_fiat', 10, 2)->nullable();
            $table->string('amount')->nullable();
            $table->string('amountf')->nullable();
            $table->string('coin')->nullable();
            $table->integer('confirms_needed')->nullable();
            $table->string('payment_address')->nullable();
            $table->text('qrcode_url')->nullable();
            $table->string('received')->nullable();
            $table->string('receivedf')->nullable();
            $table->string('recv_confirms')->nullable();
            $table->string('status')->nullable();
            $table->string('status_text')->nullable();
            $table->text('status_url')->nullable();
            $table->string('timeout')->nullable();
            
            $table->longText('checkout_url')->nullable();
            $table->longText('redirect_url')->nullable();
            $table->longText('cancel_url')->nullable();

            $table->string('type')->nullable();
            $table->longText('payload')->nullable();
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
