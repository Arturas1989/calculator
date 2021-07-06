<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allCompanies()
    {
        return Company::all();
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
        return view('company.create');
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
            'company_name.unique' => 'klientai negali kartotis',
        ]
    );

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }



        $company = company::create([
            'company_name' => $request->company_name,
        ]);
        return redirect()->route('company.index',['companies'=>$this->allCompanies()]);
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
        $company->delete();
        return redirect()->route('company.index');
    }
}
