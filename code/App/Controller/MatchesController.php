<?php

namespace App\Controller;

class MatchesController extends BaseController
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