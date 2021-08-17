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
                    if(isset($request->load_date_from) && isset($request->load_date_till))
                    {
                        $from2 = $request->load_date_from;
                        $to2 = $request->load_date_till;
                        $orders = Order::whereBetween('manufactury_date', [$from, $to])
                        ->whereBetween('load_date', [$from2, $to2])
                        ->whereHas('product', function ($q) use ($mark_id)
                        {
                            return $q->where('mark_id', $mark_id);
                        })
                        ->get()->sortByDesc(function($q)
                        {
                            return $q->product()->get()->first()->sheet_width;
                         })
                        ->values()->all();
                    }
                    else
                    {
                        $orders = Order::whereBetween('manufactury_date', [$from, $to])
                        ->whereHas('product', function ($q) use ($mark_id)
                        {
                            return $q->where('mark_id', $mark_id);
                        })
                        ->get()->sortByDesc(function($q)
                        {
                            return $q->product()->get()->first()->sheet_width;
                         })
                        ->values()->all();
                    }
                    
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

    public function getArrays($widthType, Request $request, Board $board)
    {
        $productsList = $this->getProductsList($request, $board);
        $array = [];
        // dd($productsList);

        foreach ($productsList as $marks) 
        {
            foreach ($marks as $key => $markProducts) 
            {
                switch ($widthType)
                {
                    case 'widerThan820':
                        $array[$key] = array_filter($markProducts, function($el) {
                            return $el['sheet_width'] > 820 && !$this->isSingle($el['sheet_width']);
                        });
                    break;
                    case 'lessThan821':
                        $array[$key] = array_filter($markProducts, function($el) {
                            return $el['sheet_width'] < 821 && !$this->isSingle($el['sheet_width']);
                        });
                    break;
                    case 'singles':
                        $array[$key] = array_filter($markProducts, function($el) {
                            return $this->isSingle($el['sheet_width']);
                        });
                    default:
                    $array = [];
                }  
            } 
        }
        return $array;
    }
    public function maxWidthPair($width,$products)
    {
        // dd($products);
        $maxSumArr = 
        [
            'maxSum' => 0,
            'pairIndex' => -1,
        ];
        $maxRows = 8;
        $maxWidth = 2460;
        $minWidth = 2330;
        $rows = floor($maxWidth/$width);
        if($rows>8){
            $rows = $maxRows;
        }

        foreach ($products as $key => $product) {
            
            for ($i=1; $i <= $rows; ++$i) {
                $width_left = $maxWidth - $i * $width;
                $rows2 = floor($width_left/$product['sheet_width']);
                if($rows2>8){
                    $rows2 = $maxRows;
                } 
                 for ($j=1; $j <= $rows2 ; ++$j) {
                     $widthSum =  $width * $i + $product['sheet_width'] * $j;
                    if($widthSum >= $minWidth && $widthSum <= $maxWidth){
                        $maxSumArr = 
                        [
                            'maxSum' => $widthSum, 
                            'pairIndex' => $key,
                            'rows1' => $i,
                            'rows2' => $j
                        ];
                    }  
                 }
            }  
        }
        // dd($maxSumArr);
        return $maxSumArr['maxSum'] ? $maxSumArr : false;
    }

    public function isSingle($sheetWidth)
    {
        $maxRows = 8;
        $maxWidth = 2460;
        $minWidth = 2330;

        for ($i=1; $i <= $maxRows ; ++$i) { 
            if($sheetWidth * $i >= $minWidth && $sheetWidth * $i <= $maxWidth){
                return true;
            }  
        }
        return false;
    }

    public function calculator($productsArray, Request $request, Board $board)
    {
        $pairs = [];
        foreach ($productsArray as $key1 => &$mark) 
        {
            foreach ($mark as $key2 => &$searchProduct) 
            {
                $searchProductWidth = $searchProduct['sheet_width'];
                while (isset($mark[$key2]) && $this->maxWidthPair($searchProductWidth,$mark)) 
                {
                    $maxWidthArr = $this->maxWidthPair($searchProductWidth,$mark);
                
                    $searchProductMeters = $searchProduct['sheet_length'] * $searchProduct['quantity'] 
                    / $maxWidthArr['rows1']; 

                    $pairedIndex = $maxWidthArr['pairIndex'];
                    $pairedProduct = $mark[$pairedIndex];
                    $pairedProductMeters = $pairedProduct['sheet_length'] * $pairedProduct['quantity']
                    /$maxWidthArr['rows2'];

                    $searchProductMeters > $pairedProductMeters ? 
                    $meters = $pairedProductMeters : $meters = $searchProductMeters;

                    $searchProductQuantity = $meters * $maxWidthArr['rows1'] / $searchProduct['sheet_length'];
                    $pairedProductQuantity = $meters * $maxWidthArr['rows2'] / $pairedProduct['sheet_length'];

                    $searchProduct['quantity'] -= $searchProductQuantity;
                    $mark[$pairedIndex]['quantity'] -= $pairedProductQuantity;
                    $pairs[$key1][] = 
                    [
                        'meters' => round($meters/1000,0),
                        'product1' => 
                        [
                            'code' => $searchProduct['code'],
                            'description' => $searchProduct['description'],
                            'rows' => $maxWidthArr['rows1'],
                            'sheet_width' => $searchProduct['sheet_width'],
                            'sheet_length' => $searchProduct['sheet_length'],
                            'quantity' => round($searchProductQuantity,0),
                            'dates' => $searchProduct['dates']
                        ],
                        'product2' => 
                        [
                            'code' => $pairedProduct['code'],
                            'description' => $pairedProduct['description'],
                            'rows' => $maxWidthArr['rows2'],
                            'sheet_width' => $pairedProduct['sheet_width'],
                            'sheet_length' => $pairedProduct['sheet_length'],
                            'quantity' => round($pairedProductQuantity,0),
                            'dates' => $pairedProduct['dates']
                        ]
                    ];
                    if(!$searchProduct['quantity']){
                        unset($mark[$key2]);
                    }else{
                        unset($mark[$pairedIndex]);
                    }
                } 
            }  
        }
        return [$productsArray,$pairs];
    }
    
    public function store(Request $request, Board $board)
    {
        $widerThan820 = $this->getArrays('widerThan820',$request,$board);
        $lessThan821 = $this->getArrays('lessThan821',$request,$board);
        $singles = $this->getArrays('singles',$request,$board);
        $all = [$widerThan820,$lessThan821,$singles];
        $pairs = [];
        for ($i=0; $i < 3; $i++) {
            if($i){
                // dd('taip',$i,$remainingProducts);
                $merge = array_merge_recursive($remainingProducts,$all[$i]);
                $result = $this->calculator($merge,$request,$board);
                $pairs = array_merge_recursive($pairs,$result[1]);
            }
            else{
                $result = $this->calculator($all[$i],$request,$board);
                $pairs = $result[1];
            }
            $remainingProducts = $result[0];  
        }
        
        dd($pairs);
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
