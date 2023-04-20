<?php


use Phinx\Seed\AbstractSeed;

class SetupMatches extends AbstractSeed
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

        $matches = [
            [
                'pattern' => '/Then comment on the item "(.*?)"/',
                'description' => 'Make a comment on the given item'
            ],
            [
                'pattern' => '/Given that a comment is made on an item/',
                'description' => 'A comment is made on an item'
            ],
            [
                'pattern' => '/Where the body contains "(.*?)"/',
                'description' => 'The comment body contains the given string'
            ]
        ];
        $dt = new \DateTime();
        $date = $dt->format('Y-m-d H:i:s');

        foreach ($matches as $match) {
            $data = [
                'pattern' => $match['pattern'],
                'description' => $match['description'],
                'created_at' => $date,
                'updated_at' => $date
            ];
            $table = $this->table('pattern_matches');
            $table->insert($data)->save();
        }
    }
}
