<?php

namespace App\Model;

use Nette;

class EventsModel extends BaseModel {

    public $implicitTable = 'events';

    public function createEvent($eventName, $description, $createdAt, $startDate, $endDate, $regStartDate, $regEndDate, $ownerUid) {

        $dummyName = 'DUMMY_EVENT_NAME'.date('Y-m-d-h-i-s');

        $this->getTable()->insert([
            'name' => $dummyName,
            'description'=> $description,
            'created' => $createdAt,
            'datetime_from' => $startDate,
            'datetime_to' => $endDate,
            'registration_date_from' => $regStartDate,
            'registration_date_to' => $regEndDate,
            'owner_uid' => $ownerUid
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

    public function updateEvent($id, $eventName, $description, $createdAt, $startDate, $endDate, $regStartDate, $regEndDate) {

        $this->getTable()->where('id', $id)->update([
            'name' => $eventName,
            'description'=> $description,
            'created' => $createdAt,
            'datetime_from' => $startDate,
            'datetime_to' => $endDate,
            'registration_date_from' => $regStartDate,
            'registration_date_to' => $regEndDate
        ]);

    }

    public function deleteEvent($id) {
        $this->getTable()->where('id', $id)->delete();
    }
};
