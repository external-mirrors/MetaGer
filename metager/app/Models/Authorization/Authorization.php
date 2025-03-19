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
    protected float $cost = 1;

    public bool $loggedIn = false;

    /**
     * How many Tokens are available to the user
     */
    public float $availableTokens;

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
     * 
     * @param bool $current_request Tokenauthorization does not always send valid tokens but expects interface elements as if the request is authorized
     */
    public function canDoAuthenticatedSearch(bool $current_request = true)
    {
        return $this->availableTokens >= $this->cost;
    }

    public abstract function getToken();

    /**
     * Makes a payment for the current request
     * @param float $cost Amount of token to pay
     * 
     * @return bool
     */
    public abstract function makePayment(float $cost);

    /**
     * Returns a link where the user should be sent to, when we want
     * to advertise the metager key.
     * => /keys Startpage when unauthorized
     * => /keys/key/<USER_KEY> when a key is configured
     */
    public function getAdfreeLink()
    {
        if ($this instanceof KeyAuthorization) {
            return LaravelLocalization::getLocalizedUrl(null, "/keys/key/" . urlencode($this->getToken()));
        } else if ($this instanceof TokenAuthorization) {
            return LaravelLocalization::getLocalizedUrl(null, "/keys/key/enter");
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
            $keyIcon = "/img/svg-icons/key-icon.svg";
        } else if ($this->availableTokens < $this->cost) {
            $keyIcon = "/img/svg-icons/key-empty.svg";
        } else if ($this->availableTokens <= 30) {
            $keyIcon = "/img/svg-icons/key-low.svg";
        } else {
            $keyIcon = "/img/svg-icons/key-full.svg";
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
        return $tooltip;
    }

    public function setCost(float $cost)
    {
        $this->cost = round($cost, 1);
    }

    public function getCost(): float
    {
        return $this->cost;
    }
}