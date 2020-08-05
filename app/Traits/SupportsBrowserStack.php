<?php
namespace App\Traits;

use BrowserStack\Local;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Run BrowserStack from your tests.
 */
trait SupportsBrowserStack
{
    protected static $web_driver, $bs_local;
    /**
     * Create the BrowserStack WebDriver instance.
     */
    public function createBrowserStackDriver(array $config = null): RemoteWebDriver
    {
        if ($config["capabilities"]["browserstack.local"] === "true") {
            $this->bs_local = new Local();
            $bs_local_args = [
                "key" => $config["key"],
            ];
            $this->bs_local->start($bs_local_args);
        }

        $this->web_driver = RemoteWebDriver::create(
            "https://$config[username]:$config[key]@hub-cloud.browserstack.com/wd/hub",
            $config["capabilities"]
        );

        return $this->web_driver;
    }

    /**
     * @afterClass
     */
    public static function shutdown()
    {
        if (static::$bs_local && static::$bs_local->isRunning()) {
            static::$bs_local->stop();
        }
        if (static::$web_driver) {
            static::$web_driver->quit();
        }
    }

}
