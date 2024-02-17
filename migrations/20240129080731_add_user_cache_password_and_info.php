<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserCachePasswordAndInfo extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("user_cache");

        $tbl->addColumn('password_hash', 'string', ['limit'=> 255])
        ->addColumn('token','text')
        ->addColumn('token_expiry','integer')
        ->addColumn('user_info', 'text');

        $tbl->update();
    }
}
