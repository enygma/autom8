<?php

namespace App\Controller\Web;

class MatchesController extends \App\Controller\BaseController
{
    public function index($request, $response)
    {
        $context = new \App\Context($this->container);
        $matches = $context->getAllMatches();

        return $this->jsonSuccess('index', [
            'matches' => $matches
        ]);
    }
}