@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pakoreguoti produktą</div>
 
                <div class="card-body">
                    <form action="{{route('product.update',$product)}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Kodas</label>
                            <input type="text" class="form-control" name="code" value="{{$product->code}}">
                        </div>
                        <div class="form-group">
                            <label>Klientai</label>
                            <select name="company_id">
                                <option value="">Pasirinkite klientą</option>
                                @foreach ($companies as $company)
                                    <option value="{{$company->id}}">{{$company->company_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pastabos</label>
                            <input type="text" class="form-control" name="description" value="{{$product->description}}">
                        </div>
                        <div class="form-group">
                            <label>Ruošinio plotis</label>
                            <input type="text" class="form-control" name="sheet_width" value="{{$product->sheet_width}}">
                        </div>
                        <div class="form-group">
                            <label>Ruošinio ilgis</label>
                            <input type="text" class="form-control" name="sheet_length" value="{{$product->sheet_length}}">
                        </div>
                        <div class="form-group">
                            <label>Lenkimai</label>
                            <input type="text" class="form-control" name="bending" value="{{$product->bending}}">
                        </div>
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
