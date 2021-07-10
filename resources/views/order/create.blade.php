@push('scripts')
    <script type="module" src="../../resources/js/inputRender.js"></script>
@endpush

@extends('layouts.app')

@section('content')
{{-- {{dd($errors->get('quantity-0'))}} --}}

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Sukurti užsakymą</div>
                <div class="card-body">
                    <form action="{{route('order.store')}}" method="post">
                        @csrf
                    <div class="click">
                        <div class="input-row first">
                            <div class="form-group form">
                                <label>Kodas</label>
                                <input type="text"  class="form-control click " name="code-0" value="{{old('code-0')}}" required>
                                {!! $Order->errorsHTML('code-0',$errors) !!}
                            </div>
                            <div class="form-group form">
                                <label>Kiekis</label>
                                <input type="text" class="form-control" name="quantity-0" value="{{old('quantity-0')}}" required>
                                {!! $Order->errorsHTML('quantity-0',$errors) !!}
                            </div>
                            <div class="form-group form">
                                <label>Data</label>
                                <input type="date" class="form-control" name="date-0" value="{{old('date-0')}}" required>
                                {!! $Order->errorsHTML('date-0',$errors) !!}
                            </div>
                        </div>
                            
                            @if(count(old()) > 0)
                                @for ($i=1;$i<(count(old())-1)/3;$i++)
                                    @php  
                                        $code = 'code-'.$i;
                                        $quantity = 'quantity-'.$i;
                                        $date = 'date-'.$i;
                                    @endphp
                                    <div class="input-row first">
                                        <div class="form-group form">
                                            <label>Kodas</label>
                                            <input type="text"  class="form-control click " name="{{$code}}" value="{{old($code)}}" required>
                                            {!! $Order->errorsHTML($code,$errors) !!}
                                        </div>
                                        <div class="form-group form">
                                            <label>Kiekis</label>
                                            <input type="text" class="form-control" name="{{$quantity}}" value="{{old($quantity)}}" required>
                                            {!! $Order->errorsHTML($quantity,$errors) !!}
                                        </div>
                                        <div class="form-group form">
                                            <label>Data</label>
                                            <input type="date" class="form-control" name="{{$date}}" value="{{old($date)}}" required>
                                            {!! $Order->errorsHTML($date,$errors) !!}
                                        </div>
                                    </div>
                                @endfor
                            @endif
                    </div>
                    <div class = "btn-container">
                        <input class="btn btn-primary btn-orders" type="submit" value="submit">
                    </div>
                            
                    </form>
                    
                </div>
                <div class="close">X</div>
            </div>
        </div>
    </div>
 </div>
@endsection
