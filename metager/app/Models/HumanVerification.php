<?php

namespace App\Models;

use Cache;
use URL;

class HumanVerification
{

    const CACHE_PREFIX = "humanverification";
    private $users = [];
    private $user = [];

    public readonly ?string $id;
    public readonly ?string $uid;
    public readonly ?bool $alone;
    public readonly ?int $whitelisted_accounts;
    public readonly ?int $not_whitelisted_accounts;
    public int $request_count_all_users = 0;

    public function __construct()
    {
        $request = \request();
        $ip = $request->ip();

        $id = hash("sha1", $ip);
        $agent = $_SERVER["AGENT"];
        $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");

        $this->id = $id;
        $this->uid = $uid;

        # Get all Users of this IP
        $this->users = Cache::get(self::CACHE_PREFIX . "." . $id, []);
        if ($this->users === null) {
            $this->users = [];
        }
        $this->removeOldUsers();

        if (empty($this->users[$this->uid])) {
            $this->user = [
                'uid' => $this->uid,
                'id' => $this->id,
                'unusedResultPages' => 0,
                'whitelist' => false,
                'locked' => false,
                "lockedKey" => "",
                "expiration" => now()->addWeeks(2),
            ];
            $this->users[$this->uid] = $this->user;
        } else {
            $this->user = $this->users[$uid];
        }

        # Lock out everyone in a Bot network
        # Find out how many requests this IP has made
        $sum = 0;
        // Defines if this is the only user using that IP Adress
        $alone = true;
        $whitelisted_accounts = 0;
        $not_whitelisted_accounts = 0;
        foreach ($this->users as $uidTmp => $userTmp) {
            if (!$userTmp["whitelist"]) {
                $not_whitelisted_accounts++;
                $sum += $userTmp["unusedResultPages"];
                if ($userTmp["uid"] !== $uid) {
                    $alone = false;
                }
            } else {
                $whitelisted_accounts++;
            }
        }
        $this->alone = $alone;
        $this->request_count_all_users = $sum;
        $this->whitelisted_accounts = $whitelisted_accounts;
        $this->not_whitelisted_accounts = $not_whitelisted_accounts;
    }

    function lockUser()
    {
        $this->user["locked"] = true;
        $this->saveUser();
    }

    function unlockUser()
    {
        $this->user["locked"] = false;
        $this->saveUser();
    }

    /**
     * Returns Whether this user is locked
     * 
     * @return bool
     */
    function isLocked()
    {
        return $this->user["locked"];
    }

    function saveUser()
    {
        $userList = Cache::get(self::CACHE_PREFIX . "." . $this->id, []);
        $expiration = now()->addHours(72);
        foreach ($userList as $user) {
            if ($user["whitelist"]) {
                $expiration = now()->addWeeks(2);
            }
        }
        $this->user["expiration"] = $expiration;
        $userList[$this->uid] = $this->user;
        Cache::put(self::CACHE_PREFIX . "." . $this->id, $userList, $expiration);
        $this->users = $userList;
    }

    /**
     * Deletes the data for this user
     */
    private function deleteUser()
    {
        $userList = Cache::get(self::CACHE_PREFIX . "." . $this->id, []);

        if (sizeof($userList) === 1) {
            // This user is the only one for this IP
            Cache::forget(self::CACHE_PREFIX . "." . $this->id);
        } else {
            $new_user_list = [];
            $expiration = now()->addHours(72);
            foreach ($userList as $user) {
                if ($user["uid"] !== $this->uid) {
                    $new_user_list[] = $user;
                    if ($user["whitelist"]) {
                        $expiration = now()->addWeeks(2);
                    }
                }
            }
            Cache::put(self::CACHE_PREFIX . "." . $this->id, $new_user_list, $expiration);
        }
    }

    /**
     * Function is called for a user on specific actions
     * It will either delete the data for this user or put him on a whitelist and reset his counter
     */
    public function verifyUser()
    {
        # Check if we have to whitelist the user or if we can simply delete the data
        if (!$this->alone) {
            # Whitelist
            $this->user["whitelist"] = true;
            $this->user["unusedResultPages"] = 0;
            $this->saveUser();
        } else {
            $this->deleteUser();
        }
    }

    public function unverifyUser()
    {
        $this->user["whitelist"] = false;
        $this->saveUser();
    }

    public function setUnusedResultPage($unusedResultPages)
    {
        $this->user["unusedResultPages"] = $unusedResultPages;
        $this->saveUser();
    }

    public function isWhiteListed()
    {
        return $this->user["whitelist"];
    }

    public function setWhiteListed(bool $whitelisted)
    {
        return $this->user["whitelist"] = $whitelisted;
        $this->saveUser();
    }

    function addQuery()
    {
        $this->user["unusedResultPages"]++;

        if ($this->alone || $this->user["whitelist"]) {
            # This IP doesn't need verification yet
            # The user currently isn't locked

            # We have different security gates:
            #   50 and then every 25 => Captcha validated Result Pages
            # If the user shows activity on our result page the counter will be deleted
            if ($this->user["unusedResultPages"] === 50 || ($this->user["unusedResultPages"] > 50 && $this->user["unusedResultPages"] % 25 === 0)) {
                $this->lockUser();
            }
        }
        $this->saveUser();
    }

    function removeOldUsers()
    {
        $newUserlist = [];
        $now = now();

        $changed = false;
        foreach ($this->users as $uid => $user) {
            $id = $user["id"];
            if ($now < $user["expiration"]) {
                $newUserlist[$user["uid"]] = $user;
            } else {
                $changed = true;
            }
        }

        if ($changed) {
            Cache::put(self::CACHE_PREFIX . "." . $user["id"], $newUserlist, now()->addWeeks(2));
        }

        $this->users = $newUserlist;
    }

    public function getVerificationCount()
    {
        return $this->user["unusedResultPages"];
    }

    /**
     * Returns the number of users associated to this IP
     */
    public function getUserCount()
    {
        return sizeof($this->users);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getUserList()
    {
        return $this->users;
    }
}
