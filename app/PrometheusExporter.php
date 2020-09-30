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
        $counter = $registry->getOrRegisterCounter('metager', 'humanverification_success', 'counts how often humanverification middleware was successfull', []);
        $counter->inc();
    }

    public static function HumanVerificationError()
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'humanverification_error', 'counts how often humanverification middleware had an error', []);
        $counter->inc();
    }

    public static function Duration($duration, $type)
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();
        $gauge = $registry->getOrRegisterGauge('metager', 'request_time', 'How long does Resultpage load', ['type']);
        $gauge->set($duration, [$type]);
    }
}
