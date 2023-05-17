<?php

namespace App\Models\Authorization;

use App\Models\Configuration\Searchengines;
use App\SearchSettings;
use Cookie;
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

    /**
     * Returns a link to the correct key icon corresponding to the current key charge
     */
    public function getKeyIcon()
    {
        $keyIcon = "";
        if ($this->availableTokens < 0) {
            $keyIcon = "/img/key-icon.svg";
        } else if ($this->availableTokens < $this->cost) {
            $keyIcon = "/img/key-empty.svg";
        } else if ($this->availableTokens <= 30) {
            $keyIcon = "/img/key-low.svg";
        } else {
            $keyIcon = "/img/key-full.svg";
        }

        if (Cookie::has("tokenauthorization")) {
            switch (Cookie::get("tokenauthorization")) {
                case "full":
                    $keyIcon = "/img/key-full.svg";
                    break;
                case "low":
                    $keyIcon = "/img/key-low.svg";
                    break;
                case "empty":
                    $keyIcon = "/img/key-empty.svg";
                    break;
            }
        }
        return $keyIcon;
    }

    /**
     * Returns a tooltip text corresponding to the current key charge
     */
    public function getKeyTooltip()
    {
        $tooltip = "";
        if ($this->availableTokens < 0) {
            $tooltip = __("index.key.tooltip.nokey");
        } else if ($this->availableTokens < $this->cost) {
            $tooltip = __("index.key.tooltip.empty");
        } else if ($this->availableTokens <= 30) {
            $tooltip = __("index.key.tooltip.low");
        } else {
            $tooltip = __("index.key.tooltip.full");
        }
        if (Cookie::has("tokenauthorization")) {
            switch (Cookie::get("tokenauthorization")) {
                case "full":
                    $tooltip = __("index.key.tooltip.full");
                    break;
                case "low":
                    $tooltip = __("index.key.tooltip.low");
                    break;
                case "empty":
                    $tooltip = __("index.key.tooltip.empty");
                    break;
            }
        }
        return $tooltip;
    }
}