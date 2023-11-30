<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;

class AuthenticationRequest extends FormRequest
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
        $id = current_user()->profile->id ?? 0;

        return [
            'name' => 'required',
            'national_code' => ['required','string','size:10',Rule::unique('profiles','national_code')->ignore($id)],
            'birthday' => 'required',
            'mobile' => ['required','string','size:11',  Rule::unique('profiles','mobile')->ignore($id)],
            'address' => 'required',
            'phone' => 'required',
        ];
    }
}
