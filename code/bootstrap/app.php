<?php
use League\Event\EventDispatcher;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

// Set up the autoloader for the App
spl_autoload_register(function($class) {
    $path = BASE_DIR.str_replace('\\', '/', $class).'.php';
    if (!is_file($path)) {
        // Log if the class isn't found
        error_log('Class "'.$class.'" invalid');
    } else {
        require_once $path;
    }
});

// Add Twig-View Middleware
$twig = Twig::create(BASE_DIR.'/templates');
$app->add(TwigMiddleware::create($app, $twig));

$container->set('session', function($container) {
    $session_factory = new \Aura\Session\SessionFactory;
    $session = $session_factory->newInstance($_COOKIE);
    return $session->getSegment('default');
});

$container->set('dispatch', function($container) {
    $dispatcher = new EventDispatcher();

    // Add the event listeners
    $dispatcher->addListener('ping', new \App\Event\PingEvent());

    return $dispatcher;
});

$container->set('api_client', function($container) {
    $client = new \App\ApiClient($_ENV['GITHUB_API_TOKEN']);
    return $client;
});

$container->set('logger', function($container) {
    $logger = new Logger('default');
    $logger->pushHandler(new StreamHandler($_ENV['LOG_PATH'], Level::Info));
    return $logger;
});

// Define Custom Error Handler
$customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null
) use ($app, $container) {
    $container->get('logger')->error($exception->getMessage());

    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);