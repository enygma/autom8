<?php

namespace App\Action;

class MakeComment
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function handle($payload, $config)
    {
        $this->container->get('logger')->info('[ACTION] MakeComment::handle');

        if (empty($config['comment'])) {
            throw new \Exception('No comment provided');
        }
        
        $query = '
            mutation {
                addComment(
                    input: {
                        clientMutationId: "1"
                        subjectId: "'.$config['item']->id.'"
                        body: "'.$config['comment'].'"
                    }
                ) {
                    clientMutationId
                    subject {
                        id
                    }
                }
            }
        ';
        $result = $this->container->get('api_client')->runQuery($query);
        return $result;
    }
}