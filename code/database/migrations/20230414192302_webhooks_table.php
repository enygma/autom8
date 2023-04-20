<?php

use Phinx\Migration\AbstractMigration;

class WebhooksTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('webhooks');
        $table->addColumn('hash', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->save();
    }
}
