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
</style>
@endpush

@section('content')
    <div class="container table-responsive">
        <table class="table">
            <thead>
            <tr>
                <td>基金代码</td>
                <td>名称</td>
                <td>类型</td>
                @foreach ($columns as $key => $column)
                    <td><a href="{{ route('evaluate', ['orderBy' => $key, 'sortedBy' => $column['sortedBy']]) }}">
                            {{ $column['name'] }}</a>
                    </td>
                @endforeach
                @for ($i = 0; $i < 7; $i++)
                    <td>{{$i}}th</td>
                @endfor
            </tr>
            </thead>
            <tbody>
            @foreach ($funds as $fund)
                <tr>
                    <td><a href="{{ route('fund', $fund->code) }}" target="_blank">{{ $fund->code }}</a></td>
                    <td>{{ $fund->name }}</td>
                    <td>{{ $fund->type }}</td>
                    @foreach ($columns as $key => $column)
                        <td class="rate-value">{{ $fund->$key ?: '—'}}</td>
                    @endforeach
                    @for ($i = 0; $i < 7; $i++)
                        <td class="rate-value">
                            {{ $fund->histories->take(-7)->reverse()->values()[$i]->rate }}</td>
                    @endfor
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $funds->links() }}
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

    $(function() {
        $('.rate-value').each(function () {
            $this = $(this);
            let text = $this.text();
            if (isFinite(text) && (num = parseFloat(text))) {
                if (num > 0) {
                    $this.addClass('rise');
                } else if (num < 0) {
                    $this.addClass('fall');
                }
            }
        })
    });
    </script>
@endpush
