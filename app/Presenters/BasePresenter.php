<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

class BasePresenter extends Nette\Application\UI\Presenter
{
    protected \Martinubl\Bakalapi\Client $api;

    public function __construct() {
        $this->api = new \Martinubl\Bakalapi\Client('https://server-bakalari.issziv.cz:85/', '/bakaweb');
        $this->api->restoreFromSession(true);
    }

    public function actionDefault() {
        //
    }

    public function printGroup($rec) {
        return '<span style="color:#'.$rec['color'].'">'.$rec['name'].'</span>';
    }

    public function printGroupSq($rec) {
        return '<span style="display: inline-block; width: 0.8em;height:0.8em;background-color:#'.$rec['color'].'"></span>&nbsp;<span style="color:#'.$rec['color'].'">'.$rec['name'].'</span>';
    }

    public function printGroupSqOnly($rec) {
        return '<span style="display: inline-block; width: 0.8em;height:0.8em;background-color:#'.$rec['color'].'"></span>';
    }
}
