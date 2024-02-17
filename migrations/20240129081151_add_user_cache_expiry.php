<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserCacheExpiry extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("user_cache");

        $tbl->addColumn('expires_at', 'integer');

        $tbl->update();
    }
}
