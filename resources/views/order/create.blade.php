@push('scripts')
    <script type="module" src="../../resources/js/inputRender.js"></script>
@endpush

@extends('layouts.app')

@section('content')
{{-- {{dd($errors->get('quantity-0'))}} --}}

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
                                <input type="text"  class="form-control click " name="code-0" value="{{old('code-0')}}" required>
                                {{-- <div class="error">{{}}</div> --}}
                            </div>
                            <div class="form-group form first">
                                <label>Kiekis</label>
                                <input type="text" class="form-control" name="quantity-0" value="{{old('quantity-0')}}" required>
                            </div>
                            <div class="form-group form first">
                                <label>Data</label>
                                <input type="date" class="form-control" name="date-0" value="{{old('date-0')}}" required>
                            </div>
                            @if(count(old()) > 0)
                            
                            @for ($i=1;$i<(count(old())-1)/3;$i++)
                            @php  
                                $code = 'code-'.$i;
                                $quantity = 'quantity-'.$i;
                                $date = 'date-'.$i;
                            @endphp
                                    <div class="form-group form">
                                        <label>Kodas</label>
                                        <input type="text"  class="form-control click " name="{{$code}}" value="{{old($code)}}" required>
                                    </div>
                                    <div class="form-group form">
                                        <label>Kiekis</label>
                                        <input type="text" class="form-control" name="{{$quantity}}" value="{{old($quantity)}}" required>
                                    </div>
                                    <div class="form-group form">
                                        <label>Data</label>
                                        <input type="date" class="form-control" name="{{$date}}" value="{{old($date)}}" required>
                                    </div>
                                @endfor
                            @endif
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
