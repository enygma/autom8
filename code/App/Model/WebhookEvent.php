<?php

namespace App\Model;

class WebhookEvent extends Base
{
    protected $table = 'webhook_events';

    protected $fillable = [
        'webhook_id', 'event_id'
    ];
}