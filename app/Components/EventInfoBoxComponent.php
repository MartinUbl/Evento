<?php

declare(strict_types=1);

namespace App\Components;

class EventInfoBoxComponent extends \Nette\Application\UI\Control {
    public function render($event, $allowJoin = false) {
        $this->template->event = $event;
        $this->template->allowJoin = $allowJoin;
	    $this->template->render(__DIR__ . '/EventInfoBoxComponent.latte');
    }
}
