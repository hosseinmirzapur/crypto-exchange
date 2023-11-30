<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('market_id');
            $table->enum('type', ['BUY', 'SELL']);
            $table->enum('payment_method', Order::PAYMENT_METHODS)
                ->nullable();
            $table->unsignedDecimal('fee', 6, 5)
                ->nullable();
            $table->decimal('amount', 27, 18)
                ->nullable();
            $table->decimal('price', 15, 4)
                ->nullable();
            $table->string('bill_number')
                ->nullable();
            $table->string('binance_order_id')
                ->nullable();
            $table->enum('status', Order::STATUS)
                ->default('PENDING');
            $table->enum('api_status', Order::API_STATUS)
                ->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('market_id')
                ->references('id')
                ->on('markets')
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
        Schema::dropIfExists('orders');
    }
}
