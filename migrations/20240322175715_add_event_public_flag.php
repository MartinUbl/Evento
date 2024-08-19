<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventPublicFlag extends AbstractMigration
{
    public function change(): void
    {
        $this->table('events')->addColumn('public', 'integer', ['default' => 0])->update();
    }
}
