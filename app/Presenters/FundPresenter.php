<?php

namespace App\Presenters;

use App\Models\Fund;
use McCool\LaravelAutoPresenter\BasePresenter;

class FundPresenter extends BasePresenter
{
    public function type()
    {
        return Fund::$types[$this->wrappedObject->type];
    }

    public function unit()
    {
        return round($this->wrappedObject->unit / 10000, 2);
    }

    public function total()
    {
        return round($this->wrappedObject->total / 10000, 2);
    }

    public function rate()
    {
        return round($this->wrappedObject->rate / 10000, 2);
    }

    public function in_1week()
    {
        return round($this->wrappedObject->in_1week / 10000, 2);
    }

    public function in_1month()
    {
        return round($this->wrappedObject->in_1month / 10000, 2);
    }

    public function in_3month()
    {
        return round($this->wrappedObject->in_3month / 10000, 2);
    }

    public function in_6month()
    {
        return round($this->wrappedObject->in_6month / 10000, 2);
    }

    public function current_year()
    {
        return round($this->wrappedObject->current_year / 10000, 2);
    }

    public function in_1year()
    {
        return round($this->wrappedObject->in_1year / 10000, 2);
    }

    public function in_2year()
    {
        return round($this->wrappedObject->in_2year / 10000, 2);
    }

    public function in_3year()
    {
        return round($this->wrappedObject->in_3year / 10000, 2);
    }

    public function in_5year()
    {
        return round($this->wrappedObject->in_5year / 10000, 2);
    }

    public function since_born()
    {
        return round($this->wrappedObject->since_born / 10000, 2);
    }
}
