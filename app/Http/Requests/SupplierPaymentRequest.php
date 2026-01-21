<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SupplierPaymentRequest extends FormRequest
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
            'purchase_no' => ['required', 'string', 'max:255', Rule::unique('supplier_payments')->ignore($this->id)],
            'date' => 'required|date_format:d-m-Y',
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'purchase_no.required' => 'The Voucher Number field is required.',
            'purchase_no.string' => 'The Voucher Number must be a valid string.',
            'purchase_no.max' => 'The Voucher Number must not exceed 255 characters.',

            'date.required' => 'The Date field is required.',
            'date.date_format' => 'The Date must be in the format DD-MM-YYYY.',

            'supplier_id.required' => 'The Supplier field is required.',
            'supplier_id.exists' => 'The selected Supplier is invalid.',



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
