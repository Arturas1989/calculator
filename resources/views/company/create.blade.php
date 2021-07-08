@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti klientą</div>
                <div class="card-body">
                    <form action="{{route('order.store')}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Kliento pavadinimas</label>
                            <input type="text" class="form-control" name="company_name" value="{{old('company_name')}}">
                        </div>
                        
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
