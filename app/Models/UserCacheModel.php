<?php

namespace App\Model;

use Nette;

class UserCacheModel extends BaseModel {

    public $implicitTable = 'user_cache';

    public static function passHash($pass) {
        $hash = sha1('salty#Salt'.$pass.'42');

        return $hash;
    }

    public function refreshUserCacheRecord($uid, $username, $password, $token, $tokenExpiry, $userInfoString) {

        $rec = $this->getTable()->where('uid', $uid)->fetch();
        /*if ($rec && $rec->expires_at && $rec->expires_at < time()) {
            return;
        }*/

        // when to "recache" user info
        $expiry = time() + 24*60*60; // 1 day

        if ($rec) {
            $this->getTable()->where('uid', $uid)->update([
                'username' => $username,
                'password_hash' => self::passHash($password),
                'token'=> $token,
                'token_expiry' => $tokenExpiry,
                'user_info' => $userInfoString,
                'expires_at' => $expiry
            ]);
        }
        else {
            $this->getTable()->insert([
                'uid'=> $uid,
                'username' => $username,
                'password_hash' => self::passHash($password),
                'token'=> $token,
                'token_expiry' => $tokenExpiry,
                'user_info' => $userInfoString,
                'expires_at' => $expiry
            ]);
        }
    }

    public function tryAuth($username, $password, $forceExpired = false) {
        $rec = $this->getTable()->where('username', $username)->fetch();
        if (!$rec) {
            return [ 'error' => 'no_user' ];
        }

        if (!$forceExpired && $rec->expires_at && $rec->expires_at < time()) {
            return [ 'error' => 'expired' ];
        }

        if (!$forceExpired && $rec->token_expiry && $rec->token_expiry < time()) {
            return [ 'error' => 'expired_api' ];
        }

        if ($rec->password_hash !== self::passHash($password)) {
            return [ 'error' => 'password' ];
        }

        $user = [
            'username' => $username,
            'token' => $rec->token,
            'token_expiry' => $rec->token_expiry,
            'user_info' => $rec->user_info
        ];

        return $user;
    }

};
