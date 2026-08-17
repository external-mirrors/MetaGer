<?php

namespace Tests;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Extra Firefox preferences for this test case's browser.
     *
     * Exists so a test can ask for a browser configured differently from the
     * default — above all one with JavaScript switched off, which is the only
     * way to prove the progressive-enhancement requirement rather than assume it.
     *
     * @var array<string, bool|int|string>
     */
    protected array $driverPreferences = [];

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = new FirefoxOptions();

        // php-webdriver's firefox() defaults, kept explicit now that we build
        // the options object ourselves.
        $options->setPreference("reader.parse-on-load.enabled", false);
        $options->setPreference("devtools.jsonview.enabled", false);

        foreach ($this->driverPreferences as $preference => $value) {
            $options->setPreference($preference, $value);
        }

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            "http://" . config("metager.metager.selenium.host") . ":4444/wd/hub",
            $capabilities
        );
    }
}
