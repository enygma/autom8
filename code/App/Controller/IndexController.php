<?php

namespace App\Controller;

class IndexController extends BaseController
{
    public function index($request, $response)
    {
        $data = [];
        return $this->render($request, '/index/index.php', $data);
    }
}