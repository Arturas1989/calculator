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
        $Order = new Order();
        return view('product.create',['companies' => $this->allCompanies(),'Order'=>$Order]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function productValidation($request,$code,$company_id,
    $description,$sheet_width,$sheet_length,$from_sheet_count,$bending)
    {
        
        $validator = Validator::make($request->only($code,$company_id,
            $description,$sheet_width,$sheet_length,$from_sheet_count,$bending),
    
            [
                $code => ['unique:products,code',
                            function ($attribute, $value, $fail)
                            {
                                if(!$this->markBoardIds($value))
                                {
                                    $mark = $this->markBoard($value)['mark'];
                                    $board = $this->markBoard($value)['board'];
                                    $fail ('Nėra markės: "'. $mark. '" markių sąraše 
                                    ir/arba nėra gofros: "'.$board.'" sąraše');
                                }
                            }
                        ],
                $company_id => ['required'],
                $sheet_width => ['numeric','integer','gt:0'],
                $sheet_length => ['numeric','integer','gt:0'],
                $from_sheet_count => ['numeric','integer','gt:0'],
            ],
            [
                "$code.unique" => 'Toks kodas jau yra',
                
                "$company_id.required" => 'Nepasirinkote kliento',

                "$sheet_width.numeric" => 'Plotis turi būti skaičius',
                "$sheet_width.integer" => 'Plotis turi būti sveikas skaičius',
                "$sheet_width.gt" => 'Plotis turi būti didesnis nei nulis',

                "$sheet_length.numeric" => 'Ilgis turi būti skaičius',
                "$sheet_length.integer" => 'Ilgis turi būti sveikas skaičius',
                "$sheet_length.gt" => 'Ilgis turi būti didesnis nei nulis',

                "$from_sheet_count.numeric" => 'Kiekis turi būti skaičius',
                "$from_sheet_count.integer" => 'Kiekis turi būti sveikas skaičius',
                "$from_sheet_count.gt" => 'Kiekis turi būti didesnis nei nulis',
            ],
        );
        return $validator;
    }

    public function store(Request $request)
    {
        $requestArr = $request->all();
        $length = count($requestArr);
        $allErrors = [];

        for ($i=0; $i < ($length-1)/7; $i++) 
        {
            $code = 'code-'.$i;
            $company_id = 'company_id-'. $i;
            $description = 'description-'.$i;
            $sheet_width = 'sheet_width-'.$i;
            $sheet_length = 'sheet_length-'.$i;
            $from_sheet_count = 'from_sheet_count-'.$i;
            $bending = 'bending-'.$i;

            $validator = $this->productValidation($request,$code,$company_id,
            $description,$sheet_width,$sheet_length,$from_sheet_count,$bending);

            if($validator->errors()->get($code))
            {
                $allErrors[$code] = $validator->errors()->get($code);
            }

            if($validator->errors()->get($company_id))
            {
                $allErrors[$company_id] = $validator->errors()->get($company_id);
            }

            if($validator->errors()->get($sheet_width))
            {
                $allErrors[$sheet_width] = $validator->errors()->get($sheet_width);
            }

            if($validator->errors()->get($sheet_length))
            {
                $allErrors[$sheet_length] = $validator->errors()->get($sheet_length);
            }

            if($validator->errors()->get($from_sheet_count))
            {
                $allErrors[$from_sheet_count] = $validator->errors()->get($from_sheet_count);
            }
        }

        if(count($allErrors)){
            // dd($allErrors);
            $request->flash();
            return redirect()->back()->withErrors($allErrors); 
        }

        for ($i=0; $i < ($length-1)/7; $i++) 
        {
            $code = 'code-'.$i;
            $company_id = 'company_id-'. $i;
            $description = 'description-'.$i;
            $sheet_width = 'sheet_width-'.$i;
            $sheet_length = 'sheet_length-'.$i;
            $from_sheet_count = 'from_sheet_count-'.$i;
            $bending = 'bending-'.$i;

            $product = product::create
             ([
                'code' => $requestArr[$code],
                'description' => $requestArr[$description],
                'sheet_width' => $requestArr[$sheet_width],
                'sheet_length' => $requestArr[$sheet_length],
                'from_sheet_count' => $requestArr[$from_sheet_count],
                'bending' => $requestArr[$bending],
                'company_id' => $requestArr[$company_id],
                'mark_id' => $this->markBoardIds($requestArr[$code])['mark_id'],
                'board_id' => $this->markBoardIds($requestArr[$code])['board_id'],
            ]);
            
            $order = Order::where('code','=',$requestArr[$code])->get()->first();
            if($order)
            {
                Order::where('code','=',$requestArr[$code])
                ->update(['product_id' => $product->id]);
            }  
        }
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
    public function edit(Product $product, Order $order)
    {
        $companies = Company::where('id','!=',$product->company_id)->get()->all();
        return view('product.edit',['product'=>$product,'companies' => $companies,'order'=>$order]);
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
        $validator = $this->productValidation($request,'code','company_id',
            'description','sheet_width','sheet_length','from_sheet_count','bending');

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator); 
        }
        
        $product->update
        (
            [
                'code' => $request->code,
                'description' => $request->description,
                'from_sheet_count' => $request->sheet_width,
                'sheet_width' => $request->sheet_width,
                'sheet_length' => $request->sheet_length,
                'bending' => $request->bending,
                'company_id' => $request->company_id,
                'mark_id' => $this->markBoardIds($request->code)['mark_id'],
                'board_id' => $this->markBoardIds($request->code)['board_id'], 
            ]
        );
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
