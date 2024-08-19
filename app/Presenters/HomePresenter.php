<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class HomePresenter extends BaseSecuredPresenter
{
    /** @var \App\Model\EventsModel */
    private $eventsModel;

    public function __construct(\App\Model\UserDetailsModel $userDetailsModel, \App\Model\EventsModel $eventsModel) {
        parent::__construct($userDetailsModel);

        $this->eventsModel = $eventsModel;
    }

    public function startup(): void {

        parent::startup();
    }

    public function actionDefault(): void {
        $events = $this->eventsModel->getAllEvents(true, $this->api->getUserInfo()->uid, $this->api->getUserInfo()->role);
        $this->template->userRole = $this->api->getUserInfo()->role;

        $sorted = [
            "future" => [],
            "onboard" => [],
            "closed" => [],
            "current" => [],
            "past" => []
        ];

        foreach ($events as $evt) {

            $now = new \DateTime();

            if (new \DateTime($evt->registration_date_from) > $now) {
                $sorted["future"][] = $evt;
            }
            else if (new \DateTime($evt->registration_date_to) > $now) {
                $sorted["onboard"][] = $evt;
            }
            else if (new \DateTime($evt->datetime_from) > $now) {
                $sorted["closed"][] = $evt;
            }
            else if (new \DateTime($evt->datetime_to) > $now) {
                $sorted["current"][] = $evt;
            }
            else {
                $sorted["past"][] = $evt;
            }
        }

        $this->template->events = $sorted;
    }

    public function createComponentEventInfoBox() {
        return new \App\Components\EventInfoBoxComponent();
    }
}
