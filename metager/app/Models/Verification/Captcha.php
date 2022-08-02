<?php

namespace App\Models\Verification;

use Mews\Captcha\Captcha as CaptchaCaptcha;

class Captcha extends CaptchaCaptcha
{
    public function getText()
    {
        return $this->text;
    }
}
