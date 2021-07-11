<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\State;
use Illuminate\Http\Request;
use Validator;

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

    
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd($request->all());
        return view('order.create',['Order'=>$this->Order]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestArr = $request->all();
        $length = count($requestArr);
        $allErrors = [];

        for ($i=0; $i < (count($requestArr)-1)/3; $i++) {
            $code = 'code-'.$i;
            $quantity = 'quantity-'.$i;
            $load_date = 'load_date-'.$i;


            $validator = Validator ::make($request->only($code,$quantity,$load_date),
        [
            $code =>    [  'required', 
                        function ($attribute, $value, $fail) {
                            if (!$this->ProductController->markBoardIds($value)) {
                                $fail('Kodo: "'.$value.'" markės: "' 
                                . $this->ProductController->markBoard($value)['mark'] . '" nėra markių sąraše.');
                            }
                        },
                        ],
            $quantity => ['required', 'numeric', 'integer','gt:0'],
            $load_date => ['required', 'date'],
        ],
        [
            "$code.required" => 'reikalingas kodas',

            "$quantity.required" => 'reikalingas skaičius',
            "$quantity.numeric" => 'Turi būti skaičius',
            "$quantity.integer" => 'Turi būti sveikas skaičius',
            "$quantity.gt" => 'Turi būti teigiamas skaičius',

            "$load_date.required" => 'data privaloma',
            "$load_date.date" => 'reikalinga data',
        ]

        );
            if($validator->errors()->get($code)){
                $allErrors[$code] = $validator->errors()->get($code);
            }
            if($validator->errors()->get($quantity)){
                $allErrors[$quantity] = $validator->errors()->get($quantity);
            }
            if($validator->errors()->get($load_date)){
                $allErrors[$load_date] = $validator->errors()->get($load_date);
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
            return redirect()->route('product.create',['unknownProducts'=>$unknownProducts])
            ->withErrors(['msg'=>'Yra nesuvestų gaminių']);
        }

        return redirect()->route('order.create');
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
