<?php

use App\Models\Transaction;
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
            $table->foreignId('user_id');
            $table->foreignId('order_id')
                ->nullable();
            $table->string("payment_type")
                ->nullable();
            $table->unsignedBigInteger("payment_id")
                ->nullable();
            $table->foreignId('coin_id')
                ->nullable();
            $table->enum('status', Transaction::STATUS)
                ->default('PENDING');
            $table->decimal('amount', 27, 18)
                ->nullable();
            $table->enum('type', ['DEPOSIT', 'WITHDRAW']);
            $table->enum('payment_method', Transaction::PAYMENT_METHOD)
                ->nullable();
            $table->foreignId('account_id')
                ->nullable();
            $table->string('api_status')
                ->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
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
