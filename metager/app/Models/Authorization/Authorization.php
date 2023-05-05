<?php

namespace App\Models\Authorization;

use App\Models\Configuration\Searchengines;
use App\SearchSettings;
use LaravelLocalization;

/**
 * Summary of Authorization
 */
abstract class Authorization
{
    /**
     * The cost of this search
     */
    public int $cost = 3;

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
        $this->availableTokens = -1;
    }

    /**
     * Checks whether the user is allowed to do the current
     * search in an authorized environment
     */
    public function canDoAuthenticatedSearch()
    {
        return $this->availableTokens >= $this->cost;
    }

    public abstract function getToken();

    /**
     * Makes a payment for the current request
     * @param int $cost Amount of token to pay
     * 
     * @return bool
     */
    public abstract function makePayment(int $cost);

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