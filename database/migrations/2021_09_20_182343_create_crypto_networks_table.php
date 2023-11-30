<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coin_id');
            $table->string('name');
            $table->string('network');
            $table->string('withdraw_fee');
            $table->string('withdraw_min');
            $table->string('withdraw_max');
            $table->string('regex');
            $table->string('memo_regex');
            $table->integer('min_confirm')
                ->default(0);
            $table->enum('withdraw_status', ['ACTIVATED', 'DISABLED']);
            $table->enum('deposit_status', ['ACTIVATED', 'DISABLED']);
            $table->enum('status', ['ACTIVATED', 'DISABLED'])->default('DISABLED');
            $table->string('address')->nullable();
            $table->string('memo')->nullable();

            $table->foreign('coin_id')
                ->references('id')
                ->on('coins')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crypto_networks');
    }
}
