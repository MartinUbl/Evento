<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class EventPresenter extends BaseSecuredPresenter
{
    /** @var \App\Model\EventsModel */
    private $eventsModel;

    private $selectedEvent = null;
    private $selectedActivity = null;

    public function __construct(\App\Model\UserDetailsModel $userDetailsModel, \App\Model\EventsModel $eventsModel) {
        parent::__construct($userDetailsModel);

        $this->eventsModel = $eventsModel;
    }

    public function startup(): void {

        parent::startup();
    }

    public function actionDefault(): void {
        $this->redirect('Home:');
    }

    public function actionJoin($id): void {
        $this->selectedEvent = $this->eventsModel->getEventbyId($id);
        if (!$this->selectedEvent) {
            $this->flashMessage("Na prohlížení této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("Home:");
        }

        $this->template->activities = $this->eventsModel->getEventActivities($id);
        $this->template->evt = $this->selectedEvent;

        $this->template->groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');
        $this->template->groupRules = $this->eventsModel->getEventGroupRuleArray($id);
        $this->template->joinedActivities = $this->eventsModel->getParticipantActivities($this->api->getUserInfo()->uid, $id);
        $this->template->joinedGroups = $this->eventsModel->getParticipantActivityGroups($this->api->getUserInfo()->uid, $id);
        $this->template->activityCounts = $this->eventsModel->fetchActivitiesParticipantCount($id);

        $this->template->printGroup = function($rec) {
            return $this->printGroup($rec);
        };
        $this->template->printGroupSq = function($rec) {
            return $this->printGroupSq($rec);
        };

        $this->template->excludesGroup = function($group) {
            return $this->excludes($group);
        };
    }

    public function actionActivityDetail($id) {
        $this->selectedActivity = $this->eventsModel->getEventActivityById($id);
        if (!$this->selectedActivity) {
            $this->flashMessage("Na prohlížení této události nemáte dostatečná oprávnění!", "error");
            $this->redirect("Home:");
        }

        $this->template->activity = $this->selectedActivity;

        $this->template->groups = $this->eventsModel->getActivityGroups()->fetchAssoc('id');
        $this->template->groupRules = $this->eventsModel->getEventGroupRuleArray($this->selectedActivity->events_id);
        $this->template->joinedActivities = $this->eventsModel->getParticipantActivities($this->api->getUserInfo()->uid, $this->selectedActivity->events_id);
        $this->template->joinedGroups = $this->eventsModel->getParticipantActivityGroups($this->api->getUserInfo()->uid, $this->selectedActivity->events_id);

        $this->template->printGroup = function($rec) {
            return $this->printGroup($rec);
        };
        $this->template->printGroupSq = function($rec) {
            return $this->printGroupSq($rec);
        };
    }

    public function handleJoinActivity($activityId) {
        $this->eventsModel->joinParticipant($this->api->getUserInfo()->uid, $activityId);
        $this->redirect('this');
    }

    public function handleLeaveActivity($activityId) {
        $this->eventsModel->unjoinParticipant($this->api->getUserInfo()->uid, $activityId);
        $this->redirect('this');
    }

    public function excludes($group) {
        if (!isset($this->template->groupRules) || !isset($this->template->groupRules['exclude']) || !isset($this->template->groupRules['exclude'][$group])) {
            return false;
        }

        if (!isset($this->template->joinedGroups)) {
            return false;
        }

        foreach ($this->template->joinedGroups as $gr) {
            if (in_array($gr, $this->template->groupRules['exclude'][$group]))
                return true;
        }
        return false;
    }
}
