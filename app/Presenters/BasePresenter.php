<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

class BasePresenter extends Nette\Application\UI\Presenter
{
    protected \Martinubl\Bakalapi\Client $api;

    public function __construct() {
        $this->api = new \Martinubl\Bakalapi\Client('https://server-bakalari.issziv.cz:85/', '/bakaweb');
        $this->api->restoreFromSession();
    }

    public function actionDefault() {
        //
    }

    public function handleLogout() {
        $this->api->logout();
        $this->redirect('Login:');
    }
}
