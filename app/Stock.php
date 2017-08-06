<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['symbol', 'code', 'name'];

    protected $casts = [
        'data' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'counted_at',
    ];

    public function getRouteKeyName()
    {
        return 'symbol';
    }
}
