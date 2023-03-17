<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Slim\Routing\RouteContext;

class BaseController
{
    protected $container;

    public function __construct(\DI\Container $container)
    {
        $this->container = $container; 
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args = [])
    {
        $method = strtolower($request->getMethod());
        $args = $this->getArgsFromRequest($request);

        return $this->$method($request, $response, $args);
    }

    public function render($template, array $data = [])
    {
        $response = $this->container->get('Slim\Psr7\Response');
        return $this->container->get('view')->render($response, $template, $data);
    }

    public function __get($property)
    {
        if (isset($this->container, $property)) {
            return $this->container->get($property);
        }
        return null;
    }

    public function getArgsFromRequest($request)
    {
        $routeContext = RouteContext::fromRequest($request);
        return $routeContext->getRoute()->getArguments();
    }

    public function jsonError($message, array $addl = [], $status = 500)
    {
        return $this->jsonReturn(false, $message, $addl, $status);
    }

    public function jsonSuccess($message, array $addl = [], $status = 200)
    {
        return $this->jsonReturn(true, $message, $addl, $status);
    }
    
    public function jsonReturn($state, $message, array $addl = [], $status = 200)
    {
        $data = [
            'message' => $message,
            'status' => $state
        ];
        if (!empty($addl)) {
            $data = array_merge($data, $addl);
        }

        $response = $this->container->get('Slim\Psr7\Response');
        $response->getBody()->write(json_encode($data));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
    }
}