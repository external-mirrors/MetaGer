<?php

namespace App\Models\Authorization;

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
     * Will currently be always 1;
     */
    private function calculateCost()
    {
        return 3;
    }
}