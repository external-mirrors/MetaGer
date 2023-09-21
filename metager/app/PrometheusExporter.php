<?php

namespace App;

use Prometheus\CollectorRegistry;

class PrometheusExporter
{

    public static function CaptchaShown()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'captcha_shown', 'counts how often the captcha was shown', []);
        $counter->inc();
    }

    public static function CaptchaCorrect()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'captcha_correct', 'counts how often the captcha was solved correctly', []);
        $counter->inc();
    }

    public static function CaptchaAnswered()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'captcha_answered', 'counts how often the captcha was answered', []);
        $counter->inc();
    }

    public static function HumanVerificationSuccessfull()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'humanverification_success', 'counts how often humanverification middleware was successfull', []);
        $counter->inc();
    }

    public static function HumanVerificationError()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'humanverification_error', 'counts how often humanverification middleware had an error', []);
        $counter->inc();
    }

    public static function Duration($duration, $type)
    {
        $registry = CollectorRegistry::getDefault();
        $histogram = $registry->getOrRegisterHistogram('metager', 'request_time', 'Loading Times for different cases', ['type'], [0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0, 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 2.0, 2.2, 2.4, 2.6, 2.8, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0, 15.0, 20.0, 30.0, 35.0]);
        $histogram->observe($duration, [$type]);
    }

    /**
     * @param string $language
     * @param array $type
     */
    public static function PreferredLanguage($language, $type)
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", $language, 'counts preferred language usages', ['type']);
        $counter->inc($type);
    }

    public static function OvertureFail()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "overture_failed", "counts how often overture failed a response");
        $counter->inc();
    }

    public static function KeyUsed(string $engine, bool $cached)
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "key_used", "Counts MetaGer Key Usage", ["searchengine", "cached"]);
        $counter->inc([$engine, json_encode($cached)]);
    }
    public static function UpdateMainzKeyStatus($tokens)
    {
        $registry = CollectorRegistry::getDefault();
        $gauge = $registry->getOrRegisterGauge("metager", "key", "Tracks status of the Mainz Key", ["owner"]);
        $gauge->set($tokens, ["mainz"]);
    }
}