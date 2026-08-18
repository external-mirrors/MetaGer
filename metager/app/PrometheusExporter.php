<?php

namespace App;

use Prometheus\CollectorRegistry;

class PrometheusExporter
{

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

    /**
     * Every locale decision, and whether it moved the user.
     *
     * The number the `LOCALE_DECOUPLED` rollout is watched on. Decoupling the
     * interface language from the domain removes two whole classes of redirect
     * — language-to-domain and stored-setting-to-prefixed-URL — so the
     * redirect share of this counter should fall and stay fallen. A rise means
     * a rule is firing that was supposed to be gone, which is the one failure
     * mode that costs a user a page load rather than merely a wrong word.
     *
     * `$reason` is a fixed vocabulary, never user input: Prometheus keeps one
     * time series per label value, so a free-form label is a memory leak.
     */
    public static function LocaleDecision(string $reason)
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "locale_decisions", "Locale resolutions, by what the request was answered with", ["reason"]);
        $counter->inc([$reason]);
    }

    public static function SuggestionSessionCounter()
    {
        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter("metager", "suggestion_sessions", "Suggestion Requests answered");
        $counter->inc();
    }
}