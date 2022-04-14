<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->double('txn_amount');
            $table->string('txn_title')->default("Money added to wallet");
            $table->string('txn_id');
            $table->string('txn_message');
            $table->string('txn_mode')->default("PAYU");
            $table->string('txn_status')->default("PENDING");
            $table->string('payu_money_id')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
