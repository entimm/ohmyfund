@extends('layouts.app')

@section('content')
    <div class="container">
        @foreach($funds as $fund)
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $fund->name }} (<a href="{{ route('fund', $fund['code']) }}">{{ $fund->code }}</a>)</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-9">
                            <table class="table">
                            <thead>
                                <tr>
                                    <td>估算</td>
                                    <td>增长率</td>
                                    <td>近1周</td>
                                    <td>近1月</td>
                                    <td>近3月</td>
                                    <td>近6月</td>
                                    <td>今年</td>
                                    <td>近1年</td>
                                    <td>近2年</td>
                                    <td>近3年</td>
                                    <td>近5年</td>
                                    <td>成立来</td>
                                    <td>成立日期</td>
                                </tr>
                            </thead>
                            <tbody>
                                <td>{{ $fund->evaluateRate ?: '—'}}</td>
                                <td>{{ $fund->rate ?: '—'}}</td>
                                <td>{{ $fund->in_1week ?: '—'}}</td>
                                <td>{{ $fund->in_1month ?: '—'}}</td>
                                <td>{{ $fund->in_3month ?: '—'}}</td>
                                <td>{{ $fund->in_6month ?: '—'}}</td>
                                <td>{{ $fund->current_year ?: '—'}}</td>
                                <td>{{ $fund->in_1year ?: '—'}}</td>
                                <td>{{ $fund->in_2year ?: '—'}}</td>
                                <td>{{ $fund->in_3year ?: '—'}}</td>
                                <td>{{ $fund->in_5year ?: '—'}}</td>
                                <td>{{ $fund->since_born ?: '—'}}</td>
                                <td>{{ $fund->born_date ?: '—'}}</td>
                            </tbody>
                            </table>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <td colspan="7" class="text-center">→ 7天历史增长率 →</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <tr>
                                        @for ($i = 0; $i < 7; $i++)
                                            <td>{{ $fund->histories[$i]->rate }}</td>
                                        @endfor
                                    </tr>
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
            chart.dataProvider = {!! $fund->histories->toJson() !!};
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
    </script>
@endpush
