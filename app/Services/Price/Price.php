<?php


namespace App\Services\Price;


use App\Models\Market;
use App\Traits\ChangeToToman;
use Illuminate\Support\Str;

class Price
{

    use ChangeToToman;

    protected $price;
    protected $type;
    protected $market;
    private $fee;


    /**
     * Price constructor.
     * @param string $type BUY | SELL
     */
    public function __construct($type = 'BUY')
    {
        $this->type = Str::upper($type);
    }

    public function withFee(Fee $fee)
    {
        $this->fee = $fee;
        return $this;
    }

    public function withPrice($price)
    {
        $this->price = (float) $price;
        return $this;
    }

    public function withMarket(Market $market)
    {
        $this->market = $market;
        $this->price = $market
            ->price();

        return $this;
    }


    public function getPrice()
    {

        $factor = 1;

        if (isset($this->fee)) {
            $type = strtoupper($this->type) ?? 'BUY';
            $factor = $type === 'BUY' ? $this->buyFactor() : $this->sellFactor();
        }

        return $this->price * $factor;
    }

    public function getRoundedPrice($delimiter = 5)
    {
        return round($this->getPrice() * 10 ** $delimiter) / (10 ** $delimiter);
    }

    protected function buyFactor()
    {
        return 1 + $this->fee->fee();
    }

    protected function sellFactor()
    {
        return 1 - $this->fee->fee();
    }

    public function changeToToman()
    {
        if (method_exists($this, 'toToman')) {
            $this->price = $this->toToman($this->price);
        }
        return $this;
    }

    public function changeToTether() {
        $this->price = $this->toTether($this->price);
        return $this;
    }

}
