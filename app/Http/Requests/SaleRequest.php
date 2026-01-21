<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Rules\WalkingCustomerPayment;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update with your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'invoice_no' => ['required', 'string', 'max:50', Rule::unique('sales')->ignore($this->id)],
            'date' => 'required|date_format:d-m-Y',
            'due_date' => 'required|date_format:d-m-Y',
            'customer_id' => 'required|integer|exists:customers,id',
            'godown_id' => 'required|integer|exists:godowns,id',
            // 'area_id' => 'required|integer|exists:areas,id',
            'status' => 'required|in:0,1,2,3', // Example:0 = cancel 1 = received, 2 = pending, 3 = Return
            // 'payment_type' => 'required_if:status,1|in:cash,credit',
            'sale_type' => 'nullable',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|exists:products,id',
            'productName' => 'required|array|min:1',
            'productName.*' => 'required|string',
            // 'productOldQty' => 'required|array|min:1',
            // 'productOldQty.*' => 'required|integer|min:1',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'unit' => 'required|array|min:1',
            'unit.*' => 'required|string',
            'cost' => 'required|array|min:1',
            'cost.*' => 'required|numeric|min:0',
            'calculatedCost' => 'required|array|min:1',
            'calculatedCost.*' => 'required|numeric|min:0',
            'selling_price' => 'required|array|min:1',
            'selling_price.*' => 'required|numeric|min:0',
            'amount' => 'required|array|min:1',
            'amount.*' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'sub_total' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'courier' => 'nullable|string',
            'grand_total' => 'required|numeric|min:0',
            // 'paid_amount' => 'required|numeric|min:0',
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
                new WalkingCustomerPayment($this->customer_id, $this->grand_total),
            ],
            'balance_amount' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
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
            // 'invoice_no.required' => 'Invoice number is required.',
            // 'invoice_no.string' => 'Invoice number must be a string.',
            'date.required' => 'Date is required.',
            'date.date_format' => 'Date must be in the format DD-MM-YYYY.',
            'due_date.required' => 'Due Date is required.',
            'due_date.date_format' => 'Due Date must be in the format DD-MM-YYYY.',
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'godown_id.required' => 'Godown is required.',
            'godown_id.exists' => 'The selected godown is invalid.',
            // Uncomment if area_id is used
            // 'area_id.required' => 'Area is required.',
            // 'area_id.exists' => 'The selected area is invalid.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either received (1) or pending (2).',
            'payment_type.required_if' => 'Payment Type required if status is Billed',
            'product_id.required' => 'Products are required.',
            'product_id.*.exists' => 'One or more selected products are invalid.',
            'productName.*.required' => 'Each product name is required.',
            'productOldQty.*.required' => 'Each product old quantity is required.',
            'productOldQty.*.min' => 'One or more product out of stock',
            'quantity.*.required' => 'Each quantity is required.',
            'quantity.*.min' => 'The quantity must be at least 1.',
            'unit.*.required' => 'Each unit is required.',
            'cost.*.required' => 'Each cost is required.',
            'calculatedCost.*.required' => 'Each calculated cost is required.',
            'selling_price.*.required' => 'Each selling price is required.',
            'amount.*.required' => 'Each amount is required.',
            'sub_total.required' => 'Invalid Subtotal',
            'grand_total.required' => 'Invalid Payable amount.',
            'paid_amount.required' => 'Invalid Paid amount.',
            'balance_amount.required' => 'Invalid Balance amount.',
            'change_amount.required' => 'Invalid Change Amount.',
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
