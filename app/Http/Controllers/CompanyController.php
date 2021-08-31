<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Http\Request;
use Validator;
use Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->Order = new Order();
    }

    public function allCompanies()
    {
        return Company::all();
    }

    public function data()
    {
        $companies = $this->allCompanies();
        $data = [];

        foreach ($companies as $company) {
            $data[] =   [
                            'id'=> $company->id,
                            'company_name'=> $company->company_name,
                        ];
        }
            
        return Response::json($data);
    }

    public function index()
    {
        return view('company.index',['companies'=>$this->allCompanies()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('company.create',['Order'=>$this->Order]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only('company_name'),
    
        [
            'company_name' => ['unique:companies'],
        ],
        [
            'company_name.unique' => 'Toks klientas jau yra',
        ]
    );

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }



        $company = company::create([
            'company_name' => $request->company_name,
        ]);
        return redirect()->route('company.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('company.edit',['company'=>$company]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->only('company_name'),
    
        [
            'company_name' => ['unique:companies'],
        ],
        [
            'company_name.unique' => 'klientai negali kartotis',
        ]
    );

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }
        $company->company_name = $request->company_name;
        $company->save();
        return redirect()->route('company.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $productCount = $company->product()->get()->count();
        if($productCount)
        {
            return redirect()->back()
            ->withErrors(array('company' => 'Yra neištrintų gaminių, kurie turi klientą: '.$company->company_name));
        }
        $company->delete();
        return redirect()->route('company.index');
    }
}
