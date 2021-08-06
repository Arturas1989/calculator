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
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Sukurti programą</div>
                <div class="card-body">
                    <form action="{{route('pair.store')}}" method="post">
                        @csrf
                        <h4>Pasirinkite gofras, jei reikia, ir markes</h4>
                        <div class="form-group" style="display:flex">
                            <select multiple="multiple" id="boards" name="boards[]">
                                @foreach ( $boards as $board)
                                    <option value='{{$board->id}}'>{{$board->board_name}}</option>
                                @endforeach
                            </select>
                            <select multiple="multiple" id="marks" name="marks[]">
                                @foreach ( $marks as $mark)
                                    <option value='{{$mark->id}}'>{{$mark->mark_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input class="btn btn-primary" type="submit" value="Skaičiuoti">
                        <div class="input-row">
                            <div class="form-group flex-row">
                                <label>Gamybos data: nuo</label>
                                <input type="date" class="form-control" name="manufactury_date_from" value="{{old('manufactury_date_from')}}" required>
                            </div>
                            <div class="form-group flex-row">
                                <label>Gamybos data: iki</label>
                                <input type="date" class="form-control" name="manufactury_date_till" value="{{old('manufactury_date_till')}}" required>
                            </div>
                        </div>
                        <div class="input-row">
                            <div class="form-group flex-row">
                                <label>Krovimo data: nuo</label>
                                <input type="date" class="form-control" name="load_date" value="{{old('load_date')}}">
                            </div>
                            <div class="form-group flex-row">
                                <label>Krovimo data: iki</label>
                                <input type="date" class="form-control" name="load_date" value="{{old('load_date')}}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
