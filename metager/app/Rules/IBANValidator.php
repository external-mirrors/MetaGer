<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use PHP_IBAN\IBAN;

class IBANValidator implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

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
        $iban = new IBAN($value);
        return $iban->Verify($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('spende.execute-payment.directdebit.iban.error');
    }
}