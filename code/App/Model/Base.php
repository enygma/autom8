<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public static $rules = [];

    public static function getRules()
    {
        return static::$rules;
    }
}