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
                        <div class="form-group">
                            <select multiple="multiple" id="marks" name="marks[]">
                                @foreach ( $marks as $mark)
                                
                                    <option value='{{$mark->id}}'>{{$mark->mark_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input class="btn btn-primary" type="submit" value="Skaičiuoti">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
