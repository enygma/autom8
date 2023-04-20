<?php

namespace App\Model;

class EventMatch extends Base
{
    protected $table = 'events_matches';

    protected $fillable = [
        'event_id', 'match_id'
    ];
}