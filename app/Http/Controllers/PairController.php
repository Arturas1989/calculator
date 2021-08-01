<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use App\Models\Mark;
use App\Models\Order;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class PairController extends Controller
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pair.create',['marks' => $this->allMarks()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductsList($request, Mark $mark)
    {
        $productsList = [];

        foreach ($request->marks as $mark_id) 
        {
            $mark_name = $mark->find($mark_id)->mark_name;

            $orders = Order::whereHas('product', function ($q) use ($mark_id){
                return $q->where('mark_id', $mark_id);
            })->get()->all();
        
            foreach ($orders as $order) 
            {
                $product = $order->product()->get()->first();
                $company_name = $product->company->get()->first()->company_name;
                $product->description ? 
                $description =  $company_name . ' ' . $product->description : 
                $description =  $company_name;

                $product->bending ? $bending =  $product->bending : $bending =  '';

                $productsList[$mark_name][] = 
                 [
                    'code' => $order->code,
                    'description' => $description,
                    'sheet_width' => $product->sheet_width,
                    'sheet_length' => $product->sheet_length,
                    'quantity' => $order->quantity,
                    'bending' => $bending
                ];
            }
        }
        return $productsList;
    }

    public function maxWidthPair($array)
    {
        $maxSumArr = 
        [
            'maxSum' => 0,
            'pairIndex1' => -1,
            'pairIndex2' => -1
        ];

        $maxWidth = 2460;
    }

    public function isSingle($sheetWidth)
    {
        $maxRows = 8;
        $maxWidth = 2460;
        $minWidth = 2330;

        for ($i=1; $i <= $maxRows ; ++$i) { 
            if($sheetWidth * $i > $minWidth && $sheetWidth * $i <= $maxWidth){
                return true;
            }  
        }
        return false;
    }

    public function calculator($productsList)
    {
        $widerThan820 = [];
        $lessThan821 = [];
        $singles = [];

        

        foreach ($productsList as $key => $markProducts) {
            $widerThan820[$key] = array_filter($markProducts, function($el) {
                return $el['sheet_width'] > 820 && !$this->isSingle($el['sheet_width']);
            });
            $lessThan821[$key] = array_filter($markProducts, function($el) {
                return $el['sheet_width'] < 821 && !$this->isSingle($el['sheet_width']);
            });
            $singles[$key] = array_filter($markProducts, function($el) {
                return $this->isSingle($el['sheet_width']);
            });
        }
        dd( $lessThan821);
    }
    
    public function store(Request $request, Mark $mark)
    {
        $productsList = $this->getProductsList($request,$mark);
        $this->calculator($productsList);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function show(Pair $pair)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function edit(Pair $pair)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pair $pair)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pair $pair)
    {
        //
    }
}
