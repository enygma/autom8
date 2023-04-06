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
        'getProject',
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

    private function getProject($nodeId)
    {
        $query = '
            query {
                node(id: "'.$nodeId.'") {
                    ... on ProjectV2 {
                        id
                        title
                        number,
                        fields(first: 100) {
                            edges {
                                node {
                                    ... on ProjectV2Field {
                                        id
                                        name
                                        dataType
                                    }
                                    ... on ProjectV2IterationField {
                                        id
                                        name
                                        dataType
                                    }
                                    ... on ProjectV2SingleSelectField {
                                        id
                                        name
                                        dataType
                                        options {
                                            id
                                            name
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ';
        $results = $this->container->get('api_client')->runQuery($query);
        return $results->data->node;
    }

    //--------------------------------------------------------------------------------
    
    /**
     * MATCH: /Given that a project item is reordered/
     * TYPE: projects_v2_item.reordered
     */
    public function projectItemIsReordered($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: projectItemIsReordered');

        // Push the item into the context
        $this->context['item'] = $this->getItem($payload->projects_v2_item->content_node_id);

        // Push the project into the context
        $this->context['project'] = $this->getProject($payload->projects_v2_item->project_node_id);
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
        $action->handle($payload, [
            'item' => $this->context['item'],
            'comment' => $matches[1]
        ]);
        return true;
    }

    /**
     * MATCH: /Given that a comment is made on an item/
     * TYPE: issue_comment.created
     */
    public function givenCommentIsMadeOnItem($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: whenCommentIsMadeOnItem');
        
        // Push the item into the context if everything goes well
        $this->context['item'] = $this->getItem($payload->issue->node_id);
        return true;
    }

    /**
     * MATCH: /Where the body contains "(.*?)"/
     * TYPE: issue_comment.created
     */
    public function whereCommentBodyContains($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: whereCommentBodyContains');

        // If defined, see if the search string is in the body of the comment
        if (!empty($matches[1])) {
            if (str_contains($payload->comment->body, $matches[1]) == false){
                $this->container->get('logger')->info('Search string not found in comment body', ['string' => $matches[1]]);
                return false;
            } else {
                $this->container->get('logger')->info('Search string FOUND in comment body', ['string' => $matches[1]]);
            }
        }
        return true;
    }

    /**
     * MATCH: /Where the project ID is "(.*?)"/
     */
    public function whereProjectIdIs($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: whereProjectIdIs');

        // If defined, see if the project ID matches the given ID
        if (!empty($matches[1])) {
            if ($this->context['project']->number != $matches[1]){
                $this->container->get('logger')->info('Project ID does not match', ['id' => $matches[1]]);
                return false;
            } else {
                $this->container->get('logger')->info('Project ID matches', ['id' => $matches[1]]);
            }
        }
        return true;
    }

    /**
     * MATCH: /Then set the "(.*?)" field to "(.*?)"/
     */
    public function thenSetFieldTo($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: thenSetFieldTo');

        // We should always have an item from another place...
        if (empty($this->context['item'])) {
            throw new \Exception('No item found!');
        }

        $action = new \App\Action\SetFieldValue($this->container);
        $action->handle($payload, [
            'project' => $this->context['project'],
            'field' => $matches[1],
            'value' => $matches[2]
        ]);
        return true;
    }

    /**
     * MATCH: /Given that a comment is deleted on an item/
     * TYPE: issue_comment.deleted
     */
    public function givenCommentIsDeletedOnItem($payload, $matches)
    {
        $this->container->get('logger')->info('HANDLER: whenCommentIsDeleted');

        // Push the item into the context if everything goes well
        $this->context['item'] = $this->getItem($payload->issue->node_id);
        return true;
    }
}