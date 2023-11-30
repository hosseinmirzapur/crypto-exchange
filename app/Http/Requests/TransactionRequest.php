<?php

namespace App\Http\Requests;

use App\Models\Coin;
use App\Models\CryptoNetwork;
use App\Rules\WalletAddressRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;

class TransactionRequest extends FormRequest
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
        $toman = Coin::where('code', 'TOMAN')->first();
        if ((int)$this->coin_id !== (int)$toman->id) {
            return $this->cryptoRules();
        }
        return $this->tomanRules();
    }

    protected function tomanRules()
    {
        return [
            'coin_id' => ["required", 'exists:coins,id'],
            'amount' => ["required", 'numeric', 'max:50000000', 'min:1000'],
            'account_id' => [
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    $account = current_user()
                        ->accounts()
                        ->where('id', $value)
                        ->first();
                    if (!isset($account)) {
                        $fail(trans('validation.exists'));
                    } elseif ($account->status !== 'ACCEPTED') {
                        $fail(trans('messages.JUST_ACCEPTED_ACCOUNT'));
                    }
                }
            ],
            'image' => [
                Rule::requiredIf(function () {
                    return $this->route('type') === 'deposit' && in_array($this->payment_method, ['TRANSFER', 'CARD']);
                }),
                'file'
            ],
            'gateway' => [
                Rule::requiredIf(function () {
                    return $this->payment_method === 'ONLINE';
                })
            ],
            'payment_method' => [
                Rule::requiredIf($this->route('type') === 'deposit'),
                Rule::in(['TRANSFER', 'ONLINE', 'CARD']),
            ],
            'code' => [
                Rule::requiredIf($this->route('type') === 'WITHDRAW'),
            ]
        ];
    }

    protected function cryptoRules()
    {
        return [
            'coin_id' => ["required", 'exists:coins,id'],
            'amount' => [
                "required",
                function ($attribute, $value, $fail) {
                    if ($this->route('type') === 'WITHDRAW') {
                        $network = CryptoNetwork::findOrFail($this->crypto_network_id);
                        if ($value > $network->withdraw_max) {
                            $fail(trans('validation.max.numeric', ['max' => $network->withdraw_max]));
                        }
                        if ($value < $network->withdraw_min) {
                            $fail(trans('validation.min.numeric', ['min' => $network->withdraw_min]));
                        }
                    }
                }
            ],
            'crypto_network_id' => [
                'required',
                'exists:crypto_networks,id',
                function ($attribute, $value, $fail) {
                    $network = CryptoNetwork::find($value);
                    if (!isset($network) && $network->status !== 'ACTIVATED') {
                        $fail(trans('validation.activate'));
                    }
                    if ($this->route('type') === 'WITHDRAW' && $network->withdraw_status !== 'ACTIVATED') {
                        $fail(trans('validation.activate'));
                    }
                    if ($this->route('type') === 'DEPOSIT' && $network->deposit_status !== 'ACTIVATED') {
                        $fail(trans('validation.activate'));
                    }
                    if ($network->coin_id !== (int)$this->coin_id) {
                        $fail(trans('validation.in'));
                    }
                },
            ],
            'address' => [
                Rule::requiredIf($this->route('type') === 'WITHDRAW'),
                isset($this->crypto_network_id) && $this->route('type') === 'WITHDRAW' ? new WalletAddressRule($this->crypto_network_id) : ""
            ],
            'memo' => ['sometimes'],
            'payment_method' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'CRYPTO') {
                        $fail(trans('validation.in', ['attribute' => $attribute]));
                    }
                }

            ],
            'code' => [
                Rule::requiredIf($this->route('type') === 'WITHDRAW'),
            ]
        ];
    }
}
