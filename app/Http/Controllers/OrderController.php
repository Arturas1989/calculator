<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Validator;
use App\Rules\InMarks;

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
                                $fail('Kodo '.$value.' markės ' . $this->ProductController->markBoard($value) . ' nėra markių sąraše.');
                            }
                        },
                        ],
            $quantity => ['required', 'numeric', 'integer','gt:0'],
            $load_date => ['required', 'date'],
        ],
        [
            "$code.required" => 'reikalingas kodas',

            "$quantity.required" => 'reikalingas skaičius',
            "$quantity.numeric" => 'turi būti skaičius',
            "$quantity.integer" => 'turi būti sveikas skaičius',
            "$quantity.gt" => 'turi būti teigiamas skaičius',

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
            
           
        }

        // dd($allErrors);

        if($validator->fails()){//jei klaida
            $request->flash();
            return redirect()->back()->withErrors($allErrors);
        }

        // $art = Art::create([
        //     'title'=>$request->title,
        //     'description'=>$request->description,
        //     'price'=>$request->price,
        // ]);

        $request->flash();
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
