<?php

namespace App\Model;

class EventsModel extends BaseModel {

    public $implicitTable = 'events';

    public function getEventbyId($id) {
        return $this->getTable()->where('id', $id)->fetch();
    }

    public function createEvent($eventName, $description, $createdAt, $startDate, $endDate, $regStartDate, $regEndDate, $ownerUid, $publicFlag, $restrictUserType = null) {

        $dummyName = 'DUMMY_EVENT_NAME'.date('Y-m-d-h-i-s');

        $this->getTable()->insert([
            'name' => $dummyName,
            'description'=> $description,
            'created' => $createdAt,
            'datetime_from' => $startDate,
            'datetime_to' => $endDate,
            'registration_date_from' => $regStartDate,
            'registration_date_to' => $regEndDate,
            'owner_uid' => $ownerUid,
            'restrict_user_type' => $restrictUserType,
            'public' => $publicFlag ? 1 : 0
        ]);

        $evtRec = $this->getTable()->where('name', $dummyName)->fetch();
        if (!$evtRec) {
            return false;
        }

        $this->getTable()->where('id', $evtRec->id)->update([
            'name' => $eventName
        ]);

        return $evtRec->id;
    }

    public function updateEvent($id, $eventName, $description, $startDate, $endDate, $regStartDate, $regEndDate, $publicFlag, $restrictUserType = null) {

        $this->getTable()->where('id', $id)->update([
            'name' => $eventName,
            'description'=> $description,
            'datetime_from' => $startDate,
            'datetime_to' => $endDate,
            'registration_date_from' => $regStartDate,
            'registration_date_to' => $regEndDate,
            'restrict_user_type' => $restrictUserType,
            'public' => $publicFlag ? 1 : 0
        ]);

    }

    public function getFutureEvents($publicOnly = true, $includeOwnerUid = null) {
        $sel = $this->getTable()->where('registration_date_from > ?', date('Y-m-d H:i:s'));
        if ($publicOnly) {
            $sel->where('public', 1);
        }
        if ($includeOwnerUid) {
            $sel->whereOr([ 'owner_uid' => $includeOwnerUid ]);
        }

        return $sel;
    }

    public function getOnboardEvents($publicOnly = true, $includeOwnerUid = null) {
        $now = date('Y-m-d H:i:s');

        $sel = $this->getTable()->where('registration_date_from < ?', $now)
                                ->where('registration_date_to > ?', $now);
        if ($publicOnly) {
            $sel->where('public', 1);
        }
        if ($includeOwnerUid) {
            $sel->whereOr([ 'owner_uid' => $includeOwnerUid ]);
        }

        return $sel;
    }

    public function getClosedCurrentEvents($publicOnly = true, $includeOwnerUid = null) {
        $now = date('Y-m-d H:i:s');

        $sel = $this->getTable()->where('registration_date_to < ?', $now)
                                ->where('datetime_to > ?', $now);
        if ($publicOnly) {
            $sel->where('public', 1);
        }
        if ($includeOwnerUid) {
            $sel->whereOr([ 'owner_uid' => $includeOwnerUid ]);
        }

        return $sel;
    }

    public function getPastEvents($publicOnly = true, $includeOwnerUid = null) {
        $now = date('Y-m-d H:i:s');

        $sel = $this->getTable()->where('datetime_to < ?', $now);
        if ($publicOnly) {
            $sel->where('public', 1);
        }
        if ($includeOwnerUid) {
            $sel->whereOr([ 'owner_uid' => $includeOwnerUid ]);
        }

        return $sel;
    }

    public function getAllEvents($publicOnly = true, $includeOwnerUid = null, $userType = null) {
        $sel = $this->getTable();

        $selectArray = [
        ];

        if ($publicOnly) {
            $selectArray['public'] = [1];
        }
        else {
            $selectArray['public'] = [0, 1];
        }

        if ($includeOwnerUid) {
            $selectArray['owner_uid'] = $includeOwnerUid;
        }

        $sel->whereOr($selectArray);

        if ($userType !== null) {
            if ($includeOwnerUid) {
                $sel->whereOr(['owner_uid' => $includeOwnerUid, 'restrict_user_type' => [null, $userType]]);
            }
            else {
                $sel->whereOr(['restrict_user_type' => [null, $userType]]);
            }
        }

        return $sel;
    }

    public function getEventsOwnedBy($uid) {
        return $this->getTable()->where('owner_uid', $uid)->order('datetime_from ASC');
    }

    public function deleteEvent($id) {
        $this->getTable()->where('id', $id)->delete();
    }

    public function getEventActivities($eventId) {
        return $this->table('event_activities')->where('events_id', $eventId)->order('group ASC');
    }

    public function getEventActivityById($activityId) {
        return $this->table('event_activities')->where('id', $activityId)->fetch();
    }

    public function addEventActivity($eventId, $activityName, $description, $place, $instructions, $group, $capacity) {
        $this->table('event_activities')->insert([
            'events_id' => $eventId,
            'name' => $activityName,
            'description' => $description,
            'place' => $place,
            'instructions' => $instructions,
            'group' => $group,
            'capacity' => $capacity
        ]);
    }

    public function editEventActivity($activityId, $activityName, $description, $place, $instructions, $group, $capacity) {
        $this->table('event_activities')->where('id', $activityId)->update([
            'name' => $activityName,
            'description' => $description,
            'place' => $place,
            'instructions' => $instructions,
            'group' => $group,
            'capacity' => $capacity
        ]);
    }

    public function deleteEventActivity($activityId) {
        $this->table('event_activities')->where('id', $activityId)->delete();
    }

    public function getActivityParticipants($activityId) {
        return $this->table('event_participants')->where('event_activities_id', $activityId);
    }

