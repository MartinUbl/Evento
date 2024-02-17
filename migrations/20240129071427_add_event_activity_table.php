<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventActivityTable extends AbstractMigration
{

    public function change(): void
    {
        $tbl = $this->table('event_activities');

        $tbl->addColumn('name', 'string', ['limit'=> 255])
            ->addColumn('events_id', 'integer', [ 'null' => false ])
            ->addColumn('description', 'text')
            ->addColumn('place', 'string', ['limit'=> 255])
            ->addColumn('instructions', 'text')
            ->addColumn('exclusion_group', 'string', ['limit' => 16])
            ->addColumn('inclusion_group', 'string', ['limit' => 16])
            ->addColumn('capacity', 'integer', [ 'null' => false ]);

        $tbl->addForeignKey('events_id', 'events', 'id');

        $tbl->create();
    }
}
