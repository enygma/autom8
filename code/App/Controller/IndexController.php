<?php

namespace App\Controller;

use \App\Model\PatternMatch;
use \App\Model\Webhook;
use \App\Model\Event;

class IndexController extends BaseController
{
    public function index($request, $response)
    {
        $data = [
            'matches' => PatternMatch::all(),
            'webhooks' => Webhook::all(),
            'events' => Event::all()
        ];
        return $this->render($request, '/index/index.php', $data);
    }
}