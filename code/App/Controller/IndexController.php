<?php

namespace App\Controller;

class IndexController extends BaseController
{
    public function index($request, $response)
    {
        $body = $request->getParsedBody();
        $headers = $request->getHeaders();
        // error_log(print_r($headers, true), 3, '/tmp/php.log');

        $payload = json_decode($body['payload']);
        $eventType = $headers['X-Github-Event'][0];
        
        $this->container->set('event_type', $eventType);
        $this->container->set('action', $payload->action);
        
        $this->logger->info('Received action', ['type' => $eventType.'.'.$payload->action]);
        
        // Parse all of the tests into "contexts" and run the build
        $testPath = realpath($_ENV['TEST_PATH']);
        if ($testPath == false) {
            throw new \Exception('Test path not found: '.$_ENV['TEST_PATH']);
        }

        $testFiles = glob($testPath.'/*.event');
        $successCount = 0;
        
        foreach ($testFiles as $file) {
            $this->logger->info("Running event file", ['file' => $file]);

            $contents = file_get_contents($file);
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