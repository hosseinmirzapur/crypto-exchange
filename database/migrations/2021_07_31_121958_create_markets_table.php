<?php

use App\Models\Market;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('coin_id');
            $table->foreignId('quote_id');
            $table->string('max_amount')
                ->default(0);
            $table->string('min_amount')
                ->default(0);
            $table->string('custom_max_amount')
                ->default(0);
            $table->string('custom_min_amount')
                ->default(0);
            $table->string('amount_step')
                ->default(0);
            $table->string('min_notional')
                ->default(0);
            $table->enum('status', Market::STATUS);

            $table->foreign('coin_id')
                ->references('id')
                ->on('coins')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('quote_id')
                ->references('id')
                ->on('coins')
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
        Schema::dropIfExists('markets');
    }
}
