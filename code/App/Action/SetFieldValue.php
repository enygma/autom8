<?php

namespace App\Action;

class SetFieldValue
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function handle($payload, $config)
    {
        $this->container->get('logger')->info('[ACTION] SetFieldValue::handle');

        if (empty($config['field'])) {
            throw new \Exception('No field provided');
        }
        if (empty($config['value'])) {
            throw new \Exception('No value provided');
        }
        if (empty($config['project'])) {
            throw new \Exception('No project provided');
        }

        // First we need to get the project-level ID of the field we want to update
        $field = null;
        foreach ($config['project']->fields->edges as $field) {
            if ($field->node->name == $config['field']) {
                $field = $field->node;
                break;
            }
        }
        if ($field == null) {
            throw new \Exception('Field not found: '.$config['field']);
        }

        switch($field->dataType) {
            case 'SINGLE_SELECT':
                // If it's a select, find the ID for the right option
                $value = null;
                foreach ($field->options as $option) {
                    if ($option->name == $config['value']) {
                        $value = '{singleSelectOptionId:"'.$option->id.'"}';
                        break;
                    }
                }
                if ($value == null) {
                    throw new \Exception('Option not found: '.$config['value']);
                }
                break;
            default:
                $value = '{text:"'.$config['value'].'"}';
        }
        
        // We need to use the item ID from the payload as that relates to the item's node ID in the project
        $query = '
            mutation {
                updateProjectV2ItemFieldValue(
                    input: {
                        clientMutationId: "1"
                        fieldId: "'.$field->id.'"
                        itemId: "'.$payload->projects_v2_item->node_id.'"
                        value: '.$value.'
                        projectId: "'.$config['project']->id.'"
                    }
                )   
                { clientMutationId }
            }
        ';
        $result = $this->container->get('api_client')->runQuery($query);

        $status = ($result->data->updateProjectV2ItemFieldValue->clientMutationId == '1' ? 'SUCCESS' : 'FAIL');
        $this->container->get('logger')->info('[ACTION] SetFieldValue::handle - done ('.$status.')');
        return $result;
    }
}