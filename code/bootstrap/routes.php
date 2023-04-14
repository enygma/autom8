<?php

// Handle the website requests
$app->get('/', '\App\Controller\IndexController:index');

// Handle the API requests
$app->get('/matches', '\App\Controller\MatchesController:index');
$app->post('/webhook/{id}', '\App\Controller\WebhookController:index');