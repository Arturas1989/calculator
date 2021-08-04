<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Board;
use App\Models\Order;
use Illuminate\Http\Request;
use Validator;

class MarkController extends Controller
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
        return view('mark.create',['Order'=>$this->Order]);
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
            'mark_name.unique' => 'Tokia markė jau yra',
        ]
    );

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
        }

        $board = substr($request->mark_name,0,2);
        if(is_numeric($board[1])){
            $board = $board[0];
        }
        $board_id = Board::where('board_name',$board)->get()->first()->id;

        $mark = Mark::create([
            'mark_name' => $request->mark_name,
            'board_id' => $board_id,
        ]);
        return redirect()->route('mark.index');
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
        return view('mark.edit',['mark'=>$mark,'Order'=>$this->Order]);
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
            'mark_name' => ['unique:marks','required'],
        ],
        [
            'mark_name.unique' => 'Tokia markė jau yra',
            'mark_name.required' => 'Neįrašyta markė',
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
        $countProducts = $mark->product()->get()->count();
        if($countProducts)
        {
            return redirect()->back()
            ->withErrors(array('mark' => 'Yra neištrintų gaminių, kurie turi markę: '.$mark->mark_name));
        }
        $mark->delete();
        return redirect()->route('mark.index');
    }
}
