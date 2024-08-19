<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserDetailsTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('user_details', [ 'id' => false, 'primary_key' => ['uid']]);

        $tbl->addColumn("uid", "string", [ "null" => false])
            ->addColumn("email", "string", [ 'null' => true, 'limit' => 128 ])
            ->addColumn("phone", "string", [ 'null' => true, 'limit' => 128 ]);

        $tbl->addIndex('uid');
        $tbl->create();
    }
}
