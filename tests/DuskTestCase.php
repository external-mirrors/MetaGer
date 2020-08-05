<?php

namespace Tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use \App\Traits\SupportsBrowserStack;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    use SupportsBrowserStack;

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
        $capabilities = [
            "os" => "Windows",
            "os_version" => "10",
            "browser" => "Firefox",
            "browser_version" => "79.0 beta",
            "resolution" => "1920x1080",
            "project" => env("PROJECT_NAME", "Not Set"),
            "build" => env("BRANCH_NAME", "Not Set"),
            "name" => env("COMMIT_NAME", "Not Set"),
            "browserstack.local" => "true",
            "browserstack.console" => "verbose",
            "browserstack.networkLogs" => "true",
            "browserstack.timezone" => "Europe/Berlin",
            "browserstack.selenium_version" => "3.5.2",
        ];
        if (env("APP_URL", "") !== "http://nginx") {
            # Not local Testing
            $capabilities["browserstack.local"] = "false";
        }
        return $this->createBrowserStackDriver([
            "username" => env("WEBDRIVER_USER", ""),
            "key" => env("WEBDRIVER_KEY", ""),
            "capabilities" => $capabilities,
        ]);
    }
}
