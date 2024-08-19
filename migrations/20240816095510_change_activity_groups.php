<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeActivityGroups extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("DELETE FROM activity_group WHERE color IN ('FFFF00', '40E0D0')");
    }
}
