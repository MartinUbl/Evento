<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddActivityGroupsTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('activity_group');

        $tbl->addColumn('name', 'string')
            ->addColumn('color', 'string');

        $tbl->create();

        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Červená', 'FF0000')");
        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Zelená', '00FF00')");
        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Modrá', '0000FF')");
        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Žlutá', 'FFFF00')");
        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Fialová', 'FF00FF')");
        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Tyrkysová', '40E0D0')");
        $this->execute("INSERT INTO activity_group (name, color) VALUES ('Oranžová', 'FF8000')");
    }
}
