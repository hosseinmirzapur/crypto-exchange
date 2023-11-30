<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'transactionable_id' => 1,
            'transactionable_type' => User::class,
            'payment_method' =>  Arr::random(Transaction::PAYMENT_METHOD, 1)[0],
            'amount' => $this->faker->numberBetween(100000,100000000),
            'status' =>  Arr::random(Transaction::STATUS, 1)[0],
            'fee' => 0,
            'type' =>  Arr::random(Transaction::TYPE, 1)[0],
            'created_at' => $this->faker->dateTimeBetween('-1years'),
            'updated_at' => $this->faker->dateTimeBetween('-1years')
        ];
    }
}
