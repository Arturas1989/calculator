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
    public function calculator($request)
    {
        
        $id = $request->marks[0];
        $orders = Order::whereHas('product', function ($q) use ($id){
            return $q->where('mark_id', $id);
        })->get()->all();
        $productsList = [];
        foreach ($orders as $order) {
            $product = $order->product()->get()->first();
            $company_name = $product->company->get()->first()->company_name;
            $product->description ? 
            $description =  $company_name . ' ' . $product->description : 
            $description =  $company_name;

            $product->bending ? $bending =  $product->bending : $bending =  '';

            $productsList[] = 
            [
                'code' => $order->code,
                'description' => $description,
                'sheet_width' => $product->sheet_width,
                'sheet_length' => $product->sheet_length,
                'quantity' => $order->quantity,
                'bending' => $bending
            ];
        }
        dd($productsList);
    }

    public function store(Request $request)
    {
        $this->calculator($request);
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
