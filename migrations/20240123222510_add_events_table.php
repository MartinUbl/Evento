<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventsTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('events');

        $tbl->addColumn('name', 'string', ['limit'=> 255, 'null' => false])
            ->addColumn('description', 'text')
            ->addColumn('created', 'datetime')
            ->addColumn('datetime_from', 'datetime')
            ->addColumn('datetime_to', 'datetime')
            ->addColumn('registration_date_from', 'datetime')
            ->addColumn('registration_date_to', 'datetime')
            ->addColumn('owner_uid', 'string', [ 'null' => false ]);

        $tbl->addForeignKey('owner_uid', 'user_cache', 'uid');

        $tbl->create();
    }
}
