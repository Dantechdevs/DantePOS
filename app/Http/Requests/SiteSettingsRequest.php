<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingsRequest extends FormRequest
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
        $currencyCodes = array_keys(currencyList());
        return [
            'login_logo' => 'nullable|image|max:2048', // Validate file uploads
            'favicon' => 'nullable|image|max:2048', // Validate file uploads
            'invoice_logo' => 'nullable|image|max:2048',
            'invoice_logo2' => 'nullable|image|max:2048',
            'default_image' => 'nullable|image|max:2048',
            'site_name' => 'required|string|max:255',
            'site_name_ur' => 'required|string|max:255',
            'site_address' => 'nullable|string|max:500',
            'site_address_urdu' => 'nullable|string|max:500',
            'mobile_numbers' => 'nullable|string|max:500',
            'timezone' => 'required|in:' . implode(',', \DateTimeZone::listIdentifiers()),
            'currency' => 'required|in:' . implode(',', $currencyCodes),
            'billing_language' => 'required|in:english,urdu',
            'threshold_amount' => 'nullable|integer|min:0',
            'footer_text' => 'nullable|string|max:500',
        ];
    }
}
