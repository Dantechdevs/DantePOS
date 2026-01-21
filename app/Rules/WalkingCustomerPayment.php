<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class WalkingCustomerPayment implements Rule
{
    protected $customerId;
    protected $grandTotal;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($customerId, $grandTotal)
    {
        $this->customerId = $customerId;
        $this->grandTotal = $grandTotal;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->customerId == 1) {
            return $value == $this->grandTotal;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Walking customer must pay the full amount. Paid amount must equal grand total.';
    }
}
