<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PCSRF implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        // Nobody wants to fight through hundreds of mails every day regarding
        // oranges or 60m rinse hoses
        // However CSRF requires some sort of user session which we want to avoid
        // That's why we implement a similar but easier to bypass method of pseudo CSRF

        // $value should contain a base64 encoded timestamp
        if (base64_encode(base64_decode($value, true)) !== $value) {
            return false;
        } else {
            $value = base64_decode($value, true);
        }
        if (\is_int($value)) {
            return false;
        } else {
            $value = intval($value);
        }

        $currentTime = \time();

        // If the request was sent faster than 5 seconds or if it took longer than one hour we assume it's spam
        if (($currentTime - 5) <= $value || ($currentTime - 3600) >= $value) {
            return false;
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
        return trans("validation.pcsrf");
    }
}
