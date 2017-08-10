@extends('layouts.app')

@section('content')
    <div class="container">
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
                    <td><a href="{{ route('fund', $fund['code']) }}">{{ $fund['code'] }}</a></td>
                    <td>{{ $fund['name'] }}</td>
                    <td>{{ $fund['type'] }}</td>
                    @foreach ($columns as $key => $column)
                    <td>{{ $fund[$key] }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
