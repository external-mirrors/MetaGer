<?php

namespace App;

use BrowserStack\Local;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Browserstack
{
    private $webdriver, $bs_local = null;
    private $LOCALCAPABILITIES = array();
    private $CAPABILITIES = array();

    public function __construct()
    {
        $this->setCapabilities();
        $caps = null;
        if ($this->isLocal()) {
            $caps = $this->LOCALCAPABILITIES;
            $this->bs_local = new Local();
            $bs_local_args = array("key" => env("WEBDRIVER_KEY", ""));
            $this->bs_local->start($bs_local_args);
        } else {
            $caps = $this->CAPABILITIES;
        }
        $this->webdriver = RemoteWebDriver::create(
            getenv("WEBDRIVER_URL"),
            $caps
        );
    }

    private function setCapabilities()
    {
        $this->LOCALCAPABILITIES = array(
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
        );
        $this->CAPABILITIES = array(
            "os" => "Windows",
            "os_version" => "10",
            "browser" => "Firefox",
            "browser_version" => "79.0 beta",
            "resolution" => "1920x1080",
            "project" => env("PROJECT_NAME", "Not Set"),
            "build" => env("BRANCH_NAME", "Not Set"),
            "name" => env("COMMIT_NAME", "Not Set"),
            "browserstack.local" => "false",
            "browserstack.console" => "verbose",
            "browserstack.networkLogs" => "true",
            "browserstack.timezone" => "Europe/Berlin",
            "browserstack.selenium_version" => "3.5.2",
        );
    }

    public function getWebdriver()
    {
        return $this->webdriver;
    }

    public function shutdown()
    {
        $this->webdriver->quit();
        if ($this->bs_local != null) {
            $this->bs_local->stop();
        }
    }

    private function isLocal()
    {
        return env("APP_URL", "") === "http://nginx";
    }

}
