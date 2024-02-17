<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class HomePresenter extends BasePresenter
{
    /** @var \Martinubl\Bakalapi\Baka_UserInfo */
    private $userInfo = null;

    public function startup(): void {

        parent::startup();

        if (!$this->api->hasToken()) {
            $this->redirect("Login:");
        }

        $this->userInfo = $this->api->getUserInfo();
        $this->template->userInfo = $this->userInfo;
        $this->template->roleToString = function($role) {
            if ($role == \Martinubl\Bakalapi\Baka_UserType::STUDENT) {
                return "žák";
            }
            else if ($role == \Martinubl\Bakalapi\Baka_UserType::TEACHER) {
                return "učitel";
            }
            else {
                return "neznámá";
            }
        };
    }

    public function actionDefault(): void {
        
    }
}
