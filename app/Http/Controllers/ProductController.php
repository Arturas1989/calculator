<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use App\Models\Board;
use App\Models\Mark;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allProducts()
    {
        return Product::all();
    }

    public function allCompanies()
    {
        return Company::all();
    }

    

    public function markBoard($code)
    {
        if(strlen($code)<8){
            return $code;
        }
        $num = substr($code,1,2);
        $board = substr($code,3,2);
        $color = substr($code,6,1);
        if($board[1]=='0' || $board[1]=='1'){
            $board = $board[0]; 
            $color = substr($code,5,1); 
        }
        return  $board.$num.$color;
    }
    public function markBoardIds($code)
    {
        $mark = $this->markBoard($code);
        
        if(!Mark::where('mark_name','=',$mark)->get()->first() 
        || !Board::where('board_name','=',$board)->get()->first()){
            return false;
        }
        $mark_id = Mark::where('mark_name','=',$mark)->get()[0]->id;
        $board_id = Board::where('board_name','=',$board)->get()[0]->id;
        return ['mark_id' => $mark_id, 'board_id' => $board_id];
    }

    public function index()
    {
        return view('product.index',['products'=>$this->allProducts()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('product.create',['companies' => $this->allCompanies()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!$this->markBoardIds($request->code)){
            $request->flash();
            return redirect()->back()->withErrors(['Blogas kodas']); 
        }


        $product = product::create([
            'code' => $request->code,
            'description' => $request->description,
            'sheet_width' => $request->sheet_width,
            'sheet_length' => $request->sheet_length,
            'bending' => $request->bending,
            'company_id' => $request->company_id,
            'mark_id' => $this->markBoardIds($request->code)['mark_id'],
            'board_id' => $this->markBoardIds($request->code)['board_id'],
        ]);
        return redirect()->route('product.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('product.edit',['product'=>$product,'companies' => $this->allCompanies()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        if(!$this->markBoardIds($request->code)){
            $request->flash();
            return redirect()->back()->withErrors(['Blogas kodas']); 
        }
        
        $product->code = $request->code;
        $product->description = $request->description;
        $product->sheet_width = $request->sheet_width;
        $product->sheet_length = $request->sheet_length;
        $product->bending = $request->bending;
        $product->company_id = $request->company_id;
        $product->board_id = $this->markBoardIds($request->code)['mark_id'];
        $product->mark_id = $this->markBoardIds($request->code)['board_id'];
        $product->save();
        return redirect()->route('product.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('product.index');
    }
}
