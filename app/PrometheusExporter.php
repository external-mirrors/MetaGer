<?php

namespace App;

class PrometheusExporter
{

    public static function CaptchaShown()
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'captcha_shown', 'counts how often the captcha was shown', []);
        $counter->inc();
    }

    public static function CaptchaCorrect()
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'captcha_correct', 'counts how often the captcha was solved correctly', []);
        $counter->inc();
    }

    public static function CaptchaAnswered()
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'captcha_answered', 'counts how often the captcha was answered', []);
        $counter->inc();
    }

    public static function HumanVerificationSuccessfull()
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'humanverification', 'counts how often humanverification middleware was successfull', []);
        $counter->inc();
    }
}
