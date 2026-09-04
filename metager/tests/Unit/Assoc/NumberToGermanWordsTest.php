<?php

namespace Tests\Unit\Assoc;

use App\Assoc\NumberToGermanWords;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NumberToGermanWordsTest extends TestCase
{
    #[DataProvider("numbers")]
    public function test_it_spells_out_numbers(int $number, string $expected): void
    {
        $this->assertSame($expected, NumberToGermanWords::convert($number));
    }

    public static function numbers(): array
    {
        return [
            [0, "null"],
            [1, "eins"],
            [7, "sieben"],
            [12, "zwölf"],
            [20, "zwanzig"],
            [21, "einundzwanzig"],
            [30, "dreißig"],
            [99, "neunundneunzig"],
            [100, "einhundert"],
            [101, "einhunderteins"],
            [121, "einhunderteinundzwanzig"],
            [999, "neunhundertneunundneunzig"],
            [1000, "eintausend"],
            [1001, "eintausendeins"],
            [1021, "eintausendeinundzwanzig"],
            [2000, "zweitausend"],
            [21000, "einundzwanzigtausend"],
            [123456, "einhundertdreiundzwanzigtausendvierhundertsechsundfünfzig"],
            [999999, "neunhundertneunundneunzigtausendneunhundertneunundneunzig"],
        ];
    }

    public function test_it_rejects_negative_numbers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        NumberToGermanWords::convert(-1);
    }

    public function test_it_rejects_numbers_above_the_supported_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        NumberToGermanWords::convert(1000000);
    }
}
