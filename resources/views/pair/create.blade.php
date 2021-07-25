@extends('layouts.app')

@section('content')

@push('select-scripts')
    <script src="../../resources/js/multiselect.js"></script>
@endpush


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti programą</div>
                <div class="card-body">
                    
                    <select name=markIds[] class="multiple" multiple>
                        <option class="marks" id ="0" value="AL">Alabama</option>
                        <option class="marks" id ="1" value="AK">Alaska</option>
                        <option class="marks" id ="2" value="HI">Hawaii</option>
                        <option class="marks" id ="3" value="TN">Tennessee</option>
                        <option class="marks" id ="4" value="TX">Texas</option>
                        <option class="marks" id ="5" value="FL">Florida</option>
                        <option class="marks" id ="6" value="UT">Utah</option>
                        <option class="marks" id ="7" value="WY">Wyoming</option>
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
