<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserCacheUsername extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("user_cache");

        $tbl->addColumn('username', 'string');

        $tbl->update();
    }
}
