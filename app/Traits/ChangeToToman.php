<?php


namespace App\Traits;


use App\Models\DollarPrice;

trait ChangeToToman
{
    protected function tetherToUsd()
    {
        return 1;
    }

    protected function usdToToman()
    {
        return cache()->remember(
            'tether-price',
            now()->addSeconds(30),
            function () {
                $d2 = DollarPrice::query()
                    ->latest()
                    ->value('price');

                $d1 = DollarPrice::query()
                    ->where('created_at', '>', now()->subDay())
                    ->value('price');

                return [
                    'changes' => $d2 - $d1,
                    'price' => $d2
                ];
            }
        );
    }

    public function toToman($price) {
        return $price * $this->tetherToUsd() * $this->usdToToman()['price'];
    }

    public function toTether($price) {
        return $price / ($this->tetherToUsd() * $this->usdToToman()['price']);
    }
}
