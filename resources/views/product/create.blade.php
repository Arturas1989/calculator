@extends('layouts.app')

@section('content')


<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">Sukurti gaminį</div>
                <div class="card-body">
                    @php
                        $notCreated = $Order->notCreatedProducts();
                    @endphp
                    @if ($notCreated)
                    <form action="{{route('product.store')}}" method="post">
                        @csrf
                        @for ( $i=0;$i<count($notCreated);++$i)
                            @php
                                $code_name = 'code-'.$i;
                                $company_id_name = 'company_id-'. $i;
                                $description_name = 'description-'.$i;
                                $sheet_width_name = 'sheet_width-'.$i;
                                $sheet_length_name = 'sheet_length-'.$i;
                                $from_sheet_count_name = 'from_sheet_count-'.$i;
                                $bending_name = 'bending-'.$i;
                                if(count(old())){
                                    $code = old($code_name);
                                }
                                else{
                                    $code = $notCreated[$i]->code;
                                }    
                            @endphp
                            <div class="input-row">
                                <div class="form-group product-form">
                                    <label>Kodas</label>
                                    <input type="text" class="form-control product-input" name="{{$code_name}}" value="{{$code}}">
                                    {!! $Order->errorsHTML('code',$errors) !!}
                                </div>
                                <div class="form-group product-form">
                                    <label>Klientai</label>
                                        
                                    <select class="product-input" name="{{$company_id_name}}">
                                        @if (old($company_id_name))
                                        
                                            @php
                                                $company_name = $companies->find(old($company_id_name))->get()->first()->company_name;
                                            @endphp
        
                                            <option value="{{old($company_id_name)}}">{{$company_name}}</option>
        
                                            @foreach ($companies as $company)
                                                @if($company->company_name != $company_name)
                                                    <option value="{{$company->id}}">{{$company->company_name}}</option>
                                                @endif
                                            @endforeach
        
                                        @else
                                            <option value="">Pasirinkite klientą</option>
        
                                            @foreach ($companies as $company)
                                                <option value="{{$company->id}}">{{$company->company_name}}</option>
                                            @endforeach
        
                                        @endif
                                    </select>
                                    {!! $Order->errorsHTML($company_id_name,$errors) !!}
                                </div>
                                <div class="form-group product-form">
                                    <label>Pastabos</label>
                                    <textarea  type="text" class="form-control" name="{{$description_name}}">{{old($description_name)}}</textarea>
                                </div>
                                <div class="form-group product-form">
                                    <label>Ruošinio plotis</label>
                                    <input type="text" class="form-control product-input" name="{{$sheet_width_name}}" value="{{old($sheet_width_name)}}">
                                    {!! $Order->errorsHTML($sheet_width_name,$errors) !!}
                                </div>
                                <div class="form-group product-form">
                                    <label>Ruošinio ilgis</label>
                                    <input type="text" class="form-control product-input" name="{{$sheet_length_name}}" value="{{old($sheet_length_name)}}">
                                    {!! $Order->errorsHTML($sheet_length_name,$errors) !!}
                                </div>
                                <div class="form-group product-form">
                                    <label>Gaminių iš ruošinio</label>
                                    <input type="text" class="form-control product-input" name="{{$from_sheet_count_name}}" value="{{old($from_sheet_count_name)}}">
                                    {!! $Order->errorsHTML($from_sheet_count_name,$errors) !!}
                                </div>
                                <div class="form-group product-form">
                                    <label>Lenkimai</label>
                                    <input type="text" class="form-control product-input" name="{{$bending_name}}" value="{{old($bending_name)}}">
                                </div>
                            </div>
                        @endfor
                        
                        <input class="btn btn-primary" type="submit" value="submit">
                    </form>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
