<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserCache extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("user_cache", [ "id"=> false, "primary_key"=> ["uid"]]);

        $tbl->addColumn("uid", "string", [ "null" => false]);

        $tbl->addIndex('uid');
        $tbl->create();
    }
}
