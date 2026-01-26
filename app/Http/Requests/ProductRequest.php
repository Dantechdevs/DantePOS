<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->id)],
            'is_scheme_product' => 'nullable',
            'product_code' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->id)],
            'quantity' => 'required|numeric|min:0',
            'supplier_id' => 'required',
            'stock_alert' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'default_unit' => [
                'required',
                // 'exists:units,id',
                // function ($attribute, $value, $fail) {
                //     if (!in_array($value, $this->input('unit_id', []))) {
                //         $fail('The selected default unit must be one of the provided units.');
                //     }
                // },
            ],

            // Array validations - combined into single rule arrays
            'unit_id' => [
                'required',
                'array',
                'min:1'
            ],
            'unit_id.*' => 'required|exists:units,id',

            'conversion' => 'required|array|min:1',
            'conversion.*' => 'required|numeric|min:0.01',

            'purchase_price' => 'required|array|min:1',
            'purchase_price.*' => 'required|numeric|min:0',

            'selling_price' => 'required|array|min:1',
            'selling_price.*' => 'required|numeric|min:0',

            'wholesale_price' => 'required|array|min:1',
            'wholesale_price.*' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Product Name is Required.',
            'name.unique' => 'Product Name has already been taken.',
            'name_ur.required' => 'Product Name Urdu is Required.',
            'name_ur.unique' => 'Product Name Urdu has already been taken.',
            'product_code.required' => 'Product Code is Required.',
            'product_code.unique' => 'Product Code has already been taken.',
            'supplier_id.required' => 'Supplier is Required.',
            'supplier_id.exists' => 'The selected supplier does not exist.',
            'expiry_date.after_or_equal' => 'Expiry Date must be today or a future date.',
            'unit_id.required' => 'At least one unit is required.',
            'unit_id.same' => 'All unit-related arrays must have the same number of items.',
            'default_unit.in' => 'The default unit must be one of the selected units.',
            'conversion.*.min' => 'Each conversion value must be at least 0.01.',
            'purchase_price.*.min' => 'Each purchase price must be at least 0.',
            'selling_price.*.min' => 'Each selling price must be at least 0.',
            'wholesale_price.*.min' => 'Each wholesale price must be at least 0.',
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
