<?php

use Phinx\Migration\AbstractMigration;

class EventsMatchesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('events_matches');
        $table->addColumn('event_id', 'string')
            ->addColumn('match_id', 'string')
            ->addColumn('match_order', 'integer')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->save();
    }
}
