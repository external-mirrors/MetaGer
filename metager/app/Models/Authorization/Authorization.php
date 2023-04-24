<?php

namespace App\Models\Authorization;

use LaravelLocalization;

/**
 * Summary of Authorization
 */
abstract class Authorization
{
    /**
     * The cost of this search
     */
    public int $cost;

    /**
     * How many Tokens are available to the user
     */
    public int $availableTokens;

    /**
     * How many tokens were already consumed by the search
     */
    public int $usedTokens = 0;

    public function __construct()
    {
        $this->cost = $this->calculateCost();
        $this->availableTokens = 0;
    }

    /**
     * Checks whether the user is allowed to do the current
     * search in an authorized environment
     */
    public function canDoAuthenticatedSearch()
    {
        return $this->availableTokens >= $this->cost;
    }

    public abstract function authenticate();
    public abstract function getToken();

    /**
     * Checks whether the user has already paid for his
     * authenticated search
     */
    public function isAuthenticated()
    {
        return $this->usedTokens >= $this->cost;
    }

    /**
     * Calculates the cost of the current search 
     * Will currently be always 3;
     */
    private function calculateCost()
    {
        return 3;
    }

    /**
     * Returns a link where the user should be sent to, when we want
     * to advertise the metager key.
     * => /keys Startpage when unauthorized
     * => /keys/key/<USER_KEY> when a key is configured
     */
    public function getAdfreeLink()
    {
        if (!empty($this->getToken()) && is_string($this->getToken())) {
            return LaravelLocalization::getLocalizedUrl(null, "/keys/key/" . urlencode($this->getToken()));
        } else {
            return LaravelLocalization::getLocalizedUrl(null, "/keys");
        }
    }
}