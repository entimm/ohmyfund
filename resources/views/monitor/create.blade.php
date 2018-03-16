@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">新增股票监听</div>

                <div class="panel-body">
                    <div class="col-md-8">
                        <form method="post" action="{{route('monitor.store')}}">
                          <div class="form-group">
                            <label for="date">日期</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{date('Y-m-d')}}">
                          </div>
                          <div class="form-group">
                            <label for="data">内容</label>
                            <textarea class="form-control" id="data" name="data" rows="20"></textarea>
                          </div>

                          <div class="form-group row">
                            <div class="col-sm-10">
                              {{ csrf_field() }}
                              <button type="submit" class="btn btn-primary">提交</button>
                            </div>
                          </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
