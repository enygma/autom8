<?php

use Phinx\Migration\AbstractMigration;

class MatchesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('pattern_matches');
        $table->addColumn('pattern', 'string')
            ->addColumn('description', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->save();
    }
}
