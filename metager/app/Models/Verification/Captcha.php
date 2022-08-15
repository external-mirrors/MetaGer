<?php

namespace App\Models\Verification;

use Mews\Captcha\Captcha as MewsCaptcha;

class Captcha extends MewsCaptcha
{
    public function getText()
    {
        return $this->text;
    }
}
