@extends('layouts.app')

@push('csses')
<style>
#chartdiv {
  width : 100%;
  height  : 600px;
}
</style>
<link rel="stylesheet" href="/amcharts/plugins/export/export.css"/>
@endpush

@section('content')
<div class="container">
    <div style="color:#333; font-weight:bold; text-align:center;">{{ $fund->name }}</div>
    <div id="chartdiv"></div>
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
var chart = AmCharts.makeChart("chartdiv", {
    type: "serial",
    theme: "light",
    marginRight: 40,
    marginLeft: 40,
    autoMarginOffset: 20,
    mouseWheelZoomEnabled:true,
    dataDateFormat: "YYYY-MM-DD",
    color: "#ccc",
    plotAreaFillColors: "#333",
    plotAreaFillAlphas: 1,
    valueAxes: [{
        id: "v1",
        axisAlpha: 0,
        position: "left",
        gridColor: "#555",
        gridAlpha: 1,
    }],
    balloon: {
        borderThickness: 1,
        shadowAlpha: 0
    },
    graphs: [{
        id: "g1",
        balloon:{
          drop:true,
          adjustBorderColor:false,
          horizontalPadding:0,
          color:"#000"
        },
        bullet: "round",
        bulletBorderAlpha: 1,
        bulletColor: "#FFFFFF",
        bulletSize: 5,
        hideBulletsCount: 50,
        lineThickness: 2,
        lineAlpha: 1,
        lineColor: "#ccc",
        fillAlphas: 0.5,
        useLineColorForBulletBorder: true,
        valueField: "unit",
        balloonText: "<span style='font-size:12px;'>[[value]]</span>"
    }],
    chartScrollbar: {
        graph: "g1",
        oppositeAxis:false,
        offset:30,
        scrollbarHeight: 50,
        graphType: "line",
        usePeriod: "WW",
        backgroundColor: "#333",
        graphFillColor: "#666",
        graphFillAlpha: 0.5,
        gridColor: "#555",
        gridAlpha: 1,
        selectedBackgroundColor: "#444",
        selectedGraphFillAlpha: 1
    },
    chartCursor: {
        pan: true,
        valueLineEnabled: true,
        valueLineBalloonEnabled: true,
        cursorAlpha:1,
        cursorColor:"#454545",
        limitToGraph:"g1",
        valueLineAlpha:0.2,
        valueZoomable:true
    },
    categoryField: "date",
    categoryAxis: {
        parseDates: true,
        dashLength: 1,
        minorGridEnabled: true,
        gridColor: "#555",
        gridAlpha: 1,
    },
    export: {
        enabled: true
    },
    dataLoader: {
        url: "/api/funds/{{ $fund->code }}/history",
        format: "json",
        showCurtain: true,
        showErrors: true,
        async: true,
        reverse: true,
        delimiter: ",",
        useColumnNames: true,
        complete: function (chart) {
            chart.addListener("dataUpdated", zoomChart);
        }
    }
});

function zoomChart() {
    chart.zoomToIndexes(chart.dataProvider.length - 300, chart.dataProvider.length - 1);
}
</script>
@endpush
