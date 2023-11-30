<?php

namespace App\Http\Requests;

use App\Models\Market;
use App\Rules\OrderAmountRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->market = Market::findOrFail($this->market_id);

        if ($this->market->quote->isToman) {
            $this->usdtQuoteMarket = Market::where('coin_id', $this->market->coin_id)
                ->whereHas('quote', function ($query) {
                    $query->where('code', 'USDT');
                })
                ->first();
        }

        return [
            'market_id' => ['required'],
            'type' => ['required', Rule::in(['BUY', 'SELL'])],
            'amount' => [
                'required',
                new OrderAmountRule($this->market, $this->type)
            ],
        ];
    }
}
