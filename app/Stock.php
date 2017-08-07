<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Stock
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $symbol
 * @property string $code
 * @property string $name
 * @property array $data
 * @property \Carbon\Carbon|null $counted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereCountedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Stock whereUpdatedAt($value)
 */
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
