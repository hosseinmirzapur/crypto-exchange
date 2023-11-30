<?php

namespace App\Rules;

use App\Models\CryptoNetwork;
use Illuminate\Contracts\Validation\Rule;

class WalletAddressRule implements Rule
{
    /**
     * @var CryptoNetwork
     */
    private $cryptoNetwork;

    /**
     * Create a new rule instance.
     * @param $cryptoNetworkId
     */
    public function __construct($cryptoNetworkId)
    {
        $this->cryptoNetwork = CryptoNetwork::findOrFail($cryptoNetworkId);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match("/{$this->cryptoNetwork->regex}/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.wallet_address');
    }
}
