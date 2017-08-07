<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\Company
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Company whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Company whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Company extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = ['code', 'name'];
}
