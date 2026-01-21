<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Product;

class PurchaseRequest extends FormRequest
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
            'purchase_no' => ['required', 'string', 'max:50', Rule::unique('purchases')->ignore($this->id)],
            'date' => 'required|date_format:d-m-Y',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'status' => 'required|string|in:received,pending,cancel',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|exists:products,id',
            'productName' => 'required|array|min:1',
            'productName.*' => 'required|string',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'unit' => 'required|array|min:1',
            'unit.*' => 'required|string',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
            'amount' => 'required|array|min:1',
            'amount.*' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'sub_total' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'purchase_no.required' => 'Invoice number is required.',
            'purchase_no.string' => 'Invoice number must be a string.',
            'date.required' => 'Date is required.',
            'date.date_format' => 'Date must be in the format DD-MM-YYYY.',
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either received or pending/cancel.',
            'product_id.required' => 'Products are required.',
            'product_id.*.exists' => 'One or more selected products are invalid.',
            'productName.*.required' => 'Each product name is required.',
            'quantity.*.required' => 'Each quantity is required.',
            'quantity.*.min' => 'The quantity must be at least 1.',
            'unit.*.required' => 'Each unit is required.',
            'price.*.required' => 'Each selling price is required.',
            'amount.*.required' => 'Each amount is required.',
            'sub_total.required' => 'Invalid Subtotal',
            'grand_total.required' => 'Invalid Grand total.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.mimes' => 'The attachment must be a file of type: jpg, jpeg, png, pdf, doc, docx.',
            'attachment.max' => 'The attachment must not be larger than 5MB.',
        ];

        // Fetch product names for invalid products
        $productIds = $this->input('product_id', []);
        $products = Product::whereIn('id', $productIds)->pluck('name', 'id'); // Get product names

        foreach ($productIds as $index => $productId) {
            if (!$products->has($productId)) {
                $productName = $this->input('productName')[$index] ?? 'Unknown Product'; // Use productName if provided
                $messages["product_id.$index.exists"] = "The product '$productName' at position " . ($index + 1) . " is invalid.";
            }
        }

        return $messages;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        // Replace product IDs with product names in errors if possible
        if (isset($errors['product_id'])) {
            $productIds = $this->input('product_id', []);
            $products = Product::whereIn('id', $productIds)->pluck('name', 'id');

            foreach ($errors['product_id'] as $key => $error) {
                if (preg_match('/product_id\.(\d+)\.exists/', $key, $matches)) {
                    $index = $matches[1]; // Get the index of the invalid product
                    $productId = $productIds[$index] ?? null;
                    $productName = $products[$productId] ?? 'Unknown Product';
                    $errors['product_id'][$key] = "The product '$productName' at position " . ($index + 1) . " is invalid.";
                }
            }
        }

        throw new HttpResponseException(response()->json([
            'status' => 400,
            'errors' => $errors,
        ]));
    }
}
