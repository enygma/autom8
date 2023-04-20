<?php

use Phinx\Migration\AbstractMigration;

class EventsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('events');
        $table->addColumn('name', 'string')
            ->addColumn('description', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->save();
    }
}
