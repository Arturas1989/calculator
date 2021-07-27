@extends('layouts.app')

@section('content')

@push('select-scripts')
    <script src="../../resources/js/multiselect.js" defer></script>
    <script src="../../resources/js/multi-select/jquery.multi-select.js" type="text/javascript" defer></script>
@endpush


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti programą</div>
                <div class="card-body">
                    
                    <select multiple="multiple" id="your-select" name="my-select[]">
                        <option value='elem_1'>elem 1</option>
                        <option value='elem_2'>elem 2</option>
                        <option value='elem_3'>elem 3</option>
                        <option value='elem_4'>elem 4</option>
                        <option value='elem_100'>elem 100</option>
                    </select>

                    <div class="output"></div>
                        
                    @foreach ( $marks as $mark)
                        @php
                            $mark_name = $mark->mark_name;
                        @endphp
                        <form action="{{route('pair.store',$mark)}}" method="post">
                            @csrf
                            <div class="form-group">
                                <input type="hidden" class="form-control" name="mark_name" value="{{$mark_name}}">
                            </div>
                            
                            <input class="btn btn-primary" type="submit" value="{{$mark_name}}">
                        </form>
                    @endforeach
                    
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
