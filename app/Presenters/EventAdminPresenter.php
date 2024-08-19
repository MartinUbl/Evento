<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Misc\CSVResponse;
use Nette\Application\UI\Form;

final class EventAdminPresenter extends BaseSecuredPresenter
{
    private $selectedEvent = null;
    private $selectedActivity = null;
    
    /** @var \App\Model\EventsModel */
    private $eventsModel;

    public function __construct(\App\Model\UserDetailsModel $userDetailsModel, \App\Model\EventsModel $eventsModel) {
        parent::__construct($userDetailsModel);

        $this->eventsModel = $eventsModel;
    }

    public function startup(): void {
        parent::startup();

        if ($this->api->getUserInfo()->role !== \Martinubl\Bakalapi\Baka_UserType::TEACHER) {
            $this->flashMessage("Na tuto akci nemáte dostatečná oprávnění!", "error");
            $this->redirect("Home:");
        }
    }

    public function actionDefault(): void {
        $this->redirect("EventAdmin:list");
    }

    public function actionAdd() {
        //
    }

    public function actionEdit($id) {
        $this->selectedEvent = $this->eventsModel->getEventbyId($id);
        if (!$this->selectedEvent || $this->selectedEvent->owner_uid !== $this->api->getUserInfo()->uid) {
            $this->flashMessage("Na úpravu této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("EventAdmin:list");
        }
    }

    public function actionList() {
        $this->template->events = $this->eventsModel->getEventsOwnedBy($this->api->getUserInfo()->uid);
    }

    public function actionListActivities($id) {
        $this->selectedEvent = $this->eventsModel->getEventbyId($id);
        if (!$this->selectedEvent || $this->selectedEvent->owner_uid !== $this->api->getUserInfo()->uid) {
            $this->flashMessage("Na úpravu této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("EventAdmin:list");
        }

        $this->template->activities = $this->eventsModel->getEventActivities($id);
        $this->template->evt = $this->selectedEvent;
        $this->template->activityCounts = $this->eventsModel->fetchActivitiesParticipantCount($id);

        $this->template->groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        $this->template->printGroup = function($rec) {
            return $this->printGroup($rec);
        };
        $this->template->printGroupSq = function($rec) {
            return $this->printGroupSq($rec);
        };
    }

    public function actionAddActivity($id) {
        $this->selectedEvent = $this->eventsModel->getEventbyId($id);
        if (!$this->selectedEvent || $this->selectedEvent->owner_uid !== $this->api->getUserInfo()->uid) {
            $this->flashMessage("Na úpravu této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("EventAdmin:list");
        }
    }

    public function actionEditActivity($id) {
        $this->selectedActivity = $this->eventsModel->getEventActivityById($id);
    }

    public function actionGroupRules($id) {
        $this->selectedEvent = $this->eventsModel->getEventbyId($id);
        if (!$this->selectedEvent || $this->selectedEvent->owner_uid !== $this->api->getUserInfo()->uid) {
            $this->flashMessage("Na úpravu této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("EventAdmin:list");
        }

        $this->template->event = $this->selectedEvent;

        $this->template->groupRules = $this->eventsModel->getEventGroupRuleArray($id);
        $this->template->groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        $this->template->printGroup = function($rec) {
            return $this->printGroup($rec);
        };
        $this->template->printGroupSq = function($rec) {
            return $this->printGroupSq($rec);
        };
    }

    public function actionParticipants($id) {
        $this->selectedEvent = $this->eventsModel->getEventbyId($id);
        if (!$this->selectedEvent || $this->selectedEvent->owner_uid !== $this->api->getUserInfo()->uid) {
            $this->flashMessage("Na úpravu této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("EventAdmin:list");
        }

        $this->template->event = $this->selectedEvent;

        $this->template->groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        $this->template->printGroup = function($rec) {
            return $this->printGroup($rec);
        };
        $this->template->printGroupSq = function($rec) {
            return $this->printGroupSq($rec);
        };
        $this->template->printGroupSqOnly = function($rec) {
            return $this->printGroupSqOnly($rec);
        };

        $this->template->activities = $this->eventsModel->getEventActivities($id);
        $this->template->activityParticipants = $this->eventsModel->getEventActivitiesParticipants($id);
    }

    public function handleDeleteActivity($activityId) {
        $this->eventsModel->deleteEventActivity($activityId);
        $this->redirect('this');
    }

    public function handleDuplicateActivity($activityId) {
        $act = $this->eventsModel->getEventActivityById($activityId);
        if (!$act) {
            $this->redirect('this');
        }

        $this->eventsModel->addEventActivity($act->events_id, $act->name, $act->description, $act->place, $act->instructions, $act->group, $act->capacity);
        $this->redirect('this');
    }

    public function handleExportEventParticipants($eventId) {
        $event = $this->eventsModel->getEventbyId($eventId);

        $activities = $this->eventsModel->getEventActivities($eventId);
        $groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        $stream = [];

        foreach ($activities as $act) {
            $participants = $this->eventsModel->getActivityParticipants($act->id);

            if ($participants->count() > 0) {
                $stream[] = "$act->name;".$groups[$act->group]['name'];
                $stream[] = "Účastník;Třída";

                foreach ($participants as $pt) {
                    $info = json_decode($pt->user_cache->user_info);
                    $stream[] = $info->fullName.";".$info->className;
                }
            }
        }

        $resp = new CSVResponse("export.csv", $stream);

        $this->sendResponse($resp);
        $this->terminate();
    }

    public function handleExportEventClassParticipants($eventId) {
        $event = $this->eventsModel->getEventbyId($eventId);

        $activities = $this->eventsModel->getEventActivities($eventId);
        $groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        $stream = [];

        $byClasses = [];

        foreach ($activities as $act) {
            $participants = $this->eventsModel->fetchDecodedActivityParticipants($act->id);
            foreach ($participants as $part) {
                if (!isset($byClasses[$part['class']])) {
                    $byClasses[$part['class']] = [];
                }

                $byClasses[$part['class']][] = $part['name'].";".$act->name;
            }
        }

        foreach ($byClasses as $cls => $bc) {

            sort($bc);

            $stream[] = $cls;
            foreach ($bc as $part) {
                $stream[] = $part;
            }
        }

        $resp = new CSVResponse("export_tu.csv", $stream);

        $this->sendResponse($resp);
        $this->terminate();
    }

    public function createComponentGroupRuleForm() {
        $form = new Form();

        $rules = $this->eventsModel->getEventGroupRuleArray($this->selectedEvent->id);
        $groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        foreach ($groups as $gid => $g) {
            $cont = $form->addContainer('ex_group_'.$gid);

            foreach ($groups as $gid2 => $g2) {
                $def = false;
                if (isset($rules['exclude']) && isset($rules['exclude'][$gid]) && in_array($gid2, $rules['exclude'][$gid])) {
                    $def = true;
                }

                $cont->addCheckbox('og_'.$gid2, $g2['name'])->setAttribute('data-gr', $gid2)
                     ->setDefaultValue($def);
            }

            $cont2 = $form->addContainer('enf_group_'.$gid);

            foreach ($groups as $gid2 => $g2) {
                $def = false;
                if (isset($rules['enforce']) && isset($rules['enforce'][$gid]) && in_array($gid2, $rules['enforce'][$gid])) {
                    $def = true;
                }

                $cont2->addCheckbox('og_'.$gid2, $g2['name'])->setAttribute('data-gr', $gid2)
                      ->setDefaultValue($def);
            }
        }

        $form->addSubmit('submit', 'Uložit');

        $form->onSuccess[] = [$this, 'groupRuleFormSuccess'];

        return $form;
    }

    public function groupRuleFormSuccess(Form $form) {
        $vals = $form->values;

        $groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');

        $rules = [
            'exclude' => [],
            'enforce' => []
        ];

        foreach ($groups as $gid => $g) {
            foreach ($groups as $gid2 => $g2) {
                $checked = $vals->{"ex_group_".$gid}->{"og_".$gid2};
                
                if ($checked) {
                    if (!isset($rules['exclude'][$gid])) {
                        $rules['exclude'][$gid] = [];
                    }
                    $rules['exclude'][$gid][] = $gid2;
                }

                $checked2 = $vals->{"enf_group_".$gid}->{"og_".$gid2};
                
                if ($checked2) {
                    if (!isset($rules['enforce'][$gid])) {
                        $rules['enforce'][$gid] = [];
                    }
                    $rules['enforce'][$gid][] = $gid2;
                }
            }
        }

        $this->eventsModel->updateEventGroupRules($this->selectedEvent->id, $rules);

        $this->redirect('this');
    }

    public function createComponentEventEditForm() {
        $form = new Form();

        $form->addText('name', 'Název události (projektového dne, ...)')
             ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->name : null)
             ->setRequired('Toto pole je povinné!');
    
        $form->addTextArea('description', 'Popis události - co se bude dít?')
             ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->description : null)
             ->setRequired('Toto pole je povinné!');

        $form->addText("datetime_from", "Kdy událost začíná?")
             ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->datetime_from : null)
             ->setRequired('Toto pole je povinné!');
        $form->addText("datetime_to", "Kdy událost končí?")
             ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->datetime_to : null)
             ->setRequired('Toto pole je povinné!');

        $form->addText("registration_date_from", "Kdy začínají registrace?")
             ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->registration_date_from : null)
             ->setRequired('Toto pole je povinné!');
        $form->addText("registration_date_to", "Kdy končí registrace?")
             ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->registration_date_to : null)
             ->setRequired('Toto pole je povinné!');

