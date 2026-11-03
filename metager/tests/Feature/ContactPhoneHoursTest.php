<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * /kontakt — when someone picks up the phone.
 *
 * Weekdays 9:00 to 16:00 German time someone almost always answers, outside
 * those hours almost never. The times are written the way the visitor's
 * locale writes them (16:00 in Germany, 4:00 PM in the US), and always say
 * which clock they are on, because a visitor elsewhere would otherwise read
 * them as their own.
 */
class ContactPhoneHoursTest extends TestCase
{
    public function testGermanVisitorsSeeTheHoursOnTheGermanClock(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee('Montag bis Freitag, <time datetime="09:00">09:00</time>–<time datetime="16:00">16:00</time> Uhr (deutsche Zeit)', false);
    }

    public function testAmericanVisitorsSeeTwelveHourTimes(): void
    {
        $this->followingRedirects()->get("/en-US/kontakt")
            ->assertOk()
            ->assertSee('Monday to Friday, <time datetime="09:00">9:00 AM</time>–<time datetime="16:00">4:00 PM</time> German time', false);
    }

    public function testBritishVisitorsSeeTwentyFourHourTimes(): void
    {
        $this->get("/en-GB/kontakt")
            ->assertOk()
            ->assertSee('<time datetime="09:00">09:00</time>–<time datetime="16:00">16:00</time> German time', false);
    }

    /** The hours belong to the phone, not to email or post. */
    public function testTheHoursSitInThePhoneSection(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $phone = strpos($content, 'id="phone"');
        $hours = strpos($content, '<time datetime="09:00">');
        $post = strpos($content, 'id="post"');

        $this->assertNotFalse($hours, "No office hours on the contact page.");
        $this->assertGreaterThan($phone, $hours);
        $this->assertLessThan($post, $hours);
    }

    /** Outside the hours nobody answers, and the page says so instead of letting people ring. */
    public function testThePageSaysToWriteOutsideTheHours(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSeeText("außerhalb fast nie")
            ->assertSeeText("E-Mail");
    }

    /** @return array<string, array{0: string}> */
    public static function locales(): array
    {
        $locales = [];
        foreach (glob(dirname(__DIR__, 2) . "/lang/*/kontakt.php") as $file) {
            // de-DE, en-GB, … are symlinks the container creates to these.
            if (is_link(dirname($file))) {
                continue;
            }
            $locale = basename(dirname($file));
            $locales[$locale] = [$locale];
        }
        return $locales;
    }

    #[DataProvider("locales")]
    public function testEveryLanguageHasTheHours(string $locale): void
    {
        foreach (["kontakt.phone.hours", "kontakt.phone.outside"] as $key) {
            $this->assertTrue(Lang::hasForLocale($key, $locale), "$locale is missing $key.");
        }

        $hours = trans("kontakt.phone.hours", ["from" => "<F>", "to" => "<T>"], $locale);
        $this->assertStringContainsString("<F>", $hours, "$locale: kontakt.phone.hours drops :from.");
        $this->assertStringContainsString("<T>", $hours, "$locale: kontakt.phone.hours drops :to.");
    }
}
