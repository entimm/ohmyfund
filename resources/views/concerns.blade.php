@extends('layouts.app')

@push('csses')
<style>
.amcharts-main-div a[href*="www.amcharts.com"] {
    display: none!important;
}

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
    <div class="container">
        @foreach($funds as $fund)
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="pull-right">
                        <a href="javascript:;" onclick="reload('graphScope', '7')"><span class="label label-default">7天图</span></a>
                        <a href="javascript:;" onclick="reload('graphScope', '30')"><span class="label label-default">30天图</span></a>
                        <a href="javascript:;" onclick="reload('graphScope', '100')"><span class="label label-default">100天图</span></a>
                    </div>
                    <h3 class="panel-title">{{ $fund->name }} (<a href="{{ route('fund', $fund->code) }}" target="_blank">{{ $fund->code }}</a>)</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-9 table-responsive">
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
                                    <td class="rate-value">{{ $fund->$key ?: '—'}}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="13" class="text-center">→ 13天历史增长率 →</td>
                            </tr>
                            <tr>
                                @for ($i = 0; $i < 13; $i++)
                                    <td class="rate-value">
                                    {{ $fund->histories->take(-13)->reverse()->values()[$i]->rate }}</td>
                                @endfor
                            </tr>
                            </tbody>
                            </table>
                        </div>
                        <div class="col-md-3">
                            <div id="chartdiv-{{ $fund->code }}" style="width: auto; height: 150px;"></div>
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
            chart.dataProvider = {!! $fund->histories->take(-$graphScope)->values()->toJson() !!};
            chart.categoryField = "date";
            chart.autoMargins = false;
            chart.marginLeft = 0;
            chart.marginRight = 5;
            chart.marginTop = 0;
            chart.marginBottom = 0;

            var graph = new AmCharts.AmGraph();
            graph.valueField = "unit";
            graph.showBalloon = true;
            graph.lineColor = "#fe1c40";
            graph.fillAlphas = 0.5;
            graph.bullet = "round";
            // graph.hideBulletsCount = 50;
            graph.bulletBorderAlpha = 1;
            // graph.bulletColor = "#FFFFFF";
            graph.negativeLineColor = "#23dc1e";
            graph.bulletBorderColor = "#FFFFFF";
            graph.bulletSize = 6;
            graph.fillColors = "#25bcec";
            graph.lineThickness = 2;
            graph.useNegativeColorIfDown = true;
            // graph.useLineColorForBulletBorder = true;
            graph.balloonText = "<span style='font-size:12px;'>[[rate]] ([[date]])<br/>[[bonus]]</span>";
            graph.balloon = {
                color:"#000",
                cornerRadius: 5,
                borderThickness: 1,
                shadowAlpha: 0
            };
            chart.addGraph(graph);

            chart.chartCursor = {
                cursorAlpha: 0,
                valueLineEnabled: true,
                valueLineBalloonEnabled: true
            };

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
        orderBy: "{{ $orderBy }}",
        sortedBy: "{{ $sortedBy }}",
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
