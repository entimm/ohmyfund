<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\Stock
 *
 * @property int $id
 * @property string $symbol
 * @property string $code
 * @property string $name
 * @property array $data
 * @property \Carbon\Carbon|null $counted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereCountedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Stock whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Stock extends Model implements Transformable
{
    use TransformableTrait;

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
