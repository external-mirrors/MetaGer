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
    public int $request_count_all_users = 0;

    public function __construct()
    {
        $request = \request();
        $ip = $request->ip();
        $id = "";
        $uid = "";

        $spamID = \App\Http\Controllers\HumanVerification::couldBeSpammer($ip);
        if (!empty($spamID)) {
            $id = hash("sha1", $spamID);
            $uid = hash("sha1", $spamID . $ip . $_SERVER["AGENT"] . "uid");
        } else {
            $id = hash("sha1", $ip);
            $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");
        }
        unset($_SERVER["AGENT"]);

        $this->id = $id;
        $this->uid = $uid;

        # Get all Users of this IP
        $this->users = Cache::get(self::CACHE_PREFIX . "." . $id, []);
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
        } else {
            $this->user = $this->users[$uid];
        }

        # Lock out everyone in a Bot network
        # Find out how many requests this IP has made
        $sum = 0;
        // Defines if this is the only user using that IP Adress
        $alone = true;
        foreach ($this->users as $uidTmp => $userTmp) {
            if (!$userTmp["whitelist"]) {
                $sum += $userTmp["unusedResultPages"];
                if ($userTmp["uid"] !== $uid) {
                    $alone = false;
                }
            }
        }
        $this->alone = $alone;
        $this->request_count_all_users = $sum;
    }

    function lockUser()
    {
        $this->user["locked"] = true;
        $this->saveUser();
    }

    function isLocked()
    {
        return $this->user["locked"];
    }

    function saveUser()
    {
        $userList = Cache::get(self::CACHE_PREFIX . "." . $this->id, []);
        $expiration = now()->addHours(72);
        if ($this->user["whitelist"]) {
            $expiration = now()->addWeeks(2);
        }
        $this->user["expiration"] = $expiration;
        $userList[$this->uid] = $this->user;
        Cache::put(self::CACHE_PREFIX . "." . $this->id, $userList, now()->addWeeks(2));
        $this->users = $userList;
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

    public function refererLock()
    {
        $referer = URL::previous();
        # Just the URL-Parameter
        if (stripos($referer, "?") !== false) {
            $referer = substr($referer, stripos($referer, "?") + 1);
            $referer = urldecode($referer);
            if (preg_match("/http[s]{0,1}:\/\/metager\.de\/meta\/meta.ger3\?.*?eingabe=([\w\d]+\.){1,2}[\w\d]+/si", $referer) === 1) {
                $this->lockUser();
                return true;
            }
        }
        return false;
    }

    public function getVerificationCount()
    {
        return $this->user["unusedResultPages"];
    }
}
