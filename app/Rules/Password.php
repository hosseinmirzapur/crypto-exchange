<?php


namespace App\Rules;


use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Support\Facades\Validator;

class Password extends \Illuminate\Validation\Rules\Password
{
    public function passes($attribute, $value)
    {
        $validator = Validator::make($this->data, [
            $attribute => 'string|min:'.$this->min,
        ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        $value = (string) $value;

        if ($this->mixedCase && ! preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
            $this->fail(trans('validation.pass.mixed_case'));
        }

        if ($this->letters && ! preg_match('/\pL/u', $value)) {
            $this->fail(trans('validation.pass.letters'));
        }

        if ($this->symbols && ! preg_match('/\p{Z}|\p{S}|\p{P}/u', $value)) {
            $this->fail(trans('validation.pass.symbols'));
        }

        if ($this->numbers && ! preg_match('/\pN/u', $value)) {
            $this->fail(trans('validation.pass.numbers'));
        }

        if (! empty($this->messages)) {
            return false;
        }

        if ($this->uncompromised && ! Container::getInstance()->make(UncompromisedVerifier::class)->verify([
                'value' => $value,
                'threshold' => $this->compromisedThreshold,
            ])) {
            return $this->fail(
                trans('validation.pass.uncompromised')
            );
        }

        return true;
    }

}
