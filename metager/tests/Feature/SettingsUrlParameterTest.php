<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * `url` on the settings routes is the "back to where I came from" link, and
 * every link MetaGer generates sends it as a string. A hand-built
 * `?url[]=…` used to reach a `(string)` cast and answer 500 with
 * "Array to string conversion" (GlitchTip METAGER-18L, twice, from two
 * different clients). Anything that is not a string is now treated as if
 * no `url` had been sent at all.
 */
class SettingsUrlParameterTest extends TestCase
{
    #[Test]
    public function the_settings_page_ignores_an_array_url(): void
    {
        $this->get('/meta/settings?url[]=https://metager.de/meta/meta.ger3')
            ->assertOk();
    }

    #[Test]
    public function the_settings_page_still_links_back_to_a_string_url(): void
    {
        $this->get('/meta/settings?url=' . urlencode('https://metager.de/meta/meta.ger3?eingabe=back'))
            ->assertOk()
            ->assertSee('eingabe=back', false);
    }

    #[Test]
    public function the_all_settings_page_ignores_an_array_url(): void
    {
        $this->get('/meta/settings/all-settings?url[]=https://metager.de/')
            ->assertOk();
    }

    #[Test]
    public function a_setting_change_with_an_array_url_redirects_to_a_page_that_renders(): void
    {
        $response = $this->post('/meta/settings/es', [
            'focus'     => 'web',
            'dark_mode' => 'dark',
            'url'       => ['https://metager.de/'],
        ]);
        $response->assertRedirect();

        $this->get((string) $response->headers->get('Location'))->assertOk();
    }

    #[Test]
    public function resetting_all_settings_with_an_array_url_falls_back_to_the_default(): void
    {
        $this->post('/meta/settings/all-settings/removeAll', ['url' => ['https://metager.de/']])
            ->assertRedirect('https://metager.de');
    }

    #[Test]
    public function removing_one_setting_with_an_array_url_falls_back_to_the_default(): void
    {
        $this->post('/meta/settings/all-settings/removeOne', ['key' => 'dark_mode', 'url' => ['https://metager.de/']])
            ->assertRedirect('https://metager.de');
    }
}
