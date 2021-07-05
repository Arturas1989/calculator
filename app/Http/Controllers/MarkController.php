<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use Illuminate\Http\Request;
use Validator;

class MarkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allMarks()
    {
        return Mark::all();
    }

    public function index()
    {
        return view('mark.index',['marks'=>$this->allMarks()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('mark.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->only('mark_name'),
    
        [
            'mark_name' => ['unique:marks'],
        ],
        [
            'mark_name.unique' => 'markės negali kartotis',
        ]
    );

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }



        $mark = Mark::create([
            'mark_name' => $request->mark_name,
        ]);
        return redirect()->route('mark.index',['marks'=>$this->allMarks()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function show(Mark $mark)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function edit(Mark $mark)
    {
        return view('mark.edit',['mark'=>$mark]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mark $mark)
    {
        $validator = Validator::make($request->only('mark_name'),
    
        [
            'mark_name' => ['unique:marks'],
        ],
        [
            'mark_name.unique' => 'markės negali kartotis',
        ]
    );

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }
        $mark->mark_name = $request->mark_name;
        $mark->save();
        return redirect()->route('mark.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mark $mark)
    {
        $mark->delete();
        return redirect()->route('mark.index');
    }
}
