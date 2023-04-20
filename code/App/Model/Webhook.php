<?php

namespace App\Model;

class Webhook extends Base
{
    protected $fillable = [
        'hash'
    ];

    public function events()
    {
        return $this->hasManyThrough(
            '\App\Model\Event',
            '\App\Model\WebhookEvent',
            'webhook_id', // webhook_events.webhook_id
            'id', // events.id
            'id', // webhooks.id
            'event_id' // webhook_events.event_id
        );
    }
}