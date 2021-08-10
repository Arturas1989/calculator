<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use App\Models\Mark;
use App\Models\Board;
use App\Models\Order;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PairController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function marks()
    {
        return Mark::where(\DB::raw("substr(mark_name, 1, 2)"),'=','BC')
        ->orderBy('mark_name','desc')->limit(2)->get();
    }

    public function allBoards()
    {
        return Board::all();
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
        // dd($this->marks());
        return view('pair.create',['boards' => $this->allBoards(),'marks' => $this->marks()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductsList($request, Board $board)
    {
        $productsList = [];
        isset($request->boards) ? $boards = $request->boards : 
        $boards = [Board::where('board_name','BC')->get()->first()->id];
        
            foreach ($boards as $board_id) 
            {
                $board = Board::find($board_id);
                $board_name = $board->board_name;
                $marks = $board->marks()->get();

                foreach ($marks as $mark) 
                {
                    $mark_name = $mark->mark_name;
                    if(!isset($request->boards) && $mark_name!='BC25R' && $mark_name!='BC24R'){
                        continue;
                    }
                    
                    $mark_id = $mark->id;
                    $from = $request->manufactury_date_from;
                    $to = $request->manufactury_date_till;
                    $orders = Order::whereBetween('manufactury_date', [$from, $to])
                    ->whereHas('product', function ($q) use ($mark_id){
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
                        $dates = substr($order->manufactury_date,-2).' ('.substr($order->load_date,-2).')';
                        $productsList[$board_name][$mark_name][] = 
                        [
                            'code' => $order->code,
                            'description' => $description,
                            'sheet_width' => $product->sheet_width,
                            'sheet_length' => $product->sheet_length,
                            'quantity' => $order->quantity,
                            'dates' => $dates,
                            'bending' => $bending,
                            'order_id' => $order->id,
                        ];
                    }
                }   
            }
        
        

        

        if(isset($request->marks) && count($request->marks) == 1)
        {
            foreach ($request->marks as $mark_id) 
            {
                if(Mark::find($mark_id)->mark_name == 'BC24R')
                {
                    unset($productsList['BC']['BC25R']);
                }
                else
                {
                    unset($productsList['BC']['BC24R']);
                }
            }
        }
        
        if(!isset($request->marks))
        {
            unset($productsList['BC']['BC24R']);
            unset($productsList['BC']['BC25R']);
        }
        
        // dd($productsList);
        return $productsList;
    }

    public function maxWidthPair($array)
    {
        $maxSumArr = 
        [
            'maxSum' => 0,
            'pairIndex' => -1,
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

        foreach ($productsList as $marks) {
            foreach ($marks as $key => $markProducts) {
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
        }
        dd( $lessThan821);
    }
    
    public function store(Request $request, Board $board)
    {
        // dd($request->all());
        $productsList = $this->getProductsList($request,$board);
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
