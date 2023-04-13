<?php

$app->post('/', '\App\Controller\IndexController:index');

$app->get('/matches', '\App\Controller\MatchesController:index');