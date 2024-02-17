<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserCacheUsernameIndex extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("user_cache");

        $tbl->addIndex('username');

        $tbl->update();
    }
}
