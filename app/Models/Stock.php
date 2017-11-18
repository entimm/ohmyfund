<?php

namespace App\Models;

use App\Presenters\StockPresenter;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;

/**
 * App\Models\Stock.
 *
 * @property int $id
 * @property string $symbol
 * @property string $code
 * @property string $name
 * @property array $data
 * @property \Carbon\Carbon|null $counted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereCountedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Stock whereUpdatedAt($value)
 * @mixin \Eloquent
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

    public function getPresenterClass()
    {
        return StockPresenter::class;
    }

    public function getRouteKeyName()
    {
        return 'symbol';
    }
}
