<?php

namespace App\Rules;

use Illuminate\Contracts\Encryption\DecryptException;
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
        try {
            $value = \Crypt::decrypt($value);
        } catch (DecryptException $e) {
            return false;
        }

        if (!\is_int($value)) {
            return false;
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