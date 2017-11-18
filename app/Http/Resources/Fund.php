<?php

namespace App\Http\Resources;

use App\Models\Fund as FundModel;
use Illuminate\Http\Resources\Json\Resource;

class Fund extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'code'         => $this->code,
            'name'         => $this->name,
            'type'         => FundModel::$types[$this->type],
            'unit'         => round($this->unit / 10000, 2),
            'total'        => round($this->total / 10000, 2),
            'rate'         => round($this->rate / 10000, 2),
            'in_1week'     => round($this->in_1week / 10000, 2),
            'in_1month'    => round($this->in_1month / 10000, 2),
            'in_3month'    => round($this->in_3month / 10000, 2),
            'in_6month'    => round($this->in_6month / 10000, 2),
            'current_year' => round($this->current_year / 10000, 2),
            'in_1year'     => round($this->in_1year / 10000, 2),
            'in_2year'     => round($this->in_2year / 10000, 2),
            'in_3year'     => round($this->in_3year / 10000, 2),
            'in_5year'     => round($this->in_5year / 10000, 2),
            'since_born'   => round($this->since_born / 10000, 2),
            'born_date'    => $this->born_date,
        ];
    }
}
