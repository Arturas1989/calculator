@extends('layouts.app')

@section('content')

{{-- @push('errors')
  
@endpush --}}

{{-- {{dd($errors->has('mark'))}} --}}
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
              
                <div class="card-header">Markės</div>
 
                <div class="card-body">
                  
                  
                    <table class="table" style="width:100%">
                        <tr>
                          <th>Markė</th>
                          <th>Pakoreguoti</th>
                          <th>Ištrinti</th>
                        </tr>
                  @foreach ($marks as $mark)
                  
                    <tr>
                      <td>{{$mark->mark_name}}</td>
                      <td><a class="btn btn-info" href="{{route('mark.edit',$mark)}}">Edit</a></td>
                      <td>
                        <form onclick="return confirm('Ar tikrai norite ištrinti?')" action="{{route('mark.destroy',$mark)}}" method="post">
                          @csrf
                          <input class="btn btn-danger" type="submit" value="Delete">
                        </form>
                      </td>
                    </tr>
                    @endforeach
                </table>
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
 
