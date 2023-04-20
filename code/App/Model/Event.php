<?php

namespace App\Model;

class Event extends Base
{
    protected $fillable = [
        'name', 'description'
    ];

    public function matches()
    {
        return $this->hasManyThrough(
            '\App\Model\PatternMatch',
            '\App\Model\EventMatch',
            'event_id', // event_matches.event_id
            'id', // pattern_matches.id
            'id', // events.id
            'match_id' // event_matches.match_id
        );
    }
}