<?php

// Handle the website requests
$app->get('/', '\App\Controller\Web\IndexController:index');

// Handle the API requests
$app->get('/matches', '\App\Controller\Web\MatchesController:index');
$app->post('/webhook/{id}', '\App\Controller\Web\WebhookController:index');