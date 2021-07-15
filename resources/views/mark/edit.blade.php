@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pakoreguoti markę</div>
 
                <div class="card-body">
                    <form action="{{route('mark.update',$mark)}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Markė</label>
                            <input type="text" class="form-control" name="mark_name" value="{{$mark->mark_name}}">
                            {!! $Order->errorsHTML('mark_name',$errors) !!}
                        </div>
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
