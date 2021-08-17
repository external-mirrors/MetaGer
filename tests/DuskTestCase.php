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
            "project" => config("metager.metager.git.project_name"),
            "build" => config("metager.metager.git.branch_name"),
            "name" => config("metager.metager.git.commit_name"),
            "browserstack.local" => "true",
            "browserstack.console" => "verbose",
            "browserstack.networkLogs" => "true",
            "browserstack.timezone" => "Europe/Berlin",
            "browserstack.selenium_version" => "3.5.2",
        ];
        if (config("app.url") !== "http://nginx") {
            # Not local Testing
            $capabilities["browserstack.local"] = "false";
        }
        return $this->createBrowserStackDriver([
            "username" => config("metager.metager.webdriver.user"),
            "key" => config("metager.metager.webdriver.key"),
            "capabilities" => $capabilities,
        ]);
    }
}
