<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette, Nette\Application\UI\Form;
use Exception;

final class LoginPresenter extends BasePresenter
{
    /** @var \App\Model\UserCacheModel */
    private $userCacheModel = null;

    public function __construct(\App\Model\UserCacheModel $userCacheModel) {

        parent::__construct();

        $this->userCacheModel = $userCacheModel;
    }

    public function actionDefault() {
        //
    }

    public function createComponentLoginForm() {

        $form = new Form;

        $form->addText("username","Přihlašovací jméno")
             ->setRequired("Vyplňte přihlašovací jméno!");
        $form->addPassword("password","Heslo")
             ->setRequired("Vyplňte heslo!");

        $sub = $form->addSubmit("submit","Přihlásit se");

        $sub->getControlPrototype()->value = '<i class="fa-solid fa-person-running"></i>';

        $form->onSuccess[] = [$this,"loginFormSuccess"];

        return $form;
    }

    public function loginFormSuccess(Form $form) {

        $vals = $form->values;

        $liveResult = false;
        $postponeTokenExpiry = false;
        $tryAuthResult = $this->userCacheModel->tryAuth($vals->username, $vals->password);
        if ($tryAuthResult && !isset($tryAuthResult['error']) && isset($tryAuthResult['token']) && isset($tryAuthResult['user_info']) &&
            !empty($tryAuthResult['token']) && !empty($tryAuthResult['user_info'])) {
            $result = \Martinubl\Bakalapi\Baka_LoginError::OK;
        }
        else {
            try {
                throw new Exception("Testing");
                $result = $this->api->login($vals->username, $vals->password);
                $liveResult = true;
            }
            catch (Exception $e) {

                $tryAuthResult = $this->userCacheModel->tryAuth($vals->username, $vals->password, true);
                if ($tryAuthResult && !isset($tryAuthResult['error'])) {
                    $result = \Martinubl\Bakalapi\Baka_LoginError::OK;
                    $postponeTokenExpiry = true;
                }
                else {
                    $result = \Martinubl\Bakalapi\Baka_LoginError::INVALID;
                }
            }
        }

        if ($result == \Martinubl\Bakalapi\Baka_LoginError::OK) {

            if (!$liveResult) {

                $authTokenExpiry = $tryAuthResult['token_expiry'];
                // postpone token expiry when the auth backend does not respond
                if ($postponeTokenExpiry) {
                    $authTokenExpiry = time() + 3600;
                }

                $this->api->useCached($tryAuthResult['token'], $authTokenExpiry, $tryAuthResult['user_info']);
            }

            $this->api->saveToSession();

            if ($liveResult) {
                $uinfo = $this->api->getUserInfo();
                if ($uinfo && !empty($uinfo->uid)) {
                    $this->userCacheModel->refreshUserCacheRecord($uinfo->uid, $vals->username, $vals->password, $this->api->getToken(), $this->api->getTokenExpiry(), $uinfo->stringify());
                }
            }

            $this->redirect("Home:");
        }
        else if ($result == \Martinubl\Bakalapi\Baka_LoginError::INVALID) {
            $form->addError("Neplatné přihlašovací údaje!");
        }
        else {
            $form->addError("Nepodařilo se připojit na server Bakaláři!");
        }
    }
}
