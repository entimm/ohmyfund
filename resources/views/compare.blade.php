@extends('layouts.app')

@push('csses')
    <style>
        #chartdiv {
            width : 100%;
            height  : 600px;
        }
        .amcharts-main-div a[href*="www.amcharts.com"] {
            display: none!important;
        }
    </style>
    <link rel="stylesheet" href="/amcharts/plugins/export/export.css"/>
@endpush

@section('content')
    <div class="container">
        <div class="box">
            <div id="chartdiv"></div>
        </div>
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
    let chart = AmCharts.makeChart( "chartdiv", {
      type: "stock",
      theme: "light",
      dataDateFormat: "YYYY-MM-DD",
      mouseWheelZoomEnabled: true,
      panels: [ {
        showCategoryAxis: true,
        title: "Value",
        percentHeight: 70,
        stockGraphs: [ {
          id: "g1",
          valueField: "value",
          comparable: true,
          compareField: "value",
          compareGraphLineThickness: 2,
          compareGraphLineAlpha: 1,
          lineThickness: 2,
          lineAlpha: 1,
          compareGraph: {
              balloon: {
                  color:"#000",
                  cornerRadius: 5,
                  borderThickness: 1,
                  shadowAlpha: 0
              },
          },
          balloon: {
              color:"#000",
              cornerRadius: 5,
              borderThickness: 1,
              shadowAlpha: 0
          },
          balloonText: "[[title]]:<b>[[value]]</b>",
          compareGraphBalloonText: "[[title]]:<b>[[value]]</b>"
        } ],
      } ],

      chartScrollbarSettings: {
          graph: "g1",
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

      chartCursorSettings: {
        pan: true,
        valueBalloonsEnabled: true,
        fullWidth: true,
        cursorAlpha: 0.1,
        valueLineBalloonEnabled: true,
        valueLineEnabled: true,
        valueLineAlpha: 0.5
      },

      periodSelector: {
        position: "bottom",
        periods: [ {
          period: "MM",
          selected: true,
          count: 1,
          label: "1 month"
        }, {
          period: "YYYY",
          count: 1,
          label: "1 year"
        }, {
          period: "YTD",
          label: "YTD"
        }, {
          period: "MAX",
          label: "MAX"
        } ]
      },

      export: {
        enabled: true
      }
    } );

    let stocks = {!! $compareStocksJson !!};
    stocks.forEach(function(item, index) {
        chart.dataSets.push(
            {
                title: item.title,
                compared:true,
                fieldMappings: [ {
                    fromField: "value",
                    toField: "value"
                } ],
                dataLoader: {
                    url: "/api/stocks/"+item.symbol+"/values",
                    format: "json",
                    showCurtain: true,
                    showErrors: true,
                    async: true,
                    reverse: true,
                    delimiter: ",",
                    useColumnNames: true,
                },
                categoryField: "date"
            }
        );
        chart.panels[0].stockGraphs.push();
    });

    let funds = {!! $compareFundsJson !!};
    funds.forEach(function(item, index) {
        chart.dataSets.push(
            {
                title: item.title,
                compared:true,
                fieldMappings: [ {
                    fromField: "unit",
                    toField: "value"
                } ],
                dataLoader: {
                    url: "/api/funds/"+item.code+"/history",
                    format: "json",
                    showCurtain: true,
                    showErrors: true,
                    async: true,
                    reverse: true,
                    delimiter: ",",
                    useColumnNames: true,
                },
                categoryField: "date"
            }
        );
        chart.panels[0].stockGraphs.push();
    });
    </script>
@endpush
