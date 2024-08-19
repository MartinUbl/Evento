<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;

final class ProfilePresenter extends BaseSecuredPresenter
{
    /** @var \Martinubl\Bakalapi\Baka_UserInfo */
    private $userInfo = null;

    /** @var string */
    private $redirectReason = null;

    private $userDetails = null;

    public function startup(): void {

        parent::startup();

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

    public function actionDefault($id = null, $rs = ""): void {
        $this->template->rs = $rs;

        if (!empty($rs)) {
            $this->redirectReason = $rs;
        }

        $this->userDetails = $this->userDetailsModel->getUserDetails($this->userInfo->uid);
    }

    public function createComponentProfileEditForm() {
        $form = new Form();

        $form->addEmail('email', 'E-mail (školní)')
             ->addRule(Form::EMAIL, 'Toto není platný e-mail!')
             ->addRule(Form::PATTERN, 'Zadej svůj školní e-mail!', "^.*(@issziv.cz)$")
             ->setDefaultValue($this->userDetails ? $this->userDetails->email : null)
             ->setRequired('Toto pole je povinné!');

        $form->addText('phone', 'Číslo telefonu (osobního)')
             ->setDefaultValue($this->userDetails ? $this->userDetails->phone : null)
             ->setRequired('Toto pole je povinné!');

        $form->addHidden("reason", $this->redirectReason);

        $form->addSubmit('submit', 'Uložit');

        $form->onSuccess[] = [$this, 'profileEditFormSuccess'];

        return $form;
    }

    public function profileEditFormSuccess(Form $form) {

        $this->userDetailsModel->setUserDetails($this->userInfo->uid, $form->values->email, $form->values->phone);

        if ($form->values->reason === "fill") {
            $this->flashMessage("Údaje byly uloženy! Nyní můžete systém používat.", "success");
            $this->redirect("Home:");
        }
        else {
            $this->flashMessage("Údaje byly uloženy!", "success");
            $this->redirect("Profile:");
        }
    }
}
