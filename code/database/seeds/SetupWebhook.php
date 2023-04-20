<?php


use Phinx\Seed\AbstractSeed;

class SetupWebhook extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $dt = new \DateTime();
        $date = $dt->format('Y-m-d H:i:s');

        $data = [
            'hash' => sha1(random_bytes(32)),
            'created_at' => $date,
            'updated_at' => $date
        ];
        $table = $this->table('webhooks');
        $table->insert($data)->save();
    }
}