        $form->addSelect("restrict_user_type", "Omezit jen na typ účastníka?", [
            \Martinubl\Bakalapi\Baka_UserType::STUDENT => "Jen žáci",
            \Martinubl\Bakalapi\Baka_UserType::TEACHER => "Jen učitelé",
            null => "Žáci i učitelé"
        ])
            ->setDefaultValue($this->selectedEvent ? $this->selectedEvent->restrict_user_type : null);

        $cb = $form->addCheckbox("public", "Zveřejnit událost?");
        $cb->setDefaultValue($this->selectedEvent ? ($this->selectedEvent->public ? true : false) : null);
        $cb->getLabelPrototype()->addClass('form-check-label');
        $cb->getControlPrototype()->addClass('form-check-input');

        $form->addSubmit('submit', $this->selectedEvent ? 'Uložit změny' : 'Přidat událost!');

        $form->onSuccess[] = [$this, 'eventEditFormSuccess'];

        return $form;
    }

    public function eventEditFormSuccess(Form $form) {

        $vals = $form->values;

        if ($this->selectedEvent) {
            $this->eventsModel->updateEvent($this->selectedEvent->id, $vals->name, $vals->description, $vals->datetime_from, $vals->datetime_to, $vals->registration_date_from,
                    $vals->registration_date_to, $vals->public, $vals->restrict_user_type);
        }
        else {
            $this->eventsModel->createEvent($vals->name, $vals->description, date('Y-m-d H:i:s'), $vals->datetime_from, $vals->datetime_to, $vals->registration_date_from,
                    $vals->registration_date_to, $this->api->getUserInfo()->uid, $vals->public, $vals->restrict_user_type);
        }

        $this->redirect('Home:');
    }

    public function createComponentActivityEditForm() {
        $form = new Form();

        $form->addText("name", "Název aktivity")
            ->setDefaultValue($this->selectedActivity ? $this->selectedActivity->name : null)
            ->setRequired('Vyplňte název aktivity!');

        $form->addText("description", "Popis aktivity")
            ->setDefaultValue($this->selectedActivity ? $this->selectedActivity->description : null)
            ->setRequired('Vyplňte popis aktivity!');

        $form->addText("instructions", "Dodatečné instrukce")
            ->setDefaultValue($this->selectedActivity ? $this->selectedActivity->instructions : null);

        $form->addText("place", "Místo konání")
            ->setDefaultValue($this->selectedActivity ? $this->selectedActivity->place : null);

        $form->addInteger("capacity", "Kapacita aktivity")
            ->setDefaultValue($this->selectedActivity ? $this->selectedActivity->capacity : null)
            ->setRequired("Vyplňte kapacitu!");

        $gids = [];
        $g = $this->eventsModel->getActivityGroups();
        foreach ($g as $gid => $gr) {
            $gids[$gid] = $gr->name;
        }

        $form->addSelect("group", "Skupina aktivity", $gids)
            ->setDefaultValue(($this->selectedActivity && $this->selectedActivity->group) ? $this->selectedActivity->group : null)
            ->setRequired("Vyplňte kapacitu!");

        $form->addSubmit("submit", "Odeslat");

        $form->onSuccess[] = [$this, 'activityEditFormSuccess'];

        return $form;
    }

    public function activityEditFormSuccess(Form $form) {
        $vals = $form->values;

        if ($this->selectedActivity) {
            $this->eventsModel->editEventActivity($this->selectedActivity->id, $vals->name, $vals->description, $vals->place, $vals->instructions, $vals->group, $vals->capacity);

            $this->redirect("EventAdmin:listActivities", ['id' => $this->selectedActivity->events_id]);
        }
        else {
            $this->eventsModel->addEventActivity($this->selectedEvent->id, $vals->name, $vals->description, $vals->place, $vals->instructions, $vals->group, $vals->capacity);

            $this->redirect("EventAdmin:listActivities", ['id' => $this->selectedEvent->id]);
        }
    }
}
