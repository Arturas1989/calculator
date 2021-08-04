@extends('layouts.app')

@section('content')

@push('select-scripts')
    <script src="../../resources/js/multiselect.js" defer></script>
    <script src="../../resources/js/jquery.quicksearch.js" defer></script>
    <script src="../../resources/js/multi-select/jquery.multi-select.js" type="text/javascript" defer>
        
    </script>
@endpush

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti programą</div>
                <div class="card-body">
                    <form action="{{route('pair.store')}}" method="post">
                        @csrf
                        <h4>Gofrų sąrašas, kurias pasirinkus bus suskaičiuotos visos markės</h4>
                        <div class="form-group">
                            <select multiple="multiple" id="boards" name="boards[]">
                                @foreach ( $boards as $board)
                                    <option value='{{$board->id}}'>{{$board->board_name}}</option>
                                @endforeach
                                <option value='{{$marks->}}'>{{$board->board_name}}</option>
                            </select>
                        </div>
                        <input class="btn btn-primary" type="submit" value="Skaičiuoti">
                        <div class="input-row">
                            <div class="form-group date-row">
                                <label>Gamybos data: nuo</label>
                                <input type="date" class="form-control" name="manufactury_date" value="{{old('manufactury_date')}}" required>
                            </div>
                            <div class="form-group date-row">
                                <label>Gamybos data: iki</label>
                                <input type="date" class="form-control" name="manufactury_date" value="{{old('manufactury_date')}}" required>
                            </div>
                        </div>
                        <div class="input-row">
                            <div class="form-group date-row">
                                <label>Krovimo data: nuo</label>
                                <input type="date" class="form-control" name="load_date" value="{{old('load_date')}}" required>
                            </div>
                            <div class="form-group date-row">
                                <label>Krovimo data: iki</label>
                                <input type="date" class="form-control" name="load_date" value="{{old('load_date')}}" required>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
