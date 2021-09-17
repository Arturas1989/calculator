@extends('layouts.app')

@section('content')

@push('select-scripts')
    <script src="../../resources/js/multiselect.js" defer></script>
    <script src="../../resources/js/jquery.quicksearch.js" defer></script>
    <script src="../../resources/js/multi-select/jquery.multi-select.js" type="text/javascript" defer></script>
    
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
                            <select multiple="multiple" id="boards" class="select" name="boards[]">
                                @foreach ( $boards as $board)
                                    <option id="board" value='{{$board->id}}'>{{$board->board_name}}</option>
                                @endforeach
                            </select>
                            <select multiple="multiple" id="marks" name="marks[]">
                                @php
                                    $marksOptions1 = '';
                                    $marksOptions2 = '';
                                    foreach($marks as $mark)
                                    {
                                        $mark_name = $mark->mark_name;
                                        $marksOptions1 .= "<option value='$mark->id'>$mark_name</option>";
                                        $marksOptions2 .= "<option value='$mark_name'>$mark_name</option>";
                                    }
                                @endphp
                                {!!$marksOptions1!!}
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
                                <input type="date" class="form-control" name="load_date_from" value="{{old('load_date_from')}}">
                            </div>
                            <div class="form-group flex-row">
                                <label>Krovimo data: iki</label>
                                <input type="date" class="form-control" name="load_date_till" value="{{old('load_date_till')}}">
                            </div>
                        </div>
                        <h4>Vėlesni užsakymai. Bus skaičiuojami, jei gausis didelės atliekos</h4>
                        <div class="input-row">
                            <div class="form-group flex-row">
                                <label>Gamybos data: nuo</label>
                                <input type="date" class="form-control" name="future_manufactury_date_from" value="{{old('manufactury_date_from')}}">
                            </div>
                            <div class="form-group flex-row">
                                <label>Gamybos data: iki</label>
                                <input type="date" class="form-control" name="future_manufactury_date_till" value="{{old('manufactury_date_till')}}">
                            </div>
                        </div>
                        <div class="input-row">
                            <div class="form-group flex-row">
                                <label>Krovimo data: nuo</label>
                                <input type="date" class="form-control" name="future_load_date_from" value="{{old('load_date_from')}}">
                            </div>
                            <div class="form-group flex-row">
                                <label>Krovimo data: iki</label>
                                <input type="date" class="form-control" name="future_load_date_till" value="{{old('load_date_till')}}">
                            </div>
                        </div>
                        <h4>Pasirinkite markes kurias galima jungti ir kurias galima prijungti</h4>
                        <div class="form-group" style="display:flex">
                            <div>
                                <h5>Originalios markės</h5>
                                <select multiple="multiple" id="marks_origin" class="select" name="marks_origin[]">
                                    {!!$marksOptions2!!}
                                </select>
                            </div>
                            <div>
                                <h5>Jungiamos markės</h5>
                                <select multiple="multiple" id="marks_join" name="marks_join[]">
                                    {!!$marksOptions2!!}
                                </select>
                            </div>
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
