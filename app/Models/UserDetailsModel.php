<?php

namespace App\Model;

class UserDetailsModel extends BaseModel {

    public $implicitTable = 'user_details';

    public function getUserDetails($uid) {
        $rec = $this->getTable()->where('uid', $uid)->fetch();
        if (!$rec) {
            return null;
        }
        return $rec;
    }

    public function getUserEmail($uid) {
        $rec = $this->getTable()->where('uid', $uid)->fetch();
        if (!$rec || !isset($rec->email) || empty($rec->email)) {
            return null;
        }
        return $rec->email;
    }

    public function getUserPhone($uid) {
        $rec = $this->getTable()->where('uid', $uid)->fetch();
        if (!$rec || !isset($rec->phone) || empty($rec->phone)) {
            return null;
        }
        return $rec->phone;
    }

    public function setUserEmail($uid, $email) {
        $rec = $this->getTable()->where('uid', $uid)->fetch();
        if ($rec) {
            $this->getTable()->where('uid', $uid)->update([
                'email' => $email
            ]);
        }
        else {
            $this->getTable()->insert([
                'uid' => $uid,
                'email' => $email,
                'phone' => null
            ]);
        }
    }

    public function setUserPhone($uid, $phone) {
        $rec = $this->getTable()->where('uid', $uid)->fetch();
        if ($rec) {
            $this->getTable()->where('uid', $uid)->update([
                'phone' => $phone
            ]);
        }
        else {
            $this->getTable()->insert([
                'uid' => $uid,
                'email' => null,
                'phone' => $phone
            ]);
        }
    }

    public function setUserDetails($uid, $email, $phone) {
        $rec = $this->getTable()->where('uid', $uid)->fetch();
        if ($rec) {
            $this->getTable()->where('uid', $uid)->update([
                'email' => $email,
                'phone' => $phone
            ]);
        }
        else {
            $this->getTable()->insert([
                'uid' => $uid,
                'email' => $email,
                'phone' => $phone
            ]);
        }
    }


};