    public function fetchDecodedActivityParticipants($activityId) {
        $ret = [];
        $tbl = $this->table('event_participants')->where('event_activities_id', $activityId);
        foreach ($tbl as $part) {
            $decoded = json_decode($part->user_cache->user_info);
            $ret[] = [
                'uid' => $part->user_cache->uid,
                'username' => $part->user_cache->username,
                'name' => isset($decoded->fullName) ? $decoded->fullName : $part->user_cache->username,
                'class' => isset($decoded->className) ? $decoded->className : null
            ];
        }
        return $ret;
    }

    public function getEventActivitiesParticipants($eventId) {

        $ret = [];

        $activities = $this->getEventActivities($eventId);
        foreach ($activities as $act) {
            $ret[$act->id] = $this->fetchDecodedActivityParticipants($act->id);
        }

        return $ret;
    }

    public function addActivityParticipant($activityId, $uid, $createdAt) {
        $this->table('activity_participant')->insert([
            'user_cache_uid' => $uid,
            'event_activities_id' => $activityId,
            'created_at' => $createdAt
        ]);
    }

    public function getEventGroupRules($eventId) {
        return $this->table('event_group_rules')->where('events_id', $eventId);
    }

    public function getEventGroupRuleArray($eventId) {
        $rules = $this->getEventGroupRules($eventId);

        $ret = [
            'exclude' => [],
            'enforce' => []
        ];

        foreach ($rules as $r) {
            if (!isset($ret[$r['rule']][$r['activity_group_id']])) {
                $ret[$r['rule']][$r['activity_group_id']] = [];
            }
            $ret[$r['rule']][$r['activity_group_id']][] = $r['other_activity_group_id'];
        }

        return $ret;
    }

    public function updateEventGroupRules($eventId, $rules) {

        $this->database->beginTransaction();

        try {
            $this->table('event_group_rules')->where('events_id', $eventId)->delete();

            foreach ($rules as $rule => $gr1) {
                foreach ($gr1 as $gid => $arr) {
                    foreach ($arr as $gid2) {
                        $this->addEventGroupRule($eventId, $gid, $gid2, $rule);
                    }
                }
            }

            $this->database->commit();
        }
        catch (\Exception $ex) {
            $this->database->rollBack();
            return false;
        }
    }

    public function getActivityGroups() {
        return $this->table('activity_group');
    }

    public function addEventGroupRule($eventId, $groupSrc, $groupDst, $rule) {
        if ($rule !== 'exclude' && $rule !== 'enforce') {
            throw new \Exception("Invalid group rule: ".$rule. "; valid group rules are: exclude, enforce");
        }

        // ideal (consistent) scenario: a) sel and sel2 are both null; b) sel and sel2 are valid and contains identical rules
        // we allow, that one rule exists and the other way around does not; either way it must not conflict with what is already present in DB

        $sel = $this->getEventGroupRules($eventId)->where('activity_group_id', $groupSrc)->where('other_activity_group_id', $groupDst)->fetch();
        if ($sel) {
            if ($sel->rule !== $rule) {
                return false;
            }
            else {
                return true;
            }
        }

        $sel2 = $this->getEventGroupRules($eventId)->where('activity_group_id', $groupDst)->where('other_activity_group_id', $groupSrc)->fetch();
        if ($sel2) {
            if ($sel2->rule !== $rule) {
                return false;
            }
            else {
                return true;
            }
        }

        if (!$sel) {
            $this->table('event_group_rules')->insert([
                'events_id' => $eventId,
                'activity_group_id' => $groupSrc,
                'other_activity_group_id' => $groupDst,
                'rule' => $rule
            ]);
        }

        if (!$sel2 && $groupDst !== $groupSrc) {
            $this->table('event_group_rules')->insert([
                'events_id' => $eventId,
                'activity_group_id' => $groupDst,
                'other_activity_group_id' => $groupSrc,
                'rule' => $rule
            ]);
        }
    }

    public function getParticipantActivities($uid, $eventId) {
        $act = $this->getEventActivities($eventId);
        $aids = [];
        foreach ($act as $a) {
            $aids[] = $a->id;
        }

        $joined = $this->table('event_participants')->where('user_cache_uid', $uid)->where('event_activities_id', $aids);
        $jarr = [];
        foreach ($joined as $j) {
            $jarr[] = $j->event_activities_id;
        }

        return $jarr;
    }

    public function getParticipantActivityGroups($uid, $eventId) {
        $act = $this->getEventActivities($eventId);
        $aids = [];
        foreach ($act as $a) {
            $aids[] = $a->id;
        }

        $joined = $this->table('event_participants')->where('user_cache_uid', $uid)->where('event_activities_id', $aids);
        $jarr = [];
        foreach ($joined as $j) {
            $jarr[] = $j->event_activities->group;
        }

        return $jarr;
    }

    public function fetchActivitiesParticipantCount($eventId) {
        $act = $this->getEventActivities($eventId);
        $aids = [];
        foreach ($act as $a) {
            $aids[] = $a->id;
        }

        $joined = $this->table('event_participants')->where('event_activities_id', $aids);
        $jarr = [];
        foreach ($joined as $j) {
            if (!isset($jarr[$j->event_activities_id]))
                $jarr[$j->event_activities_id] = 0;
            $jarr[$j->event_activities_id]++;
        }

        return $jarr;
    }

    public function joinParticipant($uid, $activityId) {
        $this->table('event_participants')->insert([
            'user_cache_uid' => $uid,
            'event_activities_id' => $activityId
        ]);
    }

    public function unjoinParticipant($uid, $activityId) {
        $this->table('event_participants')->where('user_cache_uid', $uid)->where('event_activities_id', $activityId)->delete();
    }
};
