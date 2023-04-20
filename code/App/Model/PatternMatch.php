<?php

namespace App\Model;

class PatternMatch extends Base
{
    protected $table = 'pattern_matches';

    protected $fillable = [
        'pattern', 'description'
    ];
}