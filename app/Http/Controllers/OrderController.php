<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\State;
use Illuminate\Http\Request;
use Validator;
use Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->ProductController = new ProductController();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->State = new State();
    }

    
    public function data()
    {
        $products = Product::all();
        $data = [];

        foreach ($products as $product) {
            $data[] =   [
                            'code'=> $product->code,
                            'sheet_width'=> $product->sheet_width,
                            'sheet_length' => $product->sheet_length,
                            'from_sheet_count' => $product->from_sheet_count,
                        ];
        }
            
        return Response::json($data);
    }

    

    public function index()
    {
        $order = new Order();
        $orders = $order->createdProducts();
        return view('order.index',['orders'=>$orders]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd($request->all());
        $order = Order::find(3);
        return view('order.create',['Order'=>$this->Order,'order'=>$order]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderValidation($request,$code,$quantity,$load_date,$manufactury_date)
    {
        $validator = Validator ::make($request->only($code,$quantity,$load_date,$manufactury_date),
        [
            $code =>    [  'required', 
                        function ($attribute, $value, $fail) {
                            if (!$this->ProductController->markId($value)) {
                                $fail('Kodo: "'.$value.'" markės: "' 
                                . $this->ProductController->mark($value) . '" nėra markių sąraše.');
                            }
                        },
                        ],
            $quantity => ['required', 'numeric', 'integer','gt:0'],
            $load_date => ['required', 'date'],
            $manufactury_date => ['required', 'date'],
        ],
        [
            "$code.required" => 'reikalingas kodas',

            "$quantity.required" => 'reikalingas skaičius',
            "$quantity.numeric" => 'Turi būti skaičius',
            "$quantity.integer" => 'Turi būti sveikas skaičius',
            "$quantity.gt" => 'Turi būti teigiamas skaičius',

            "$load_date.required" => 'data privaloma',
            "$load_date.date" => 'reikalinga data',

            "$manufactury_date.required" => 'data privaloma',
            "$manufactury_date.date" => 'reikalinga data',
        ]

        );
        return $validator;
    }

    public function store(Request $request)
    {
        $requestArr = $request->all();
        $length = count($requestArr);
        $allErrors = [];

        for ($i=0; $i < (count($requestArr)-1)/4; $i++) {
            $code = 'code-'.$i;
            $quantity = 'quantity-'.$i;
            $load_date = 'load_date-'.$i;
            $manufactury_date = 'manufactury_date-'.$i;

            $validator = $this->orderValidation($request,$code,$quantity,$load_date,$manufactury_date);
            
            if($validator->errors()->get($code)){
                $allErrors[$code] = $validator->errors()->get($code);
            }
            if($validator->errors()->get($quantity)){
                $allErrors[$quantity] = $validator->errors()->get($quantity);
            }
            if($validator->errors()->get($load_date)){
                $allErrors[$load_date] = $validator->errors()->get($load_date);
            }
            if($validator->errors()->get($manufactury_date)){
                $allErrors[$manufactury_date] = $validator->errors()->get($manufactury_date);
            }

            $product_id = null;
            $nullArr = [];
            if($this->Product->where('code','=',$requestArr[$code])->get()->first()){
                $product_id = $this->Product
                ->where('code','=',$requestArr[$code])
                ->get()->first()->id;
            }

            
            if(count($allErrors)==0){
            $order = Order::create([
                'code' => $requestArr[$code],
                'product_id'=>$product_id,
                'quantity'=>$requestArr[$quantity],
                'manufactury_date'=>$requestArr[$manufactury_date],
                'load_date'=>$requestArr[$load_date],
                'state_id' => $this->State
                ->where('state_name','=','Open')->get()->first()->id,
            ]);
            }
            if(!$product_id){
                $nullArr[] = $product_id;
            }
           
        }

        if(count($allErrors)!=0){
            $request->flash();
            return redirect()->back()->withErrors($allErrors);
        }

        if($nullArr){
            $unknownProducts = $this->Order->where('product_id','=',null)->get()->all();
            return redirect()->route('product.create')
            ->withErrors(['msg'=>'Yra nesuvestų gaminių']);
            // ['unknownProducts'=>$unknownProducts]
        }

        return redirect()->route('order.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        return view('order.edit',['order'=>$order]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $code = 'code';
        $quantity = 'quantity';
        $load_date = 'load_date';
        $manufactury_date = 'manufactury_date';

        $validator = $this->orderValidation($request,$code,$quantity,$load_date,$manufactury_date);

        if($validator->fails()){
            $request->flash();
            return redirect()->back()->withErrors($validator);
            
        }

        $order->update(
            [
                'code' => $request->code,
                'quantity' => $request->quantity,
                'load_date' => $request->load_date,
                'manufactury_date' => $request->manufactury_date
            ]);
            
        $product = Product::where('code','=',$request->code)->get()->first();
        if(!$product)
        {
            $product = $order->product()->get()->first();
            $product->code = $request->code;
            $product->mark_id = $this->ProductController->markId($product->code);
            $product->save(); 
        }
        else
        {
            $order->product_id = $product->id;
        }

        $order->save();
        return redirect()->route('order.index'); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('order.index');
    }
}
