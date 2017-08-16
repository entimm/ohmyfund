@extends('layouts.app')

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

@section('content')
    <div class="container table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <td>基金代码</td>
                    <td>名称</td>
                    <td>类型</td>
                    @foreach ($columns as $key => $column)
                    <td><a href="{{ route('rank', ['orderBy' => $key, 'sortedBy' => $column['sortedBy']]) }}">
                        {{ $column['name'] }}</a>
                    </td>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach ($funds as $fund)
                <tr>
                    <td><a href="{{ route('fund', $fund['code']) }}" target="_blank">{{ $fund['code'] }}</a></td>
                    <td>{{ $fund['name'] }}</td>
                    <td>{{ $fund['type'] }}</td>
                    @foreach ($columns as $key => $column)
                    <td class="rate-value">{{ $fund[$key] }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
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
