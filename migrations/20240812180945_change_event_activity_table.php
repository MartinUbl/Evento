<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeEventActivityTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('event_activities')
             ->removeColumn('exclusion_group')
             ->removeColumn('inclusion_group')
             ->addColumn('group', 'integer', array('null' => false, 'default' => 0))
             ->update();
    }
}
