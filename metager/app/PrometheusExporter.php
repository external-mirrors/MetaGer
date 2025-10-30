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

    public static function KeyUsed(float $amount, string $source, bool $cached)
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "key_used", "Counts MetaGer Key Usage", ["source", "cached"]);
        $counter->incBy($amount, [$source, json_encode($cached)]);
    }
    public static function UpdateKeyStatus($key, $tokens, $owner)
    {
        $registry = CollectorRegistry::getDefault();
        $gauge = $registry->getOrRegisterGauge("metager", "key_status", "Tracks status of the Key", ["key", "owner"]);
        $gauge->set($tokens, [$key, $owner]);
    }
    public static function CreditcardDonation(string $status)
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "donation_card", "Card Payment started", ["status"]);
        $counter->inc([$status]);
    }

    public static function SuggestionResult(string $httpcode)
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "suggestion_results", "Suggestion Requests answered", ["httpcode"]);
        $counter->inc([$httpcode]);
    }

    public static function SuggestionSessionCounter()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "suggestion_sessions", "Suggestion Requests answered");
        $counter->inc();
    }
}