<?php
// --- building a new app container for Eloquent to use
use \Illuminate\Container\Container as Container;
use \Illuminate\Support\Facades\Facade as Facade;

/**
* Setup a new app instance container
* 
* @var Illuminate\Container\Container
*/
$appContainer = new Container();
$appContainer->singleton('app', 'Illuminate\Container\Container');

/**
* Set $app as FacadeApplication handler
*/
Facade::setFacadeApplication($appContainer);

/**
 * This does the setup for the Eloquent handling
 * outside of Laravel (using capsule)
 */
$dbconfig = [
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
];
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($dbconfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();