<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SupplierRequest extends FormRequest
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
            'name' => ['required', 'regex:/^[a-zA-Z0-9\s]*$/', Rule::unique('suppliers')->ignore($this->id)],
            'email' => [
                'nullable',
                'regex:/^[\w\.-]+@[\w\.-]+\.\w+$/', // Basic email regex
                Rule::unique('suppliers')->ignore($this->id)
            ],
            'opening_balance' => 'nullable|numeric|min:0',
            'mobile' => ['required', 'regex:/^[0-9]{10}$/', Rule::unique('suppliers')->ignore($this->id)],
            'national_id' => ['nullable', Rule::unique('suppliers')->ignore($this->id)],
            'address' => 'nullable',
            'available_days' => 'nullable|array|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Supplier Name is Required',
            'email.required' => 'Supplier Email is Required.',
            'mobile.required' => 'Mobile is Required.',
            'mobile.regex' => 'The mobile number must be a 10-digit number.',
            'opening_balance.numeric' => 'Opening balance must be a number.',
            'opening_balance.min' => 'Opening balance must be at least 0.',
            'available_days.in' => 'Invalid day selected. Please choose from Monday to Sunday.',
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
