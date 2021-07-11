@extends('layouts.app')

@section('content')


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti gaminį</div>
                <div class="card-body">
                    <form action="{{route('product.store')}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Kodas</label>
                            <input type="text" class="form-control" name="code" value="{{old('code')}}">
                            {!! $Order->errorsHTML('code',$errors) !!}
                        </div>
                        <div class="form-group">
                            <label>Klientai</label>
                                
                            <select name="company_id">
                                @if (old('company_id'))
                                
                                    @php
                                        $company_name = $companies->find(old('company_id'))->get()->first()->company_name;
                                    @endphp

                                    <option value="{{old('company_id')}}">{{$company_name}}</option>

                                    @foreach ($companies as $company)
                                        @if($company->company_name != $company_name)
                                            <option value="{{$company->id}}">{{$company->company_name}}</option>
                                        @endif
                                    @endforeach

                                @else
                                    <option value="">Pasirinkite klientą</option>

                                    @foreach ($companies as $company)
                                        <option value="{{$company->id}}">{{$company->company_name}}</option>
                                    @endforeach

                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pastabos</label>
                            <input type="text" class="form-control" name="description" value="{{old('description')}}">
                        </div>
                        <div class="form-group">
                            <label>Ruošinio plotis</label>
                            <input type="text" class="form-control" name="sheet_width" value="{{old('sheet_width')}}">
                            {!! $Order->errorsHTML('sheet_width',$errors) !!}
                        </div>
                        <div class="form-group">
                            <label>Ruošinio ilgis</label>
                            <input type="text" class="form-control" name="sheet_length" value="{{old('sheet_length')}}">
                            {!! $Order->errorsHTML('sheet_length',$errors) !!}
                        </div>
                        <div class="form-group">
                            <label>Lenkimai</label>
                            <input type="text" class="form-control" name="bending" value="{{old('bending')}}">
                        </div>
                        
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
