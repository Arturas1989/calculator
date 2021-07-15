@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pakoreguoti markę</div>
 
                <div class="card-body">
                    <form action="{{route('order.update',$order)}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Kodas</label>
                            <input type="text" class="form-control" name="code" value="{{$order->code}}">
                            {!! $order->errorsHTML('code',$errors) !!}
                        </div>
                        <div class="form-group">
                            <label>Kiekis</label>
                            <input type="text" class="form-control" name="quantity" value="{{$order->quantity}}">
                            {!! $order->errorsHTML('quantity',$errors) !!}
                        </div>
                        <div class="form-group">
                            <label>Atkrovimo data</label>
                            <input type="date" class="form-control" name="load_date" value="{{$order->load_date}}">
                            {!! $order->errorsHTML('load_date',$errors) !!}
                        </div>
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
