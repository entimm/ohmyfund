<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class History extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'date'  => $this->date,
            'unit'  => round($this->unit / 10000, 4),
            'total' => round($this->total / 10000, 4),
            'rate'  => round($this->rate / 10000, 4),
        ];
        if ($this->bonus) {
            $data['bonus'] = $this->bonus;
        }
        return $data;
    }
}
