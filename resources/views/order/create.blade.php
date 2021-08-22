@push('scripts')
    <script id="script" src="../../resources/js/getPutData.js" data-getOrder="{{route('order.data',$order)}}">
    </script>
    <script src="../../resources/js/inputRender.js"></script>
@endpush
    

@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Sukurti užsakymą</div>
                <div class="card-body">
                    <form action="{{route('order.store')}}" method="post">
                        @csrf
                    <div class="click data">
                        <div class="input-row first">
                            <div class="form-group form">
                                <label>Kodas</label>
                                <input type="text"  class="form-control code" name="code-0" value="{{old('code-0')}}" required>
                                {!! $Order->errorsHTML('code-0',$errors) !!}
                            </div>
                            <div class="form-group form">
                                <label>Kiekis</label>
                                <input type="text" class="form-control click" name="quantity-0" value="{{old('quantity-0')}}" required>
                                {!! $Order->errorsHTML('quantity-0',$errors) !!}
                            </div>
                            <div class="form-group form">
                                <label>Gamybos data</label>
                                <input type="date" class="form-control" name="manufactury_date-0" value="{{old('manufactury_date-0')}}" required>
                                {!! $Order->errorsHTML('manufactury_date-0',$errors) !!}
                            </div>
                            <div class="form-group form">
                                <label>Krovimo data</label>
                                <input type="date" class="form-control" name="load_date-0" value="{{old('load_date-0')}}" required>
                                {!! $Order->errorsHTML('load_date-0',$errors) !!}
                            </div>
                            <div class="form-group form">
                                <label>Plotis</label><br>
                                <p id="width-0"></p>
                            </div>
                            <div class="form-group form">
                                <label>Ilgis</label><br>
                                <p id="length-0"></p>
                            </div>
                            <div class="form-group form">
                                <label>Iš ruošinio</label><br>
                                <p id="from_sheet_count-0"></p>
                            </div>
                        </div>
                            
                            @if(count(old()) > 0)
                                @for ($i=1;$i<(count(old())-1)/4;$i++)
                                    @php  
                                        $code = 'code-'.$i;
                                        $quantity = 'quantity-'.$i;
                                        $load_date = 'load_date-'.$i;
                                        $manufactury_date = 'manufactury_date-'.$i;
                                    @endphp
                                    <div class="input-row first">
                                        <div class="form-group form">
                                            <label>Kodas</label>
                                            <input type="text"  class="form-control code" name="{{$code}}" value="{{old($code)}}" required>
                                            {!! $Order->errorsHTML($code,$errors) !!}
                                        </div>
                                        <div class="form-group form">
                                            <label>Kiekis</label>
                                            <input type="text" class="form-control click" name="{{$quantity}}" value="{{old($quantity)}}" required>
                                            {!! $Order->errorsHTML($quantity,$errors) !!}
                                        </div>
                                        <div class="form-group form">
                                            <label>Data</label>
                                            <input type="date" class="form-control" name="{{$load_date}}" value="{{old($load_date)}}" required>
                                            {!! $Order->errorsHTML($load_date,$errors) !!}
                                        </div>
                                        <div class="form-group form">
                                            <label>Data</label>
                                            <input type="date" class="form-control" name="{{$manufactury_date}}" value="{{old($manufactury_date)}}" required>
                                            {!! $Order->errorsHTML($manufactury_date,$errors) !!}
                                        </div>
                                        <div class="form-group form">
                                            <label>Plotis</label><br>
                                            <p id="width-{{$i}}"></p>
                                        </div>
                                        <div class="form-group form">
                                            <label>Ilgis</label><br>
                                            <p id="length-{{$i}}"></p>
                                        </div>
                                        <div class="form-group form">
                                            <label>Iš ruošinio</label><br>
                                            <p id="from_sheet_count-{{$i}}"></p>
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
