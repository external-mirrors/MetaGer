<?php

namespace App\Assoc;

/**
 * Spells out a non-negative integer as a German cardinal number, capitalised
 * for a Zuwendungsbestätigung's "Betrag in Buchstaben" line (legally required
 * so the printed amount can't be altered after signing). A clean rewrite of
 * Bescheinigungen/Spendenbescheinigung.php's zahl2wort(), not a port —
 * zahl2wort() hand-cased 1/2/3/4-digit numbers with duplicated branches and
 * capped out at 9999; this recurses over hundreds/thousands instead, and
 * supports up to 999999 (a single receipted amount larger than that would be
 * unusual enough to want a human to check it, so it's a hard error rather
 * than silently falling through).
 */
class NumberToGermanWords
{
    private const ONES = [
        "null", "ein", "zwei", "drei", "vier", "fünf", "sechs", "sieben", "acht", "neun",
        "zehn", "elf", "zwölf", "dreizehn", "vierzehn", "fünfzehn", "sechzehn", "siebzehn", "achtzehn", "neunzehn",
    ];

    private const TENS = [
        2 => "zwanzig", 3 => "dreißig", 4 => "vierzig", 5 => "fünfzig",
        6 => "sechzig", 7 => "siebzig", 8 => "achtzig", 9 => "neunzig",
    ];

    public static function convert(int $number): string
    {
        if ($number < 0 || $number > 999999) {
            throw new \InvalidArgumentException("NumberToGermanWords only supports 0-999999, got {$number}.");
        }

        if ($number === 0) {
            return "null";
        }

        $thousands = intdiv($number, 1000);
        $rest = $number % 1000;

        $words = "";
        if ($thousands > 0) {
            $words .= ($thousands === 1 ? "ein" : self::belowThousand($thousands)) . "tausend";
        }
        if ($rest > 0) {
            $words .= self::belowThousand($rest);
        }

        return $words;
    }

    private static function belowThousand(int $number): string
    {
        $hundreds = intdiv($number, 100);
        $rest = $number % 100;

        $words = "";
        if ($hundreds > 0) {
            $words .= ($hundreds === 1 ? "ein" : self::ONES[$hundreds]) . "hundert";
        }
        if ($rest > 0) {
            $words .= self::belowHundred($rest);
        }

        return $words;
    }

    private static function belowHundred(int $number): string
    {
        // "eins", not "ein" — but only here, where it's the last word of the
        // whole output (every caller appends this return value last, never
        // combines it with anything after). The "ein" form is for the
        // combining position below (einundzwanzig) and the hundred/thousand
        // headers (einhundert, eintausend), never for a number that is just 1.
        if ($number === 1) {
            return "eins";
        }
        if ($number < 20) {
            return self::ONES[$number];
        }

        $tens = intdiv($number, 10);
        $ones = $number % 10;

        $word = self::TENS[$tens];
        if ($ones > 0) {
            $word = ($ones === 1 ? "ein" : self::ONES[$ones]) . "und" . $word;
        }

        return $word;
    }
}
