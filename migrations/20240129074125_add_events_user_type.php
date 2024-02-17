<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventsUserType extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('events')->addColumn('restrict_user_type', 'integer', ['default' => null]);

        $tbl->update();
    }
}
