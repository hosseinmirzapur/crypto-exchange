<?php

namespace App\Rules;

use App\Models\Market;
use Illuminate\Contracts\Validation\Rule;
use function App\Helpers\hasRemain;
use function App\Helpers\prettifyNumber;

class OrderAmountRule implements Rule
{
    protected $market;
    protected $message = '';

    /**
     * Create a new rule instance.
     *
     * @param Market $market
     * @param $type
     */
    public function __construct(Market $market, $type)
    {
        $this->market = $market;
        $this->type = $type;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!isset($this->market)) {
            return true;
        }
        // max
        $maxAmountLimit = $this->market->findMaxLimit();
        if ($maxAmountLimit > 0 && $value > $maxAmountLimit) {
            $this->message = trans('validation.max.numeric', ['max' =>   prettifyNumber($maxAmountLimit, 0)]); // 'MAX_AMOUNT_LIMITATION'
            return false;
        }

        // min

        $minAmountLimit = $this->market->findMinLimit();
        if ($minAmountLimit > 0 && $value <= $minAmountLimit) {
            $this->message = trans('validation.min.numeric', ['min' => prettifyNumber($minAmountLimit)]); // 'Min_AMOUNT_LIMITATION:' . $minAmountLimit;
            return false;
        }
        if (($value * ($this->market->price ?? 0) < $this->market->min_notional) && $this->market->min_notional) {
            $this->message = trans('validation.min.numeric',
                [
                    'min' => prettifyNumber(
                        !empty($this->market->price) ?
                            $this->market->min_notional / ($this->market->price) :
                            0, 1)
                ]
            );
            return false;
        }

        // step
        if ( ($this->market->amount_step > 0) &&  hasRemain($value , $this->market->amount_step)) {
            $this->message = trans('validation.step', ['min' => $this->market->amount_step]);
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
