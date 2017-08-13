<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;

class HistoryPresenter extends BasePresenter
{
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

    public function bonus()
    {
        return round($this->wrappedObject->bonus / 10000, 2);
    }
}
