<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CustomerRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'area_id' => 'nullable|exists:areas,id',
            'name' => ['required', 'regex:/^[a-zA-Z0-9\s]*$/', Rule::unique('customers')->ignore($this->id)],
            'name_ur' => ['nullable', Rule::unique('customers')->ignore($this->id)],
            'opening_balance' => 'nullable|numeric|min:0',
            'email' => [
                'nullable',
                'regex:/^[\w\.-]+@[\w\.-]+\.\w+$/', // Basic email regex
                Rule::unique('customers')->ignore($this->id)
            ],
            'mobile' => ['nullable', 'regex:/^[0-9]{10}$/', Rule::unique('customers')->ignore($this->id)],
            'national_id' => ['nullable', Rule::unique('customers')->ignore($this->id)],
            'address' => 'nullable',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Customer Name is Required',
            'name.regex' => 'The customer name may only contain letters, numbers, and spaces.',
            'name.unique' => 'The customer name has already been taken.',
            'name_ur.unique' => 'The customer name urdu has already been taken.',
            'email.required' => 'Customer Email is Required.',
            'mobile.regex' => 'The mobile number must be a 10-digit number.',
            'area_id.exists' => 'The selected area does not exist.',
            'email.regex' => 'The email format is invalid.',
            'mobile.regex' => 'The mobile number must be a valid 10-digit number.',
            'national_id.unique' => 'The national ID must be unique.',
            'opening_balance.numeric' => 'Opening balance must be a number.',
            'opening_balance.min' => 'Opening balance must be at least 0.',
            'email.unique' => 'The email has already been taken.',
            'mobile.unique' => 'The mobile number has already been taken.',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        if ($this->ajax()) {
            throw new HttpResponseException(response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ]));
        } else {
            parent::failedValidation($validator);
        }
    }
}
