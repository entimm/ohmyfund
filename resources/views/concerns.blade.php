@extends('layouts.app')

@section('content')
    <div class="container">
        <div>

        </div>

        @foreach($funds as $fund)
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="pull-right">
                        <a href="javascript:;" onclick="reload('graphScope', '7')"><span class="label label-default">7天图</span></a>
                        <a href="javascript:;" onclick="reload('graphScope', '30')"><span class="label label-default">30天图</span></a>
                        <a href="javascript:;" onclick="reload('graphScope', '100')"><span class="label label-default">100天图</span></a>
                    </div>
                    <h3 class="panel-title">{{ $fund->name }} (<a href="{{ route('fund', $fund['code']) }}">{{ $fund->code }}</a>)</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-9">
                            <table class="table">
                            <thead>
                                <tr>
                                    @foreach ($columns as $key => $column)
                                        <td><a href="javascript:;" onclick="reload('orderBy', '{{ $key }}')">
                                                {{ $column['name'] }}</a>
                                        </td>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                @foreach ($columns as $key => $column)
                                    <td>{{ $fund->$key ?: '—'}}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="13" class="text-center">→ 13天历史增长率 →</td>
                            </tr>
                            <tr>
                                @for ($i = 0; $i < 13; $i++)
                                    <td>{{ $fund->histories[$i]->rate }}</td>
                                @endfor
                            </tr>
                            </tbody>
                            </table>
                        </div>
                        <div class="col-md-3">
                            <div id="chartdiv-{{ $fund->code }}" style="width: auto; height: 170px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="/amcharts/amcharts.js"></script>
    <script src="/amcharts/serial.js"></script>
    <script src="/amcharts/amstock.js"></script>
    <script src="/amcharts/plugins/dataloader/dataloader.min.js"></script>
    <script src="/amcharts/plugins/export/export.min.js"></script>
    <script src="/amcharts/themes/light.js"></script>
    <script>
    AmCharts.ready(function () {
    @foreach($funds as $fund)
            var chart = new AmCharts.AmSerialChart();
            chart.dataProvider = {!! $fund->histories->take($graphScope)->values()->toJson() !!};
            chart.categoryField = "date";
            chart.autoMargins = false;
            chart.marginLeft = 0;
            chart.marginRight = 5;
            chart.marginTop = 0;
            chart.marginBottom = 0;

            var graph = new AmCharts.AmGraph();
            graph.valueField = "unit";
            graph.showBalloon = false;
            graph.lineColor = "#25bcec";
            graph.fillAlphas = 0.5;
            chart.addGraph(graph);

            var valueAxis = new AmCharts.ValueAxis();
            valueAxis.gridAlpha = 0;
            valueAxis.axisAlpha = 0;
            chart.addValueAxis(valueAxis);

            var categoryAxis = chart.categoryAxis;
            categoryAxis.gridAlpha = 0;
            categoryAxis.axisAlpha = 0;
            categoryAxis.startOnAxis = true;
            chart.write("chartdiv-{{ $fund->code }}");
    @endforeach
    });

    var params = {
        orderBy: "{{ request('orderBy') }}",
        sortedBy: "{{ request('sortedBy') }}",
        graphScope: "{{ $graphScope }}",
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
    </script>
@endpush
