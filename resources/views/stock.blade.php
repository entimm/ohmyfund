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
          type: "stock",
          theme: "light",
          dataDateFormat: "YYYY-MM-DD",
          panels: [ {
              title: "Value",
              percentHeight: 70,
              stockGraphs: [ {
                  type: "candlestick",
                  id: "g1",
                  openField: "open",
                  closeField: "close",
                  highField: "high",
                  lowField: "low",
                  valueField: "close",
                  lineColor: "#fff",
                  fillColors: "#fff",
                  negativeLineColor: "#db4c3c",
                  negativeFillColors: "#db4c3c",
                  fillAlphas: 1,
                  comparedGraphLineThickness: 2,
                  columnWidth: 0.7,
                  useDataSetColors: false,
                  comparable: true,
                  compareField: "close",
                  showBalloon: false,
                  proCandlesticks: true
              } ],
              stockLegend: {
                  valueTextRegular: undefined,
                  periodValueTextComparing: "[[percents.value.close]]%"
                }
              }, {
                  title: "Volume",
                  percentHeight: 30,
                  marginTop: 1,
                  columnWidth: 0.6,
                  showCategoryAxis: false,
                  stockGraphs: [ {
                      valueField: "volume",
                      openField: "open",
                      type: "column",
                      showBalloon: false,
                      fillAlphas: 1,
                      lineColor: "#fff",
                      fillColors: "#fff",
                      negativeLineColor: "#db4c3c",
                      negativeFillColors: "#db4c3c",
                      useDataSetColors: false
                  } ],
                  stockLegend: {
                      markerType: "none",
                      markerSize: 0,
                      labelText: "",
                      periodValueTextRegular: "[[value.close]]"
                  },
                  valueAxes: [ {
                      usePrefixes: true
                  } ]
              }
          ],
          panelsSettings: {
              plotAreaFillColors: "#333",
              plotAreaFillAlphas: 1,
              marginLeft: 60,
              marginTop: 5,
              marginBottom: 5
          },
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
          categoryAxesSettings: {
              equalSpacing: true,
              gridColor: "#555",
              gridAlpha: 1
          },
          valueAxesSettings: {
              gridColor: "#555",
              gridAlpha: 1,
              inside: false,
              showLastLabel: true
          },
          chartCursorSettings: {
              pan: true,
              valueLineEnabled: true,
              valueLineBalloonEnabled: true
          },
          balloon: {
              textAlign: "left",
              offsetY: 10
          }
      });

      var stockDataSet = new AmCharts.DataSet();
      stockDataSet.title = '指数';
      stockDataSet.fieldMappings = [ {
          fromField: "open",
          toField: "open"
      }, {
          fromField: "high",
          toField: "high"
      }, {
          fromField: "low",
          toField: "low"
      }, {
          fromField: "close",
          toField: "close"
      }, {
          fromField: "volume",
          toField: "volume"
      } ];
      stockDataSet.compared = false,
          stockDataSet.categoryField = "date",
          stockDataSet.dataLoader = {
              url: "/api/stocks/{{ $symbol }}/candlesticks",
              format: "json",
              showCurtain: true,
              showErrors: true,
              async: true,
              reverse: true,
              delimiter: ",",
              useColumnNames: true,
              complete: function ( chart ) {
                  console.log(chart);
              }
          };
      chart.dataSets = [stockDataSet];
  </script>
@endpush