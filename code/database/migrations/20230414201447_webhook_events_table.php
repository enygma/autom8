<?php

use Phinx\Migration\AbstractMigration;

class WebhookEventsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('webhook_events');
        $table->addColumn('event_id', 'string')
            ->addColumn('webhook_id', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->save();
    }
}
