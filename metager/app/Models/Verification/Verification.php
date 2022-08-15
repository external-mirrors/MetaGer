<?php

namespace App\Models\Verification;

use Cache;

abstract class Verification
{
    protected $cache_prefix = "humanverification";
    protected int $cache_duration_minutes = 360;
    private $users = [];
    private $user = [];

    public readonly ?string $id;
    public readonly ?string $uid;
    public bool $alone;
    public int $whitelisted_accounts;
    public int $not_whitelisted_accounts;
    public int $request_count_all_users = 0;

    /**
     * @param string $id The Group ID for the verifier
     * @param string $uid The User ID for the verifier
     */
    public function __construct($id, $uid)
    {
        $this->id = $id;
        $this->uid = $uid;

        # Get all Users of this IP
        $this->users = Cache::get($this->cache_prefix . "." . $this->id, []);
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
                "expiration" => now()->addMinutes($this->cache_duration_minutes),
            ];
            $this->users[$this->uid] = $this->user;
        } else {
            $this->user = $this->users[$this->uid];
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
                if ($userTmp["uid"] !== $this->uid) {
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

    public static abstract function impersonate($id, $uid);

    function lockUser()
    {
        if ($this->user["unusedResultPages"] === 0) {
            $this->user["unusedResultPages"] = 1;
        }
        $this->user["locked"] = true;
        $this->saveUser();
    }

    function unlockUser()
    {
        if ($this->user["locked"]) {
            $this->user["locked"] = false;
            $this->saveUser();
        }
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
        $userList = Cache::get($this->cache_prefix . "." . $this->id, []);
        $expiration = now()->addMinutes($this->cache_duration_minutes);

        // Todo remove setting expiration for all users
        // Just added to apply the new expiration policy to all existing entries
        // Will not be needed in the future
        foreach ($userList as $index => $user) {
            if ($expiration < $user["expiration"]) {
                $userList[$index]["expiration"] = $expiration;
            }
        }

        $this->user["expiration"] = $expiration;

        $userList[$this->uid] = $this->user;
        Cache::put($this->cache_prefix . "." . $this->id, $userList, $expiration);
        $this->users = $userList;
    }

    /**
     * Deletes the data for this user
     */
    private function deleteUser()
    {
        $userList = Cache::get($this->cache_prefix . "." . $this->id, []);

        if (sizeof($userList) === 1) {
            // This user is the only one for this IP
            Cache::forget($this->cache_prefix . "." . $this->id);
        } else {
            $new_user_list = [];
            $expiration = now()->addMinutes($this->cache_duration_minutes);
            foreach ($userList as $user) {
                if ($user["uid"] !== $this->uid) {
                    $new_user_list[] = $user;
                }
            }
            Cache::put($this->cache_prefix . "." . $this->id, $new_user_list, $expiration);
        }
    }

    /**
     * Function is called for a user on specific actions
     * It will either delete the data for this user or put him on a whitelist and reset his counter
     */
    public function verifyUser()
    {
        # Check if we have to whitelist the user or if we can simply delete the data
        if ($this->alone === false) {
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
            Cache::put($this->cache_prefix . "." . $user["id"], $newUserlist, now()->addWeeks(2));
            $this->users = $newUserlist;
        }

        $this->users = $newUserlist;
    }

    public function getVerificationCount()
    {
        return $this->user["unusedResultPages"];
    }

    function getExpiration()
    {
        return $this->user["expiration"];
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
