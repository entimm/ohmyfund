@extends('layouts.app')

@push('csses')
<style>
.rise {
    color: red;
    font-weight: bolder;
}

.fall {
    color: green;
    font-weight: bolder;
}

.box.box-default {
    border-top-color: #d2d6de;
}
.box {
    position: relative;
    border-radius: 3px;
    background: #ffffff;
    border-top: 3px solid #d2d6de;
    margin-bottom: 20px;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
.box-header {
    color: #444;
    display: block;
    padding: 10px;
    position: relative;
}
.box-body {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-right-radius: 3px;
    border-bottom-left-radius: 3px;
    padding: 10px;
}
.box-footer {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-right-radius: 3px;
    border-bottom-left-radius: 3px;
    border-top: 1px solid #f4f4f4;
    padding: 10px;
    background-color: #fff;
}
.box-header .box-title {
    display: inline-block;
    font-size: 18px;
    margin: 0;
    line-height: 1;
}
.row .col {
    padding-left: 5px;
    padding-right: 5px;
}
</style>
@endpush

@section('content')
    <div class="container table-responsive">
        <div class="row">
            @foreach ($collection as $funds)
            <div class="col-md-4 col">
            <div class="box">
                <div class="box-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <td>名称</td>
                            @foreach ($columns as $key => $column)
                                <td>
                                    @if (isset($column['sortedBy']))
                                    <a href="{{ route('simple', ['orderBy' => $key, 'sortedBy' => $column['sortedBy']]) }}">
                                        {{ $column['name'] }}
                                    </a>
                                    @else
                                        {{ $column['name'] }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($funds as $fund)
                            <tr id="fund-{{ $fund->code }}">
                                <td>
                                    <a href="{{ route('fund', $fund->code) }}" target="_blank">
                                    {{ $fund->name }}
                                    </a>
                                </td>
                                @foreach ($columns as $key => $column)
                                    <td class="rate-value {{$key}}">{{ $fund->$key ?: '—' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
    var params = {
        orderBy: "{{ $orderBy }}",
        sortedBy: "{{ $sortedBy }}",
    };

    function reload(field, value)
    {
        if (field == "orderBy") {
            if (value == params[field]) {
                params.sortedBy = params.sortedBy == 'asc' ? 'desc' : 'asc';
            } else {
                params.sortedBy = 'desc';
            }
        }
        params[field] = value;
        var paramStr = jQuery.param(params);
        var url = "{{ request()->url() }}";
        url += "?" + paramStr;
        window.location.href = url;
    }

    makeColor();

    getEvaluate();
    setInterval(getEvaluate, 120000);

    function getEvaluate()
    {
        $.get('/api/fund/evaluate', function(list) {
            for(item of list) {
                $tr = $('#fund-'+item.code);
                $tr.find('.evaluateRate').html(item.rate);
                $tr.find('.evaluateTime').html(item.time);
            }
            makeColor();
        });
    }

    function makeColor()
    {
        $('.rate-value').each(function () {
            $this = $(this);
            let text = $this.text();
            if (isFinite(text) && (num = parseFloat(text))) {
                if (num > 0) {
                    $this.addClass('rise').removeClass('fall');
                } else if (num < 0) {
                    $this.addClass('fall').removeClass('rise');
                }
            }
        });
    }
    </script>
@endpush
