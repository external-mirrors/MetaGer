<?php

namespace App\Models\Verification;

use Cache;

class HumanVerification
{
    /** @var Verification[] */
    private $verificators = array();
    public readonly ?string $key;

    public function __construct()
    {
        $this->verificators[] = new IPVerification();

        $this->key = \md5("hv.key." . microtime(true));
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
}
