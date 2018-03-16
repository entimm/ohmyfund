<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function create()
    {
        return view('monitor.create');
    }

    public function store(Request $request)
    {
        $date = $request->input('date');
        $data = $request->input('data');

        $data = explode(PHP_EOL, $data);
        foreach ($data as $key => &$value) {
            $value = explode('|', $value);
            $code = $value[0];
            $stars = isset($value[1]) ? $value[1] : 0;
            $remark = isset($value[2]) ? $value[2] : '';
            $value = compact('code', 'stars', 'remark');
            unset($value);
        }
    }

    public function show()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function destroy()
    {

    }

}
