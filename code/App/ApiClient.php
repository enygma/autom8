<?php

// Used to make requests to the GitHub GraphQL API
namespace App;

class ApiClient
{
    private $apiToken = '';

    public function __construct($apiToken)
    {
        $this->apiToken = $apiToken;
    }

    function runQuery($query)
    {
        $client = new \GuzzleHttp\Client();

        try {
            $res = $client->request('POST', 'https://api.github.com/graphql', [
                'headers' => ["Authorization" => "bearer ".$this->apiToken],
                'json' => ['query' => $query]
            ]);
            if ($res->getStatusCode() !== 200) {
                throw new \Exception('Error on query!');
            }
            if (isset($res->errors)) {
                $errorMesasges = [];
                foreach($res->errors as $error) {
                    $errorMesasges[] = $error->message;
                }
                throw new \Exception('Error(s) on query: '.implode(', ', $errorMesasges));
            }
            $body = (string)$res->getBody();
            // echo $body;
            
            return json_decode($body);
        } catch (\Exception $e) {
            echo 'ERROR: '.$e->getMessage()."\n";
        }   
    }
}