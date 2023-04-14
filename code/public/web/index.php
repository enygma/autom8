<?php

// use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

require_once __DIR__.'/../../vendor/autoload.php';

define('BASE_DIR', __DIR__.'/../../');
define('APP_DIR', __DIR__.'/../../App');
define('CONFIG_DIR', __DIR__.'/../../bootstrap');
define('ENV', 'web');

$dotenv = Dotenv\Dotenv::createImmutable(CONFIG_DIR);
$dotenv->load();

// Initialize the app
$container = new Container();
$app = \DI\Bridge\Slim\Bridge::create($container);

require_once CONFIG_DIR.'/app.php';
foreach (new DirectoryIterator(CONFIG_DIR) as $file) {
    if ($file->isDot() || substr($file->getPathname(), -3) !== 'php') { continue; }
    require_once $file->getPathname();
}

$app->run();