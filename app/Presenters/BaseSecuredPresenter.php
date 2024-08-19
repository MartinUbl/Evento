<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

class BaseSecuredPresenter extends BasePresenter
{
    /** @var \App\Model\UserDetailsModel */
    protected $userDetailsModel = null;

    /** @var \Martinubl\Bakalapi\Baka_UserInfo */
    private $userInfo = null;

    public function __construct(\App\Model\UserDetailsModel $userDetailsModel) {
        parent::__construct();

        $this->userDetailsModel = $userDetailsModel;
    }

    public function startup(): void {

        parent::startup();

        if (!$this->api->hasToken()) {
            $this->redirect("Login:");
        }

        if ($this->getPresenter()->getName() !== "Profile") {
            $details = $this->userDetailsModel->getUserDetails($this->api->getUserInfo()->uid);
            if (!$details || empty($details->email) || empty($details->phone)) {
                $this->redirect("Profile:", [ 'rs' => 'fill' ]);
            }
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

    public function handleLogout() {
        $this->api->logout();
        $this->redirect('Login:');
    }
}
