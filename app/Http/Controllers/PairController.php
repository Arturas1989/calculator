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
    public function params(){
        return 
        [
            'quantityPercentage' => 0.05,
            'minMeters' => 70,
            'maxWidth' => 2460,
            'minWidth' => 2330,
            'maxRows' => 8
        ];
    }

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
                        
                        $company_name = $product->company()->get()->first()->company_name;
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
                            'totalQuantity' => $order->quantity,
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

        foreach ($productsList as $board => $marks) 
        {
            foreach ($marks as $mark => $markProducts) 
            {
                switch ($widthType)
                {
                    case 'widerThan820':
                        $array[$board][$mark] = array_filter($markProducts, function($el) {
                            return $el['sheet_width'] > 820 && !$this->isSingle($el['sheet_width']);
                        });
                    break;
                    case 'lessThan821':
                        $array[$board][$mark] = array_filter($markProducts, function($el) {
                            return $el['sheet_width'] < 821 && !$this->isSingle($el['sheet_width']);
                        });
                    break;
                    case 'singles':
                        $array[$board][$mark] = array_filter($markProducts, function($el) {
                            return $this->isSingle($el['sheet_width']);
                        });
                    break;
                    default:
                    $array = [];
                }  
            } 
        }
        return $array;
    }

    public function checkMetersQuantity($maxWidth, $minMeters, $searchProduct, $pairedProduct, $rows1, $rows2)
    {
        $searchProduct['rows'] = $rows1;
        $pairedProduct['rows'] = $rows2;

        $searchProductMilimeters = $searchProduct['sheet_length'] 
        * $searchProduct['quantity'] / $rows1; 

        $pairedProductMilimeters = $pairedProduct['sheet_length'] 
        * $pairedProduct['quantity']/$rows2;

        if($searchProductMilimeters <= $pairedProductMilimeters){
            $checkMetersProduct = $pairedProduct;
            $checkQuantityProduct = $searchProduct;
            $minMilimeters = $searchProductMilimeters;
            $maxMilimeters = $pairedProductMilimeters;
        }
        else{
            $checkMetersProduct = $searchProduct;
            $checkQuantityProduct = $pairedProduct;
            $minMilimeters = $pairedProductMilimeters;
            $maxMilimeters = $searchProductMilimeters;
        }
        $difMilimeters = $maxMilimeters - $minMilimeters;
        $additionalQuantity =  round($difMilimeters * $checkQuantityProduct['rows'] 
        / $checkQuantityProduct['sheet_length'],0);

        if($additionalQuantity / $checkQuantityProduct['totalQuantity'] > 0.05
        || strpos($checkQuantityProduct['description'],'tikslus')
        || strpos($checkQuantityProduct['description'],'pak'))
        {
            $milimeters = $minMilimeters;
        }
        else
        {
            $milimeters = $maxMilimeters;
        }

        $quantity = round($minMilimeters * $checkMetersProduct['rows'] 
        / $checkMetersProduct['sheet_length'],0);
        $quantityLeft = $checkMetersProduct['quantity'] - $quantity;

        $rows = floor($maxWidth/$checkMetersProduct['sheet_width']);
        $maxRows = $this->params()['maxRows'];
        $rows > $maxRows ? $maxRows : $rows;

        $productMeters = round($quantityLeft * $checkMetersProduct['sheet_length'] 
        /$maxRows/1000,0);

        return $productMeters>=$minMeters ? $milimeters : false;
    }

    public function maxWidthPair($minWidth,$minMeters,$productWidth,$index,$products)
    {
        $searchProduct = $products[$index];
        unset($products[$index]);
        
        $maxSumArr = ['maxSum' => 0];
        $maxRows = $this->params()['maxRows'];
        $maxWidth = $this->params()['maxWidth'];
        $rows = floor($maxWidth/$productWidth);
        if($rows>$maxRows){
            $rows = $maxRows;
        }
        foreach ($products as $key => $product) {
        
            for ($i=1; $i <= $rows; ++$i) {
                $width_left = $maxWidth - $i * $productWidth;
                $rows2 = floor($width_left/$product['sheet_width']);
                if($rows2>$maxRows){
                    $rows2 = $maxRows;
                } 
                    for ($j=1; $j <= $rows2 ; ++$j) 
                    {
                        $widthSum =  $productWidth * $i + $product['sheet_width'] * $j;
                        if($widthSum >= $minWidth 
                        && $widthSum <= $maxWidth 
                        && $widthSum > $maxSumArr['maxSum']
                        && $milimeters = $this->checkMetersQuantity($maxWidth,$minMeters,$searchProduct,$products[$key],$i,$j))
                        {
                            $maxSumArr = 
                            [
                                'maxSum' => $widthSum,
                                'milimeters' => $milimeters,
                                'pairIndex' => $key,
                                'rows1' => $i,
                                'rows2' => $j
                            ];
                        }  
                    }
            }  
        }
        return ($maxSumArr['maxSum'] ) ? $maxSumArr : false;
    }

    public function isSingle($sheetWidth)
    {
        $maxRows = $this->params()['maxRows'];
        $maxWidth = $this->params()['maxWidth'];
        $minWidth = $this->params()['minWidth'];
        for ($i=1; $i <= $maxRows ; ++$i) { 
            if($sheetWidth * $i >= $minWidth && $sheetWidth * $i <= $maxWidth){
                return true;
            }  
        }
        return false;  
    }

    public function calculatorSingle($productsArray, $maxWidth,  Request $request, Board $board)
    {
        $pairs = [];
        foreach ($productsArray as $board => &$marks) 
        {
            foreach ($marks as $mark => &$products) 
            {
                foreach ($products as $key => &$product) 
                {
                    // dd($searchProduct);
                    $maxRows = $this->params()['maxRows'];
                    $productWidth = $product['sheet_width'];
                    $productLength = $product['sheet_length'];
                    $rows = floor($maxWidth/$productWidth);
                    $rows > $maxRows ? $maxRows : $rows;
                    
                    $milimeters = $productLength * $product['quantity'] / $rows;
                    $productQuantity = $milimeters * $rows / $productLength;
                    $wasteM2 = $milimeters * (2500 - $rows * $productWidth)/1000000;

                    $product['quantity'] -= $productQuantity;

                    $product = 
                    [
                        'code' => $product['code'],
                        'description' => $product['description'],
                        'rows' => $rows,
                        'sheet_width' => $product['sheet_width'],
                        'sheet_length' => $product['sheet_length'],
                        'quantity' => round($productQuantity,0),
                        'dates' => $product['dates'],
                        'order_id' => $product['order_id']
                    ];
                    $pairs[$board][$mark][] = 
                    [
                        'waste' => round($wasteM2,2),
                        'meters' => round($milimeters/1000,0),
                        'product' => $product
                    ];
                    unset($products[$key]); 
                } 
            }  
        }
        return [$productsArray,$pairs];
    }

    public function calculator($productsArray, $minWidth, $minMeters, Request $request, Board $board)
    {
        $pairs = [];
        $minMeters2 = $this->params()['minMeters'];
        // dd($productsArray);
        foreach ($productsArray as $board => &$marks) 
        {
            foreach ($marks as $mark => &$products) 
            {
                foreach ($products as $key => &$searchProduct) 
                {
                    // dd($searchProduct);
                    $searchProductWidth = $searchProduct['sheet_width'];
                    if($this->isSingle($searchProductWidth)){
                        continue;
                    }
    
                    while (isset($products[$key]) 
                    && $maxWidthArr = $this->maxWidthPair($minWidth,$minMeters,$searchProductWidth,$key,$products)) 
                    {

                        $pairedIndex = $maxWidthArr['pairIndex'];
                        $pairedProduct = $products[$pairedIndex];
                        $milimeters = $maxWidthArr['milimeters'];
                        
                        if($milimeters/1000 < $minMeters2){
                            $this -> calculator($productsArray, $minWidth, $minMeters2, $request, $board);
                        }
                        
                        $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
                        / $searchProduct['sheet_length'],0);
                        $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
                        / $pairedProduct['sheet_length'],0);

                        $searchProduct['quantity'] -= $searchProductQuantity;
                        $products[$pairedIndex]['quantity'] -= $pairedProductQuantity;
                        
                        
                        // if(!$products[$pairedIndex]['quantity']
                        //     && $searchProduct['quantity']/$searchProductOrderQuantity<0.05)
                        // {
                        //     $milimeters+=$searchProduct['sheet_length']
                        //     * $searchProduct['quantity']/$maxWidthArr['rows1'];
                        //     $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] / $searchProduct['sheet_length'],0);
                        //     $searchProduct['quantity'] = 0;
                        // }

                        
                        
                        $wasteM2 = $milimeters * (2500-$maxWidthArr['maxSum'])/1000000;   
                            
                        $product1 = 
                        [
                            'code' => $searchProduct['code'],
                            'description' => $searchProduct['description'],
                            'rows' => $maxWidthArr['rows1'],
                            'sheet_width' => $searchProduct['sheet_width'],
                            'sheet_length' => $searchProduct['sheet_length'],
                            'quantity' => $searchProductQuantity,
                            'dates' => $searchProduct['dates'],
                            'order_id' => $searchProduct['order_id']
                        ];
                        
                        $product2 = 
                        [
                            'code' => $pairedProduct['code'],
                            'description' => $pairedProduct['description'],
                            'rows' => $maxWidthArr['rows2'],
                            'sheet_width' => $pairedProduct['sheet_width'],
                            'sheet_length' => $pairedProduct['sheet_length'],
                            'quantity' => $pairedProductQuantity,
                            'dates' => $pairedProduct['dates'],
                            'order_id' => $pairedProduct['order_id']
                        ];
                        if($product1['rows'] * $product1['sheet_width']<$product2['rows'] * $product2['sheet_width']){
                            list($product1,$product2) = [$product2,$product1];
                        }
                        
                        $pairs[$board][$mark][] = 
                        [
                            'waste' => round($wasteM2,2),
                            'meters' => round($milimeters/1000,0),
                            'product1' => $product1,
                            'product2' => $product2
                        ];
                       
                        // dd($pairs[$key1]);
                        if($searchProduct['quantity']<=0){
                            unset($products[$key]);
                            if($products[$pairedIndex]['quantity']<=0){
                                unset($products[$pairedIndex]);
                            }
                        }
                        else{
                            unset($products[$pairedIndex]);
                        }     
                    } 
                }  
            }
        }
            // dd($test);
            return [$productsArray,$pairs];
    }
    
    public function wasteSum($array)
    {
        // dd($array);
        $wasteSum = 0;
        $wasteSumArr = [];

        foreach ($array as $board => $marks) {
            foreach ($marks as $mark =>$products) {
                foreach ($products as $product) {
                    foreach ($product as $key2 => $value) {
                        if($key2 == 'waste'){
                            $wasteSum += $product['waste']; 
                        }
                        else{
                            break;
                        }
                    }  
                }
            $wasteSumArr[$board][$mark] = $wasteSum;
            $wasteSum = 0;
            }
        }
        
        return $wasteSumArr;
    }

    public function quantityTest($array)
    {
        $result = [];
        foreach ($array as $board) 
        {
            foreach ($board as $mark)
            {
                foreach($mark as $pair)
                {
                    foreach ($pair as $key => $value) 
                    {
                        if($key=='product1'||$key=='product2'||$key=='product')
                        {
                            foreach($value as $key2 => $product)
                            {
                                if($key2=='code'){
                                    // dd($key2);
                                    if(!isset($result[$product]))
                                    {
                                        $result[$product] = 0;
                                    }
                                    $result[$product]+=$value['quantity'];
                                }
                                else
                                {
                                    break;
                                }
                                
                            }  
                        }
                    }
                }   
            }
        }
        
        ksort($result);
        return $result;
    }

    public function sheetWidthTest($array,$sheet_width)
    {
        $result = [];
        foreach ($array as $board) 
        {
            foreach ($board as $mark)
            {
                foreach($mark as $pair)
                {
                    foreach ($pair as $key => $value) 
                    {
                        if($key=='product1'||$key=='product2'||$key=='product')
                        {
                            foreach($value as $key2 => $product)
                            {
                                // dd($value['sheet_width']);
                                if($key2 == 'sheet_width' && $value['sheet_width'] == $sheet_width){
                                    
                                    $result[]=$pair;
                                }
                                
                                
                            }  
                        }
                    }
                }   
            }
        }

        return $result;
    }

    public function store(Request $request, Board $board)
    {
        $widerThan820 = $this->getArrays('widerThan820',$request,$board);
        // dd($widerThan820);
        $lessThan821 = $this->getArrays('lessThan821',$request,$board);
        $singles = $this->getArrays('singles',$request,$board);
        $all = [$widerThan820,$lessThan821,$singles];
        $pairs = [];
        for ($i=0; $i < 3; $i++) {
            $i == 2 ? $minWidth = 2140 : $minWidth = 2330;
            if($i){
                // dd('taip',$i,$remainingProducts);
                $merge = array_merge_recursive($remainingProducts,$all[$i]);
                $result = $this->calculator($merge,$minWidth,0,$request,$board);
                $pairs = array_merge_recursive($pairs,$result[1]);
            }
            else{
                $result = $this->calculator($all[$i],$minWidth,0,$request,$board);
                $pairs = $result[1];
            }
            $remainingProducts = $result[0];
            // dd($remainingProducts);
        }

        if(count($remainingProducts)){
            $singleProducts = $this->calculatorSingle($remainingProducts,2460,$request,$board);
            $pairs = array_merge_recursive($pairs,$singleProducts[1]);
            $remainingProducts = $singleProducts[0];
        }
        $wasteSumArr = $this->wasteSum($pairs);
        
        $result = $this->quantityTest($pairs);
        $result2 = $this->sheetWidthTest($pairs,600);
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
