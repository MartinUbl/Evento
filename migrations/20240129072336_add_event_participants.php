<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventParticipants extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('event_participants', [ 'id' => false, 'primary_key' => [ 'user_cache_uid', 'event_activities_id' ]]);

        $tbl->addColumn('user_cache_uid', 'string', ['null' => false])
            ->addColumn('event_activities_id', 'integer', ['null' => false])
            ->addColumn('created_at', 'datetime');

        $tbl->addForeignKey('user_cache_uid', 'user_cache', 'uid')
            ->addForeignKey('event_activities_id', 'event_activities', 'id');

        $tbl->create();
    }
}
