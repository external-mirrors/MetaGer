<?php

namespace App\Models\Verification;

use App\SearchSettings;
use Cache;
use Crypt;
use Exception;

class HumanVerification
{
    /** @var Verification[] */
    private $verificators = array();
    public readonly ?string $key;

    public function __construct($key = null, $verificators = null)
    {
        if (empty($verificators)) {
            $search_settings = \app()->make(SearchSettings::class);
            $bv_key = $search_settings->bv_key;
            $bv_data = Cache::get($bv_key);

            try {
                $cookie_verificator = new CookieVerification();
                $this->verificators[] = $cookie_verificator;
            } catch (Exception $e) {
                // If we detected usage of a webdriver
                if (!empty($bv_data) && is_array($bv_data) && array_key_exists("webdriver", $bv_data) && $bv_data["webdriver"] === true) {
                    $this->verificators[] = new WebdriverVerification();
                } elseif (!empty($bv_data) && \array_key_exists("csp", $bv_data) && \array_key_exists("error_count", $bv_data["csp"]) && $bv_data["csp"]["error_count"] > 1) {
                    $this->verificators[] = new IPVerification();
                    $this->verificators[] = new CSPVerification($bv_data["csp"]);
                } else {
                    $this->verificators[] = new IPVerification();
                    $this->verificators[] = new AgentVerification();
                }
            }

            $this->key = \md5("hv.key." . microtime(true));
        } else {
            $this->key = $key;
            $this->verificators = $verificators;
        }
        $ids = [];
        foreach ($this->verificators as $verificator) {
            $ids[] = [
                "class" => $verificator::class,
                "id" => $verificator->id,
                "uid" => $verificator->uid,
            ];
        }
        Cache::put($this->key, $ids, now()->addMinutes(15));
    }

    public static function createFromKey(string $key)
    {
        $ids = Cache::get($key);
        if (empty($ids)) {
            return null;
        }
        $verificators = array();
        foreach ($ids as $hv_entry) {
            $verificators[] = $hv_entry["class"]::impersonate($hv_entry["id"], $hv_entry["uid"]);
        }
        return new self($key, $verificators);
    }

    /**
     * Whether or not there are other users in this group
     */
    public function isAlone()
    {
        $alone = true;
        foreach ($this->verificators as $verificator) {
            if (!$verificator->alone) {
                $alone = false;
                break;
            }
        }
        return $alone;
    }

    /**
     * Is this user whitelisted
     * 
     * @return boolean
     */
    public function isWhiteListed()
    {
        $whitelisted = false;
        foreach ($this->verificators as $verificator) {
            if ($verificator->isWhiteListed()) {
                $whitelisted = true;
                break;
            }
        }
        return $whitelisted;
    }

    /**
     * Checks whether there are many not whitelisted accounts which would lead to a captcha
     * for new users
     * 
     * @return boolean
     */
    public function checkGroupLock()
    {
        foreach ($this->verificators as $verificator) {
            if (!$verificator->alone && $verificator->request_count_all_users >= 50 && !$verificator->isWhiteListed() && $verificator->not_whitelisted_accounts > $verificator->whitelisted_accounts) {
                $verificator->lockUser();
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if one of the verificators is locked
     * 
     * @return boolean
     */
    public function isLocked()
    {
        foreach ($this->verificators as $verificator) {
            if ($verificator->isLocked()) {
                return true;
            }
        }
        return false;
    }

    public function addQuery()
    {
        foreach ($this->verificators as $verificator) {
            $verificator->addQuery();
        }
    }

    /**
     * Reports the highest verification count
     * 
     * @return int[]
     */
    public function getVerificationCount()
    {
        $count = array();
        foreach ($this->verificators as $verificator) {
            $count[] = $verificator->getVerificationCount();
        }
        return $count;
    }

    /**
     * Returns the UIDS for all verificators
     * 
     * @return string[]
     */
    public function getUids()
    {
        $uids = array();
        foreach ($this->verificators as $verificator) {
            $uids[] = $verificator->uid;
        }
        return $uids;
    }

    /**
     * @return Verificator[]
     */
    public function getVerificators()
    {
        return $this->verificators;
    }

    /**
     * @return Verificator[]
     */
    public function serialize()
    {
        $result = array();
        foreach ($this->verificators as $verificator) {
            $result[] = [
                "class" => $verificator::class,
                "id" => $verificator->id,
                "uid" => $verificator->uid,
            ];
        }
        $result = Crypt::encrypt($result);
        return $result;
    }

    public function getUid()
    {
        $uid = "";
        foreach ($this->verificators as $verificator) {
            $uid .= $verificator->uid;
        }
        $uid = \sha1($uid);
        return $uid;
    }

    public function unlockUser()
    {
        foreach ($this->verificators as $verificator) {
            $verificator->unlockUser();
        }
    }

    public function verifyUser()
    {
        foreach ($this->verificators as $verificator) {
            $verificator->verifyUser();
        }
    }

    public function saveUser()
    {
        foreach ($this->verificators as $verificator) {
            $verificator->saveUser();
        }
    }
}
