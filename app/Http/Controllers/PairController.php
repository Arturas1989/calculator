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
use Response;

class PairController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function marks()
    {
        return Mark::all();
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
    

    public function params()
    {
        return 
        [
            'quantityRatio' => 0,
            'largeWasteRatio' => 0.12,
            'minMeters' => 70,
            'maxWidth' => 2460,
            'minWidth' => 2330,
            'maxRows' => 8
        ];
    }

    public function gradeNum($mark)
    {
        $pos = strpos($mark,'R') ? strpos($mark,'R') : strpos($mark,'W');
        return intval(substr($mark,$pos-2,2));
    }

    
    public function getProductsList($from, $to, $from2, $to2, $request, Board $board)
    {
        // dd($request);
        $productsList = [];
        isset($request->boards) ? $boards = $request->boards : 
        $boards = [Board::where('board_name','BC')->get()->first()->id];
        
            foreach ($boards as $board_id) 
            {
                $board = Board::find($board_id);
                $board_name = $board->board_name;
                $marks = $board->marks()->get()->sort(function ($a, $b){
                    return $this->gradeNum($b->mark_name) <=> $this->gradeNum($a->mark_name);
                });
                
                foreach ($marks as $mark) 
                {
                    $mark_name = $mark->mark_name;
                    if(!isset($request->boards) && $mark_name!='BC25R' && $mark_name!='BC24R'){
                        continue;
                    }
                    
                    $mark_id = $mark->id;
                    if($from2 && $to2)
                    {
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
                    else if($from && $to)
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
                    else
                    {
                        return [];
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

    public function marksJoin(Request $request)
    {
        $marksJoin = [];
        if(isset($request->marks_origin))
        {
            $marksJoin['marks_origin'] = $request->marks_origin;
            $marksJoin['marks_join'] = $request->marks_join;
        }
        return $marksJoin;
    }

    public function dates(Request $request)
    {
        $from = $request->manufactury_date_from;
        $to = $request->manufactury_date_till;
        $from2 = null;
        $to2 = null;
        $from3 = null;
        $to3 = null;
        $from4 = null;
        $to4 = null;

        if(isset($request->load_date_from) && isset($request->load_date_till))
        {
            $from2 = $request->load_date_from;
            $to2 = $request->load_date_till;
        }
        if(isset($request->future_manufactury_date_from) && isset($request->future_manufactury_date_till))
        {
            $from3 = $request->future_manufactury_date_from;
            $to3 = $request->future_manufactury_date_till;
        }
        if(isset($request->future_load_date_from) && isset($request->future_load_date_till))
        {
            $from4 = $request->future_load_date_from;
            $to4 = $request->future_load_date_till;
        }
        return 
        [
            'manufactury_date_from' => $from,
            'manufactury_date_till' => $to,
            'load_date_from' => $from2,
            'load_date_till' => $to2,
            'future_manufactury_date_from' => $from3,
            'future_manufactury_date_till' => $to3,
            'future_load_date_from' => $from4,
            'future_load_date_till' => $to4,
        ];

    }

    public function getArrays($widthType, Request $request, Board $board)
    {
        $from = $this->dates($request)['manufactury_date_from'];
        $to = $this->dates($request)['manufactury_date_till'];
        $from2 = $this->dates($request)['load_date_from'];
        $to2 = $this->dates($request)['load_date_till'];
        $from3 = $this->dates($request)['future_manufactury_date_from'];
        $to3 = $this->dates($request)['future_manufactury_date_till'];
        $from4 = $this->dates($request)['future_load_date_from'];
        $to4 = $this->dates($request)['future_load_date_till'];


        $productsList = $this->getProductsList($from, $to, $from2, $to2, $request, $board);
        $futureProductsList = $this->getProductsList($from3, $to3, $from4, $to4, $request, $board);
        // dd($futureProductsList);
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
                    case 'exceptSingles':
                        $array[$board][$mark] = array_filter($markProducts, function($el) {
                            return !$this->isSingle($el['sheet_width']);
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
        $quantityRatio = $this->params()['quantityRatio'];

        if($additionalQuantity / $checkQuantityProduct['totalQuantity'] > $quantityRatio
        || strpos($checkQuantityProduct['description'],'tikslus'))
        {
            $milimeters = $minMilimeters;
        }
        else
        {
            return $maxMilimeters;
        }

        $quantity = round($milimeters * $checkMetersProduct['rows'] 
        / $checkMetersProduct['sheet_length'],0);
        $quantityLeft = $checkMetersProduct['quantity'] - $quantity;

        if($checkMetersProduct['sheet_width'] > $maxWidth) $maxWidth = 2500;

        $rows = floor($maxWidth/$checkMetersProduct['sheet_width']);
        $maxRows = $this->params()['maxRows'];
        if($rows > $maxRows) $rows = $maxRows;

        $productMeters = round($quantityLeft * $checkMetersProduct['sheet_length'] 
        /$rows/1000,0);

        return $productMeters>=$minMeters ? $milimeters : false;
    }

    public function isLargerWidth($width1,$width2,$maxWidth,$maxRows,$maxWidthSum)
    {
        $rows1 = floor($maxWidth/$width1);
        if($rows1>$maxRows) $rows1 = $maxRows;
        $rows2 = floor($maxWidth/$width2);
        if($rows2>$maxRows) $rows2 = $maxRows;
        $maxWidth1 = $rows1 * $width1;
        $maxWidth2 = $rows2 * $width2;
        return ($maxWidth1+$maxWidth2)/2 >= $maxWidthSum;
    }

    public function maxWidthPair($minWidth,$minMeters,$productWidth,$index,$products,$futureProducts = null)
    {
        $searchProduct = $products[$index];
        if(!$futureProducts)
        {
            unset($products[$index]);
        }
        else
        {
            $products = $futureProducts;
        }
        
        $maxSumArr = ['maxSum' => 0];
        $maxRows = $this->params()['maxRows'];

        $maxWidth = $this->params()['maxWidth'];
        if($productWidth > $maxWidth){
            $maxWidth = 2500;
        }
        $rows = floor($maxWidth/$productWidth);
        if($rows>$maxRows){
            $rows = $maxRows;
        }
        
        foreach ($products as $key => $product) {
        
            for ($i=1; $i <= $rows; ++$i) {
                $width_left = $maxWidth - $i * $productWidth;
                $rows2 = floor($width_left/$product['sheet_width']);
                if($i + $rows2 > $maxRows){
                    $rows2 = $maxRows - $i;
                } 
                    for ($j=1; $j <= $rows2 ; ++$j) 
                    {
                        $widthSum =  $productWidth * $i + $product['sheet_width'] * $j;
                        if($this->isLargerWidth($productWidth,$product['sheet_width'],$maxWidth,$maxRows,$widthSum)) continue;

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
        $singles = [];
        foreach ($productsArray as $board => &$marks) 
        {
            foreach ($marks as $mark => &$products) 
            {
                $wasteSum = 0;
                foreach ($products as $key => &$product) 
                {
                    // dd($searchProduct);
                    
                    $maxRows = $this->params()['maxRows'];
                    $productWidth = $product['sheet_width'];
                    $productLength = $product['sheet_length'];

                    if($productWidth > $maxWidth) $maxWidth = 2500;
                    
                    $rows = floor($maxWidth/$productWidth);
                    $rows > $maxRows ? $maxRows : $rows;
                    
                    $milimeters = $productLength * $product['quantity'] / $rows;
                    $productQuantity = $milimeters * $rows / $productLength;

                    $widthLeft = 2500 - $rows * $productWidth;
                    $wasteRatio = round($widthLeft/2500,2);
                    
                    $wasteM2 = $milimeters * $widthLeft/1000000;

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

                    $waste = round($wasteM2,2);

                        $singles[$board][$mark][] = 
                        [
                            'waste' => $waste,
                            'meters' => round($milimeters/1000,0),
                            'product' => $product
                        ];
                    
                    unset($products[$key]); 
                }
            }  
        }
        // dd($singles);
        return $singles;
    }

    public function pairing($searchProduct,$products,$minWidth,
    $minMeters,$searchProductWidth,$key,$pairs,$boards,$mark,$minMetersParam)
    {
        while (isset($products[$key]) 
        && $maxWidthArr = $this->maxWidthPair($minWidth,$minMeters,$searchProductWidth,$key,$products)) 
        {
            $pairedIndex = $maxWidthArr['pairIndex'];
            $pairedProduct = $products[$pairedIndex];
            $milimeters = $maxWidthArr['milimeters'];

            if($milimeters/1000 < $minMetersParam){
                return false;
            }
            
            
            $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
            / $searchProduct['sheet_length'],0);
            $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
            / $pairedProduct['sheet_length'],0);

            $products[$key]['quantity'] -= $searchProductQuantity;
            $products[$pairedIndex]['quantity'] -= $pairedProductQuantity;
            
            // if($searchProductWidth == 1700){
            //     dd($products);
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
            
            $pairs[$boards][$mark][] = 
            [
                'waste' => round($wasteM2,2),
                'meters' => round($milimeters/1000,0),
                'product1' => $product1,
                'product2' => $product2
            ];
            // dd($pairs[$key1]);
            if($products[$key]['quantity']<=0){
                unset($products[$key]);
                if($products[$pairedIndex]['quantity']<=0){
                    unset($products[$pairedIndex]);
                }
            }
            else{
                unset($products[$pairedIndex]);
            }     
        }
        
        return 
        [
            'pairs' => $pairs,
            'products' =>$products
        ];
    }

    public function calculator($productsArray, $minWidth, $minMeters, 
    Request $request, Board $board, $isLast = false, $joinList=[])
    {
        $from = $this->dates($request)['manufactury_date_from'];
        $to = $this->dates($request)['manufactury_date_till'];
        $from2 = $this->dates($request)['load_date_from'];
        $to2 = $this->dates($request)['load_date_till'];
        $from3 = $this->dates($request)['future_manufactury_date_from'];
        $to3 = $this->dates($request)['future_manufactury_date_till'];
        $from4 = $this->dates($request)['future_load_date_from'];
        $to4 = $this->dates($request)['future_load_date_till'];


        $productsList = $this->getProductsList($from, $to, $from2, $to2, $request, $board);
        $futureProducts= $this->getProductsList($from3, $to3, $from4, $to4, $request, $board);
        $joinMark = '';

        // dd($allProducts);
        $pairs = [];
        $productsCopy = $productsArray;

        $minMetersParam = $this->params()['minMeters'];
        $maxWidthParam = $this->params()['maxWidth'];
        $maxRowsParam = $this->params()['maxRows'];
        $largeWasteParam = $this->params()['largeWasteRatio'];
        // dd($productsArray);
        foreach ($productsArray as $boards => &$marks) 
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
                    
                    $pairsProducts = $this->pairing($searchProduct,$products,$minWidth,$minMeters,
                    $searchProductWidth,$key,$pairs,$boards,$mark,$minMetersParam);
                    if(!$pairsProducts) return false;
                    $pairs = $pairsProducts['pairs'];
                    $products = $pairsProducts['products'];
                    

                    if($isLast && isset($products[$key]) && isset($futureProducts[$boards][$mark]))
                    {
                        $futureProductsList = &$futureProducts[$boards][$mark];
                        while (isset($products[$key]) 
                        && $maxWidthArr = $this->maxWidthPair($minWidth,$minMeters,$searchProductWidth,$key,$products,$futureProductsList)) 
                        {
                            $pairedIndex = $maxWidthArr['pairIndex'];
                            $pairedProduct = $futureProductsList[$pairedIndex];
                            $milimeters = $maxWidthArr['milimeters'];
                        
                            
                            if($milimeters/1000 < $minMetersParam){
                                return false;
                            }
                            
                            $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
                            / $searchProduct['sheet_length'],0);
                            $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
                            / $pairedProduct['sheet_length'],0);

                            $searchProduct['quantity'] -= $searchProductQuantity;
                            $futureProductsList[$pairedIndex]['quantity'] -= $pairedProductQuantity;

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
                            
                            $pairs[$boards][$mark][] = 
                            [
                                'waste' => round($wasteM2,2),
                                'meters' => round($milimeters/1000,0),
                                'product1' => $product1,
                                'product2' => $product2
                            ];
                        
                            // dd($pairs[$key1]);
                            if($searchProduct['quantity']<=0){
                                unset($products[$key]);
                                if($futureProductsList[$pairedIndex]['quantity']<=0){
                                    unset($futureProductsList[$pairedIndex]);
                                }
                            }
                            else{
                                unset($futureProductsList[$pairedIndex]);
                            }           
                        }
                    }
                    

                    if($isLast && isset($products[$key]) && count($joinList))
                    {
                        
                        $singleRows = floor($maxWidthParam/$searchProductWidth);
                        if($singleRows > $maxRowsParam)  $singleRows = $maxRowsParam;
                        
                        $isLargeWaste = 1 - $singleRows * $searchProductWidth / 2500 >= $largeWasteParam;
                        
                        
                        if($isLargeWaste)
                        {
                            $joinProductsList = &$joinList;
                            $boardName = array_key_first($joinProductsList[0]);
                            $joinMark = array_key_first($joinProductsList[0][$boardName]);
                            while (isset($products[$key]) 
                            && $maxWidthArr = $this->maxWidthJoin($minWidth,$minMeters,$searchProductWidth,$products[$key],$joinList)) 
                            {
                                // dd($maxWidthArr);
                                $index1 = $maxWidthArr['key'];
                                $board = $maxWidthArr['board'];
                                $index2 = $maxWidthArr['index'];

                                $pairedProduct = $joinProductsList[$index1][$board][$joinMark][$index2];
                                $milimeters = $maxWidthArr['milimeters'];
                            
                                
                                if($milimeters/1000 < $minMetersParam){
                                    return false;
                                }
                                
                                $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
                                / $searchProduct['sheet_length'],0);
                                $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
                                / $pairedProduct['sheet_length'],0);
    
                                $searchProduct['quantity'] -= $searchProductQuantity;
                                $joinProductsList[$index1][$board][$joinMark][$index2]['quantity'] -= $pairedProductQuantity;
    
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
                                if($product1['rows'] * $product1['sheet_width'] < $product2['rows'] * $product2['sheet_width']){
                                    list($product1,$product2) = [$product2,$product1];
                                }
                                
                                $pairs[$boards][$mark][] = 
                                [
                                    'waste' => round($wasteM2,2),
                                    'meters' => round($milimeters/1000,0),
                                    'product1' => $product1,
                                    'product2' => $product2
                                ];
                            
                                // dd($pairs[$key1]);
                                if($searchProduct['quantity']<=0){
                                    unset($products[$key]);
                                    if($joinProductsList[$index1][$board][$joinMark][$index2]['quantity']<=0){
                                        unset($joinProductsList[$index1][$board][$joinMark][$index2]);
                                    }
                                }
                                else{
                                    unset($joinProductsList[$index1][$board][$joinMark][$index2]);
                                }           
                            }
                        }
                        
                    } 
                }  
            }
        }
            // dd($pairs);
            return 
            [
                'remaining_products' => $productsArray,
                'pairs' => $pairs,
                'joinList' => $joinList,
                'joinMark' => $joinMark
            ];
    }

    public function maxWidthJoin($minWidth,$minMeters,$productWidth,$searchProduct,$joinList)
    {
        $maxSumArr = ['rows1' => 0, 'maxSum' => 0];
        $maxRows = $this->params()['maxRows'];

        $maxWidth = $this->params()['maxWidth'];
        if($productWidth > $maxWidth){
            $maxWidth = 2500;
        }
        $rows = floor($maxWidth/$productWidth);
        if($rows>$maxRows){
            $rows = $maxRows;
        }
        
        foreach ($joinList as $key => $boards) 
        {
            foreach ($boards as $board => $marks) 
            {
                foreach ($marks as $mark => $products) 
                {
                    foreach ($products as $index => $product) 
                    {
                        for ($i=$rows; $i >= 1; --$i) 
                        {
                            $width_left = $maxWidth - $i * $productWidth;
                            $rows2 = floor($width_left/$product['sheet_width']);
                            if($rows2==0) continue;
                            if($i + $rows2 > $maxRows){
                                $rows2 = $maxRows - $i;
                            }

                            $widthSum =  $productWidth * $i + $product['sheet_width'] * $rows2;
                            if($this->isLargerWidth($productWidth,$product['sheet_width'],$maxWidth,$maxRows,$widthSum)) continue;
    
                            
                            if($widthSum >= $minWidth 
                            && $widthSum <= $maxWidth
                            && ($i > $maxSumArr['rows1'] || ($i == $maxSumArr['rows1'] && $widthSum > $maxSumArr['maxSum']))
                            && $milimeters = $this->checkMetersQuantity($maxWidth,$minMeters,$searchProduct,$products[$index],$i,$rows2))
                            {
                                $maxSumArr =  
                                [
                                    'maxSum' => $widthSum,
                                    'milimeters' => $milimeters,
                                    'key' => $key,
                                    'board' => $board,
                                    'mark' => $mark,
                                    'index' => $index,
                                    'rows1' => $i,
                                    'rows2' => $rows2
                                ];
                            }  
                        }
                    }
                }      
            }
        }

        return ($maxSumArr['maxSum'] ) ? $maxSumArr : false;
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
            $wasteSumArr[$board][$mark]['waste'] = $wasteSum;
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
                    if(is_array($pair))
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
                    if(is_array($pair))
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
        }

        return $result;
    }

    public function calculationMethod($productList, $minMeters, 
    Request $request, Board $board, $isLast=false, $joinList=[])
    {

        $length = count($productList);
        $pairs = [];
        $joinMark = '';

        if (!$length) return 
        [
            'pairs' => $pairs,
            'joinList' => $joinList,
            'joinMark' => $joinMark
        ];;

        
        $maxWidth = $this->params()['maxWidth'];
        $minMetersParam = $this->params()['minMeters'];
        $isLast = false;

        for ($i=0; $i < $length; $i++) 
        {
            if($i == $length - 1)
            {
                $minWidth = 2140;
                $isLast = true;
            }
            else
            {
                $minWidth = $this->params()['minWidth'];
            }
            if($i){
                $merge = array_merge_recursive($remainingProducts,$productList[$i]);
                $result = $this->calculator($merge,$minWidth,$minMeters,$request,$board,$isLast,$joinList);
                if(!$result) return $this->calculationMethod($productList,$minMetersParam,$request,$board,$isLast,$joinList);
                
                $pairs = array_merge_recursive($pairs,$result['pairs']);
            } 
            else{
                $result = $this->calculator($productList[$i],$minWidth,$minMeters,$request,$board,$isLast,$joinList);
                if(!$result) return $this->calculationMethod($productList,$minMetersParam,$request,$board,$isLast,$joinList);
                $pairs = $result['pairs'];
                // dd($pairs);
            }
            $remainingProducts = $result['remaining_products'];
            // dd($result);
        }

        $joinList = $result['joinList'];
        $joinMark = $result['joinMark'];
        if(count($remainingProducts))
        {
            $singleProducts = $this->calculatorSingle($remainingProducts,$maxWidth,$request,$board);
            $pairs = array_merge_recursive($pairs,$singleProducts);
        }
        $wasteSumArr = $this->wasteSum($pairs);
        $pairs = array_merge_recursive($pairs,$wasteSumArr);
        return 
        [
            'pairs' => $pairs,
            'joinList' => $joinList,
            'joinMark' => $joinMark
        ];
        
    }

    // public function passOneByOne ($productArray, $count, $request, $board)
    // {
    //     $productList = [];
    //     $result = [];

    //     foreach ($productArray[0] as $key => $marks) 
    //     {
    //         foreach ($marks as $mark => $products) 
    //         {
    //             for ($i=0; $i < $count; ++$i) 
    //             { 
    //                 $productList[$i][$key][$mark] = $productArray[$i][$key][$mark];
    //             }
    //             $result = array_merge_recursive($result,$this->calculationMethod($productList, 0, $request, $board));
    //             $productList = [];
    //         }
    //     }
    //     return $result;
    // }

    public function getSeperateList($list,Request $request)
    {
        $seperateProductsList = [];
        $joinProductsList = [];
        $allowedJoins = [];
        if(count($this->marksJoin($request)))
        {
            $marks_origin = $this->marksJoin($request)['marks_origin'];
            $marks_join = $this->marksJoin($request)['marks_join'];
        }
        else
        {
            return
            [
                'joinProductsList' => $joinProductsList,
                'seperateProductList' => $list 
            ];
        }
        // dd($marks_origin);
        foreach ($list as $key => $productList) 
        {
            foreach ($productList as $board => $marks) 
            {
                foreach ($marks as $mark => $products) 
                { 
                    $mark_key = array_search($mark,$marks_origin);
                    if($mark_key !== false)
                    {
                        $join_mark = $marks_join[$mark_key];
                        $joinProductsList[$mark]['origin'][$key][$board][$mark]=$productList[$board][$mark];
                        $joinProductsList[$mark]['join'][$key][$board][$join_mark]=$productList[$board][$join_mark];
                    } 
                    else
                    {
                        $seperateProductsList[$key][$board][$mark] = $productList[$board][$mark]; 
                    }
                }
            }
        }

        // dd($joinProductsList);
        return
        [
            'joinProductsList' => $joinProductsList,
            'seperateProductList' => $seperateProductsList
        ];
    }

    public function calculationJoin(Request $request, $productList,$marks_origin,$marks_join, $board)
    {
        // dd($productList);
        $isLast = false;
        $pairs = [];
        $productListCopy = $productList;
        foreach ($productList as $mark => &$joinList) 
        {
            foreach ($joinList as $joinType => $productArray) 
            {   
                // dd($joinList['join']);
                
                $result = $this->calculationMethod($joinList['origin'], 0 , $request , $board, $isLast, $joinList['join']);
                $pairs = array_merge_recursive($pairs,$result['pairs']);
                $joinProductList = $result['joinList'];
                $joinMark = $result['joinMark'];
                $mark_key = array_search($joinMark,$marks_origin);
                if($mark_key!==false)
                {
                    $productList[$joinMark]['origin'] = $joinProductList;
                }
                else
                {
                    $result = $this->calculationMethod($joinProductList, 0 , $request , $board, $isLast, []);
                    $pairs = array_merge_recursive($pairs,$result['pairs']);
                }
                break;
            }
        }

        $reversedList = array_reverse($productListCopy);
        $pairs2 = [];
        foreach ($reversedList as $mark => &$joinList) 
        {
            foreach ($joinList as $joinType => $productArray) 
            {   
                // dd($joinList);
                
                $result = $this->calculationMethod($joinList['join'], 0 , $request , $board, $isLast, $joinList['origin']);
                $pairs2 = array_merge_recursive($pairs2,$result['pairs']);
                $originProductList = $result['joinList'];
                // dd($result);
                $joinMark = $result['joinMark'];
                $mark_key = array_search($joinMark,$marks_join);
                if($mark_key!==false)
                {
                    $reversedList[$joinMark]['origin'] = $originProductList;
                }
                else
                {
                    $result = $this->calculationMethod($originProductList, 0 , $request , $board, $isLast, []);
                    // dd($reversedList,$result['pairs']);
                    $pairs2 = array_merge_recursive($pairs2,$result['pairs']);
                }
                break;
            }
        }
        
        dd($pairs2);
    }

    public function store(Request $request, Board $board)
    {
        $from = $this->dates($request)['manufactury_date_from'];
        $to = $this->dates($request)['manufactury_date_till'];
        $from2 = $this->dates($request)['load_date_from'];
        $to2 = $this->dates($request)['load_date_till'];

        $widerThan820 = $this->getArrays('widerThan820', $request, $board);
        $lessThan821 = $this->getArrays('lessThan821', $request, $board);
        $singles = $this->getArrays('singles', $request, $board);
        $exceptSingles = $this->getArrays('exceptSingles', $request, $board);
        $products1 = [$widerThan820, $lessThan821, $singles];
        $products2 = [$exceptSingles, $singles];
        $products3 = [$this->getProductsList($from, $to, $from2, $to2, $request, $board)];

        $exceptJoinProducts1 = $this->getSeperateList($products1,$request)['seperateProductList'];
        // dd($productList1);
        $exceptJoinProducts2 = $this->getSeperateList($products2,$request)['seperateProductList'];
        $exceptJoinProducts3 = $this->getSeperateList($products3,$request)['seperateProductList'];

        if(isset($request->marks_origin))
        {
            $marks_origin = $request->marks_origin;
            $marks_join = $request->marks_join;
            $joinProducts1 = $this->getSeperateList($products1,$request)['joinProductsList'];
            $this -> calculationJoin($request, $joinProducts1, $marks_origin,$marks_join, $board);
        }

        $result1 = $this->calculationMethod($exceptJoinProducts1, 0 , $request , $board);
        $result2 = $this->calculationMethod($exceptJoinProducts2, 0 , $request , $board);
        $result3 = $this->calculationMethod($exceptJoinProducts3, 0 , $request , $board);
        // dd($result3);

        $resultArr = [$result1, $result2, $result3];
        $result = [];
            foreach ($resultArr as $key =>$array) 
            {
                // dd($all);
                foreach ($array as $board => $marks) 
                {
                    // dd($array);
                    foreach ($marks as $mark => $pairs) 
                    {
                        // dd($pairs);
                        
                        if(!isset($result[$board][$mark]['waste']))
                        {
                            $result[$board][$mark] = $pairs;
                            $result[$board][$mark]['waste'] = $pairs['waste'];
                            // dd($result[$board][$mark]['waste']);
                        }
                        else if($pairs['waste'] < $result[$board][$mark]['waste'])
                        {
                            // dd($array[1]);
                            $result[$board][$mark] = $pairs;
                            $result[$board][$mark]['waste'] = $pairs['waste'];
                        }
                    }   
                }
            
            
        }
        
        $quantity = $this->quantityTest($result);
        
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
