@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pakoreguoti klientą</div>
 
                <div class="card-body">
                    <form action="{{route('company.update',$company)}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Kliento pavadinimas</label>
                            <input type="text" class="form-control" name="company_name" value="{{$company->company_name}}">
                        </div>
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
