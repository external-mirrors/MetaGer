<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use \ChinLeung\BrowserStack\RunsOnBrowserStack;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, RunsOnBrowserStack;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
    }

    protected function getBuildName(): string
    {
        return config("metager.metager.git.branch_name");
    }
}
