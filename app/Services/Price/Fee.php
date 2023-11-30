<?php


namespace App\Services\Price;


use App\Models\Coin;
use function App\Helpers\current_user;

class Fee
{
    protected $fee = 0;
    private $coin;

    public function __construct(array $allowable_fees, Coin $coin = null)
    {

        $this->coin = $coin;
        if (!empty($allowable_fees)) {
            foreach ($allowable_fees as $fee) {
                $method = 'with'.ucwords($fee).'Fee';
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
        }
    }

    public function fee()
    {
        return $this->fee;
    }

    protected function withConstantFee()
    {
        $this->fee += $this->coin->constant_fee ?? 0;

        return $this;
    }

    protected function withRankFee()
    {
        $this->fee +=  current_user()->rank->fee ?? 0.003;
        return $this;
    }

    protected function withBinanceFee()
    {
        $this->fee += 0.001;
        return $this;
    }

}
