<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventGroupRulesTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('event_group_rules');

        /* rule values:
         * exclude = if I am enrolled in the activity in this group, I cannot enroll into activity of the other group
         * enforce = if I am enrolled in the activity in this group, I must enroll into activity of the other group
         * ???
         */

        $tbl->addColumn('events_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('activity_group_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('other_activity_group_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('rule', 'string', ['null' => false]);

        $tbl->addForeignKey('events_id', 'events', 'id')
            ->addForeignKey('activity_group_id', 'activity_group', 'id')
            ->addForeignKey('other_activity_group_id', 'activity_group', 'id');

        $tbl->addIndex('events_id');

        $tbl->create();
    }
}
