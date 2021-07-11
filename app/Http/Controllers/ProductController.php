<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Company;
use App\Models\Board;
use App\Models\Mark;
use Illuminate\Http\Request;
use Validator;

class ProductController extends Controller
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
            return ['mark'=>$code,'board'=>$code];
        }
        $num = substr($code,1,2);
        $board = substr($code,3,2);
        $color = substr($code,6,1);
        if($board[1]=='0' || $board[1]=='1'){
            $board = $board[0]; 
            $color = substr($code,5,1); 
        }
        return  ['mark'=>$board.$num.$color,'board'=>$board];
    }
    public function markBoardIds($code)
    {
        $markBoardArr = $this->markBoard($code);
        $mark = $markBoardArr['mark'];
        $board = $markBoardArr['board'];

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
        return view('product.create',['companies' => $this->allCompanies(),'Order'=>$this->Order]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
    
        [
            'code' => ['unique:products',
            function ($attribute, $value, $fail)
            {
                if(!$this->markBoardIds($value)){
                    $mark = $this->markBoard($value)['mark'];
                    $board = $this->markBoard($value)['board'];
                    $fail ('Nėra markės: "'. $mark. '" markių sąraše 
                    ir/arba nėra gofros: "'.$board.'" sąraše');
                }
            }
                                                        ],
            'sheet_width' => ['numeric','integer','gt:0'],
            'sheet_length' => ['numeric','integer','gt:0'],
        ],
        [
            'code.unique' => 'Toks kodas jau yra',

            'sheet_width.numeric' => 'Plotis turi būti skaičius',
            'sheet_width.integer' => 'Plotis turi būti sveikas skaičius',
            'sheet_width.gt' => 'Plotis turi būti didesnis nei nulis',

            'sheet_length.numeric' => 'Ilgis turi būti skaičius',
            'sheet_length.integer' => 'Ilgis turi būti sveikas skaičius',
            'sheet_length.gt' => 'Ilgis turi būti didesnis nei nulis',
        ],
    );
    

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator); 
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
