<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CustomerPaymentRequest extends FormRequest
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
        return [
            'invoice_no' => ['required', 'string', 'max:255', Rule::unique('customer_opening_balances')->ignore($this->id)],
            'date' => 'required|date_format:d-m-Y',
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:credit,debit', // Assuming type is either 'credit' or 'debit'
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'invoice_no.required' => 'The Voucher Number field is required.',
            'invoice_no.string' => 'The Voucher Number must be a valid string.',
            'invoice_no.max' => 'The Voucher Number must not exceed 255 characters.',

            'date.required' => 'The Date field is required.',
            'date.date_format' => 'The Date must be in the format DD-MM-YYYY.',

            'customer_id.required' => 'The Customer field is required.',
            'customer_id.exists' => 'The selected Customer is invalid.',

            'area_id.required' => 'The Area field is required.',
            'area_id.exists' => 'The selected Area is invalid.',

            'type.required' => 'The Type field is required.',
            'type.in' => 'The Type must be either "credit" or "debit".',

            'description.string' => 'The Description must be a valid string.',
            'description.max' => 'The Description must not exceed 1000 characters.',

            'amount.required' => 'The Amount field is required.',
            'amount.numeric' => 'The Amount must be a valid number.',
            'amount.min' => 'The Amount must be at least 0.',
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
