<?php

namespace App\Controller;

use Slim\Routing\RouteContext;

class WebhookController extends BaseController
{
    public function index($request, $response)
    {
        $this->container->get('logger')->info('WebhookController::index - Received webhook');

        $body = $request->getParsedBody();
        $headers = $request->getHeaders();
        // error_log(print_r($headers, true), 3, '/tmp/php.log');

        $id = $this->getArgsFromRequest($request)['id'];
        var_export($id);

        // Find the matching webhook
        $webhook = \App\Model\Webhook::where('hash', $id)->first();
        if ($webhook == null) {
            throw new \Exception('ERROR: Webhook not found');
        }
        // var_export($webhook);
        var_export($webhook->events);

        if (empty($body)) {
            throw new \Exception('ERROR: No request body found');
        }

        $payload = json_decode($body['payload']);
        $eventType = $headers['X-Github-Event'][0];
        
        $this->container->set('event_type', $eventType);
        $this->container->set('action', $payload->action);
        
        $this->logger->info('Received action', ['type' => $eventType.'.'.$payload->action]);

        // Build the events from the webhook->events property
        $successCount = 0;
        foreach ($webhook->events as $event) {
            $this->logger->info('Running event', ['name' => $event->name]);
            $contents = '';
            foreach ($event->matches as $match) {
                $contents .= $match->pattern."\n";
            }
            
            // Run the event
            $context = new \App\Context($this->container);
            $result = $context->build($payload, $contents);
            
            if ($result == true) {
                $successCount++;
            }
        }
        
        return $this->jsonSuccess('index', [
            'message' => $successCount.' events ran successfully'
        ]);
    }
}