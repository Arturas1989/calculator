@extends('layouts.app')

@section('content')


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sukurti programą</div>
                <div class="card-body">
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
