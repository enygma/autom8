<?php

namespace App;

class Context
{
    public $context = [];
    protected $container;
    protected $exclude = [
        '__construct',
        'build',
        'getItem',
        'buildMatchList'
    ];

    public function __construct($container)
    {
        $this->container = $container;
    }
    public function build($payload, $content)
    {
        $rc = new \ReflectionClass('App\Context');
        $matchList = $this->buildMatchList($rc);
        $typeMatch = $this->container->get('event_type').'.'.$this->container->get('action');

        // Go through each line and see if there's a a match...
        foreach (explode("\n", $content) as $line) {
            $this->container->get('logger')->info('Matching line', ['line' => $line]);

            foreach ($matchList as $method => $match) {
                $find = preg_match($match['string'], $line, $matches);
                $this->container->get('logger')->info('Method type', ['type' => $match['type']]);
                
                if ($find != false) {
                    // If we have a "type" make sure we match it, if not move along
                    if ($match['type'] !== null && $match['type'] !== $typeMatch) {
                        $this->container->get('logger')->info('No match on method by type', ['method' => $method]);
                        continue;
                    }
                    $this->container->get('logger')->info('Matched method', ['method' => $method]);

                    // Call the matched method and check the result
                    $result = $this->$method($payload, $matches);
                    if ($result == false) { 
                        $this->container->get('logger')->info('Criteria not passed, failing event');
                        return false; 
                    }
                    continue 2;
                } else {
                    $this->container->get('logger')->info('No match on method', ['method' => $method]);
                }
            }
            $this->container->get('logger')->info('No match found for line', ['line' => $line]);
            return false;
        }
        return true;
    }

    private function buildMatchList($reflectedClass)
    {
        // Build the list of methods and matches
        $matchList = [];
        foreach ($reflectedClass->getMethods() as $method) {
            $comment = $method->getDocComment();
            
            // Find the MATCH: and TYPE: lines
            $match = preg_match_all('/(MATCH|TYPE): (.*)/', $comment, $matches);
            if ($match !== false && !empty($matches)) {
                if (!in_array($method->name, $this->exclude)) {
                    $matchList[$method->name] = [
                        'string' => $matches[2][0],
                        'type' => (isset($matches[2][1])) ? $matches[2][1] : null
                    ];
                }
            }
        }
        return $matchList;
    }

    private function getItem($nodeId)
    {
        $query = '
            query {
                node(id: "'.$nodeId.'") {
                    ... on Issue {
                        id
                        title
                        body
                        number
                    }
                }
            }
        ';
        $results = $this->container->get('api_client')->runQuery($query);
        return $results->data->node;
    }

    //--------------------------------------------------------------------------------
    
    /**
     * MATCH: /When a project item is reordered/
     * TYPE: projects_v2_item.reordered
     */
    public function projectItemIsReordered($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: projectItemIsReordered');

        // Push the item into the context
        $this->context['item'] = $this->getItem($payload->projects_v2_item->content_node_id);
        return true;
    }

    /**
     * MATCH: /Then comment on the item "(.*?)"/
     */
    public function makeACommentOnItem($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: makeACommentOnItem');

        // We should always have an item from another place...
        if (empty($this->context['item'])) {
            throw new \Exception('No item found!');
        }

        $action = new \App\Action\MakeComment($this->container);
        $action->handle($this->context['item'], [
            'comment' => $matches[1]
        ]);
        return true;
    }

    /**
     * MATCH: /When a comment is made on an item( containing "(.+?)")?/
     * TYPE: issue_comment.created
     */
    public function whenCommentIsMadeOnItem($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: whenCommentIsMadeOnItem');

        // If defined, see if the search string is in the body of the comment
        if (!empty($matches[2])) {
            if (str_contains($payload->comment->body, $matches[2]) == false){
                $this->container->get('logger')->info('Search string not found in comment body', ['string' => $matches[2]]);
                return false;
            }
        }
        
        // Push the item into the context if everything goes well
        $this->context['item'] = $this->getItem($payload->issue->node_id);
        return true;
    }

    /**
     * MATCH: /When a comment is deleted on an item/
     * TYPE: issue_comment.deleted
     */
    public function whenCommentIsDeletedOnItem($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: whenCommentIsDeleted');

        // Push the item into the context if everything goes well
        $this->context['item'] = $this->getItem($payload->issue->node_id);
        return true;
    }
}