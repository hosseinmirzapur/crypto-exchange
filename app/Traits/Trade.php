<?php


namespace App\Traits;


trait Trade
{
    public function trades()
    {
        return $this->hasMany(\App\Models\Trade::class);
    }

    public function createTrade()
    {
        //todo profit
        return $this->trades()
            ->create([
                'amount' => $this->amount,
                'price' => $this->price,
                'fee' => $this->fee,
                'type' => $this->type
            ]);
    }
}
