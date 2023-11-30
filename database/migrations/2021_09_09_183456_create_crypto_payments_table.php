<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_network_id')
                ->nullable();
            $table->string('address')
                ->nullable();
            $table->string('memo')
                ->nullable();
            $table->string('tag')
                ->nullable();
            $table->string('tx_id')
                ->nullable();
            $table->string('commission')
                ->nullable();
            $table->string('withdraw_id')
                ->nullable();
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
        Schema::dropIfExists('crypto_payments');
    }
}
