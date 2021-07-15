@extends('layouts.app')

@section('content')



<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
              
                <div class="card-header">Užsakymai</div>
 
                <div class="card-body">
                  
                  
                    <table class="table" style="width:100%">
                        <tr>
                          <th>Kodas</th>
                          <th>Klientas</th>
                          <th>Kiekis</th>
                          <th>Atkrovimo data</th>
                          <th>Ruošinio plotis</th>
                          <th>Ruošinio ilgis</th>
                          <th>Krenta</th>
                          <th>Pakoreguoti</th>
                          <th>Ištrinti</th>
                        </tr>
                  @foreach ($orders as $order)
                  
                    <tr>
                      <td>{{$order->code}}</td>
                      <td>{{$order->product()->get()->first()
                      ->company()->get()->first()->company_name}}</td>
                      <td>{{$order->quantity}}</td>
                      <td>{{$order->load_date}}</td>
                      <td>{{$order->product()->get()->first()->sheet_width}}</td>
                      <td>{{$order->product()->get()->first()->sheet_length}}</td>
                      <td>{{$order->product()->get()->first()->from_sheet_count}}</td>
                      <td><a class="btn btn-info" href="{{route('order.edit',$order)}}">Edit</a></td>
                      <td>
                        <form onclick="return confirm('Ar tikrai norite ištrinti?')" action="{{route('order.destroy',$order)}}" method="post">
                          @csrf
                          <input class="btn btn-danger" type="submit" value="Delete">
                        </form>
                      </td>
                    </tr>
                    @endforeach
                </table>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
 
