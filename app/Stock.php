<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['symbol', 'code', 'name'];

    protected $casts = [
        'data' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'symbol';
    }
}
