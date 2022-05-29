@extends('layouts.app')

@section('content')



<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
              
                <div class="card-header">Gaminiai</div>
 
                <div class="card-body">
                  
                  
                    <table class="table" style="width:100%">
                        <tr>
                          <th>Kodas</th>
                          <th>Klientas</th>
                          <th>Pastabos</th>
                          <th>Ruošinio plotis</th>
                          <th>Ruošinio ilgis</th>
                          <th>Gaminių iš ruošinio</th>
                          <th>Lenkimai</th>
                          <th>Pakoreguoti</th>
                          <th>Ištrinti</th>
                        </tr>
                  @foreach ($products as $product)
                  
                    <tr>
                      <td>{{$product->code}}</td>
                      <td>{{$product->company->company_name}}</td>
                      <td>{{$product->description}}</td>
                      <td>{{$product->sheet_width}}</td>
                      <td>{{$product->sheet_length}}</td>
                      <td>{{$product->from_sheet_count}}</td>
                      <td>{{$product->bending}}</td>
                      <td><a class="btn btn-info" href="{{route('product.edit',$product)}}">Edit</a></td>
                      <td>
                        <form onclick="return confirm('Ar tikrai norite ištrinti?')" action="{{route('product.destroy',$product)}}" method="post">
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
 
