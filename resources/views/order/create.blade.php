@push('scripts')
    <script src="../../resources/js/main.js"></script>
@endpush

@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti užsakymą</div>
                <div class="card-body">
                    <form action="{{route('order.store')}}" method="post">
                        @csrf
                    <div class="click">
                            <div class="form-group form first">
                                <label>Kodas</label>
                                <input type="text" class="form-control click " name="code-0" value="{{old('code')}}">
                            </div>
                            <div class="form-group form first">
                                <label>Kiekis</label>
                                <input type="text" class="form-control" name="quantity-0" value="{{old('quantity')}}">
                            </div>
                            <div class="form-group form first">
                                <label>Data</label>
                                <input type="date" class="form-control" name="date-0" value="{{old('date')}}">
                            </div>
                            
                        
                    </div>
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                    
                </div>
                <div class="close">X</div>
            </div>
        </div>
    </div>
 </div>
@endsection
