<?php

namespace App\Models\Authorization;

use Cookie;
use Illuminate\Support\Facades\Redis;

class TokenAuthorization extends Authorization
{

    /**
     * @var Token[]
     */
    private $tokens = [];
    private $keyserver = "";
    private $tokenauthorization_header;
    public ?AnonymousTokenPayment $token_payment = null;

    public function __construct(string|null $tokenString, string $tokenauthorization)
    {
        parent::__construct();
        $this->tokenauthorization_header = $tokenauthorization;
        $this->loggedIn = true;
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";

        $this->token_payment = new AnonymousTokenPayment($this->cost, [], []);
        $tokenJson = json_decode($tokenString);
        if ($tokenJson === null) {
            $this->availableTokens = 0;
            $tokenJson = [];
        } else if (!is_array($tokenJson)) {
            $this->availableTokens = 0;
            $tokenJson = [];
        } else {
            foreach ($tokenJson as $token) {
                $this->token_payment->addJSONToken($token);
            }
            $this->availableTokens = $this->token_payment->getAvailableTokenCount();
        }

        $this->token_payment->checkTokens();
        $this->availableTokens = $this->token_payment->getAvailableTokenCount();

    }

    public function makePayment(float $cost)
    {
        $cost = round($cost, 1);
        if (!$this->canDoAuthenticatedSearch()) {
            return false;
        }

        if ($this->token_payment->makePayment($cost)) {
            $this->usedTokens += $cost;
            return true;
        } else {
            return false;
        }
    }

    public function setCost(float $cost)
    {
        parent::setCost($cost);
        $this->token_payment->cost = round($cost, 1);
    }

    /**
     * Checks whether the user is allowed to do the current
     * search in an authorized environment
     * 
     * @param bool $current_request Tokenauthorization does not always send valid tokens but expects interface elements as if the request is authorized
     */
    public function canDoAuthenticatedSearch(bool $current_request = true)
    {
        if ($current_request === true || empty($this->tokenauthorization_header)) {
            return parent::canDoAuthenticatedSearch($current_request);
        } else {
            switch ($this->tokenauthorization_header) {
                case "full":
                case "low":
                    return true;
                default:
                    return false;
            }
        }
    }

    /**
     * Returns a link to the correct key icon corresponding to the current key charge
     */
    public function getKeyIcon()
    {
        $keyIcon = parent::getKeyIcon();

        if (!empty($this->tokenauthorization_header)) {
            switch ($this->tokenauthorization_header) {
                case "full":
                    $keyIcon = "/img/svg-icons/key-full.svg";
                    break;
                case "low":
                    $keyIcon = "/img/svg-icons/key-low.svg";
                    break;
                case "empty":
                    $keyIcon = "/img/svg-icons/key-empty.svg";
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
        $tooltip = parent::getKeyTooltip();

        if (!empty($this->tokenauthorization_header)) {
            switch ($this->tokenauthorization_header) {
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

    /**
     * Tokenauthorization is always authenticated
     */
    public function isAuthenticated(): bool
    {
        return true;
    }

    /**
     * @return AnonymousTokenPayment
     */
    public function getToken()
    {
        return $this->token_payment;
    }

}