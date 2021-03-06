@extends('layouts.app')

@section('content')



<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
              
                <div class="card-header">Klientai</div>
 
                <div class="card-body">
                  
                  
                    <table class="table" style="width:100%">
                        <tr>
                          <th>Klientas</th>
                          <th>Pakoreguoti</th>
                          <th>Ištrinti</th>
                        </tr>
                  @foreach ($companies as $company)
                  
                    <tr>
                      <td>{{$company->company_name}}</td>
                      <td><a class="btn btn-info" href="{{route('company.edit',$company)}}">Edit</a></td>
                      <td>
                        <form onclick="return confirm('Ar tikrai norite ištrinti?')" action="{{route('company.destroy',$company)}}" method="post">
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
 
