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
        return view('pair.create',['boards' => $this->allBoards(),'marks' => $this->marks()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    

    //skaičiuoklės parametrai
    public function params()
    {
        return 
        [
            'description' => 'tikslus',
            'quantityMore' => 20,
            'quantityRatio' => 0.05,
            'largeWasteRatio' => 0.12,
            'minMeters' => 70,
            'possibleMaxWidths' => [2500, 2300, 2100],
            'minusfromMaxWidth' => 40,
            'maxWasteRatio' => 0.068,
            'absoluteMaxWasteRatio' => 0.144,
            'maxRows' => 8
        ];
    }

    //profilio numeris iš markės
    public function gradeNum($mark)
    {
        $pos = strpos($mark,'R') ? strpos($mark,'R') : strpos($mark,'W');
        return intval(substr($mark,$pos-2,2));
    }

    //DUOMENU FUNKCIJOS
    //-----------------

    public function getProductsList($from, $to, $from2, $to2, $request, Board $board)
    {
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
                            'quantityLeft' => $order->quantity,
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

    public function filterByProductWidth($markProducts, $mark, $board, $widthType, $possibleWidths)
    {
        $productList = [];

        switch ($widthType){
            case 'widerThan820':
            $productList[$board][$mark] = array_filter($markProducts,function($product) use ($possibleWidths){
                return $product['sheet_width'] > 820 && !$this->isSingle($product['sheet_width'], $possibleWidths);
            });
            break;

            case 'lessThan821':
            $productList[$board][$mark] = array_filter($markProducts,function($product) use ($possibleWidths){
                return $product['sheet_width'] < 821 && !$this->isSingle($product['sheet_width'], $possibleWidths);
            });
            break;

            case 'exceptSingles':
            $productList[$board][$mark] = array_filter($markProducts,function($product) use ($possibleWidths){
                return !$this->isSingle($product['sheet_width'], $possibleWidths);
            });
            break;

            case 'singles':
            $productList[$board][$mark] = array_filter($markProducts,function($product) use ($possibleWidths){
                return $this->isSingle($product['sheet_width'], $possibleWidths);
            });
            break;

            default:
            return [];

        }
        return $productList;
    }

    // public function checkMetersQuantity($maxWidth, $minMeters, $searchProduct, $pairedProduct, $rows1, $rows2)
    // {
        
    //     $searchProduct['rows'] = $rows1;
    //     $pairedProduct['rows'] = $rows2;

    //     $searchProductMilimeters = $searchProduct['sheet_length'] 
    //     * $searchProduct['quantity'] / $rows1; 

    //     $pairedProductMilimeters = $pairedProduct['sheet_length'] 
    //     * $pairedProduct['quantity']/$rows2;

    //     if($searchProductMilimeters <= $pairedProductMilimeters){
    //         $checkMetersProduct = $pairedProduct;
    //         $checkQuantityProduct = $searchProduct;
    //         $minMilimeters = $searchProductMilimeters;
    //         $maxMilimeters = $pairedProductMilimeters;
    //     }
    //     else{
    //         $checkMetersProduct = $searchProduct;
    //         $checkQuantityProduct = $pairedProduct;
    //         $minMilimeters = $pairedProductMilimeters;
    //         $maxMilimeters = $searchProductMilimeters;
    //     }
    //     $difMilimeters = $maxMilimeters - $minMilimeters;
    //     $additionalQuantity =  round($difMilimeters * $checkQuantityProduct['rows'] 
    //     / $checkQuantityProduct['sheet_length'],0);
    //     $quantityRatio = $this->params()['quantityRatio'];

    //     if($additionalQuantity / $checkQuantityProduct['totalQuantity'] > $quantityRatio
    //     || strpos($checkQuantityProduct['description'],'tikslus'))
    //     {
    //         $milimeters = $minMilimeters;
    //     }
    //     else
    //     {
    //         return $maxMilimeters;
    //     }

    //     $quantity = round($milimeters * $checkMetersProduct['rows'] 
    //     / $checkMetersProduct['sheet_length'],0);
    //     $quantityLeft = $checkMetersProduct['quantity'] - $quantity;

    //     if($checkMetersProduct['sheet_width'] > $maxWidth) $maxWidth = 2500;

    //     $rows = floor($maxWidth/$checkMetersProduct['sheet_width']);
    //     $maxRows = $this->params()['maxRows'];
    //     if($rows > $maxRows) $rows = $maxRows;

    //     $productMeters = round($quantityLeft * $checkMetersProduct['sheet_length'] 
    //     /$rows/1000,0);

    //     return $productMeters>=$minMeters ? $milimeters : false;
    // }

    public function checkMetersQuantity($pairedList, $minMeters, $possibleWidths)
    { 
        $pairedList = $this->calculatePairedMeters($pairedList);

        $minWidth = min($possibleWidths) - $this->params()['minusfromMaxWidth'];

        foreach ($pairedList as $product) 
        {
            if($product['quantityLeft'] == 0) continue;

            $singleRows = $this->minSingleRows($product['sheet_width'], $minWidth);
            $metersToCheck = $this->calculateMeters($product['quantityLeft'], $singleRows, $product['sheet_length']);
            if($metersToCheck < $minMeters){
                return false;
            } 
        }

        return $pairedList;
    }

    
    public function widthDif($width, $maxWidth, $widthSum, $pairedRows)
    {
        $maxRows = $this->params()['maxRows'];

        $singleRows = floor($maxWidth / $width);
        if($singleRows > $maxRows) $singleRows = $maxRows;

        $singleWidth = $singleRows * $width;
        $widthDif = $singleWidth - $widthSum;

        if($singleRows == 0){
            dd($maxWidth, $width);
        }

        return $widthDif / $singleRows * $pairedRows;
    }



    public function isWidthsEqual($pairedList, $widthInfo)
    {
        $maxWidth = $widthInfo['maxWidth'];
        $widthSum = $widthInfo['widthSum'];

        // dd($pairedList);
        $product1 = $pairedList['product1'];
        $widthDif1 = $this->widthDif($product1['sheet_width'], $maxWidth, $widthSum, $product1['rows']);

        $product2 = $pairedList['product2'];
        $widthDif2 = $this->widthDif($product2['sheet_width'], $maxWidth, $widthSum, $product2['rows']);

        
        $widthDifs = [];
        foreach ($pairedList as $product) {
            $widthDifs[] = $this->widthDif($product['sheet_width'], $maxWidth, $widthSum, $product['rows']);
        }

        return array_sum($widthDifs) == 0;
    }

    // check if quantityLeft doesnt exceed minimum requirements. If so add meters that it takes all quantity.
    public function correctMeters($productList, &$meters)
    {
        $params = $this->params();
        $adMetr = 0;
        foreach ($productList as $product)
        {
            if( strpos($product['description'], $params['description']) ) return;

            if ($product['quantityLeft'] <= $params['quantityMore'] 
                || $product['quantityLeft'] / $product['totalQuantity'] <= $params['quantityRatio']
                && !strpos($product['description'], $params['description'])
                && $product['quantityLeft'] != 0)
                {
                    $adMetr2 = $this->calculateMeters($product['quantityLeft'], $product['rows'], $product['sheet_length']);
                    if($adMetr2 > $adMetr) $adMetr = $adMetr2;
                }
            else{
                $adMetr = 0;
                break;
            }
        }
        $meters += $adMetr;
    }

    public function calculatePairedMeters($pairedList)
    {
        $product1 = &$pairedList['product1'];
        $product2 = &$pairedList['product2'];

        $meters1 = $this->calculateMeters($product1['quantityLeft'], $product1['rows'], $product1['sheet_length']);
        $meters2 = $this->calculateMeters($product2['quantityLeft'], $product2['rows'], $product2['sheet_length']);
        $productList = [];


        if(isset($pairedList['product3'])){
            $product3 = &$pairedList['product3'];
            $meters3 = $this->calculateMeters($product3['quantityLeft'], $product3['rows'], $product3['sheet_length']);
            $meters = min($meters1, $meters2, $meters3);
            $pairedQuantity3 = $this->calculateQuantity($meters, $product3['rows'], $product3['sheet_length']);
            $product3['quantityLeft'] -= $pairedQuantity3;
            $product3['pairedQuantity'] = $pairedQuantity3;
            if($product3['quantityLeft'] < 0) $product3['quantityLeft'] = 0; 
        }
        else{
            $meters = min($meters1, $meters2);
        }

        $pairedQuantity1 = $this->calculateQuantity($meters, $product1['rows'], $product1['sheet_length']);
        $product1['pairedQuantity'] = $pairedQuantity1;
        $product1['quantityLeft'] -= $pairedQuantity1;
        if($product1['quantityLeft'] < 0) $product1['quantityLeft'] = 0;

        $pairedQuantity2 = $this->calculateQuantity($meters, $product2['rows'], $product2['sheet_length']);
        $product2['pairedQuantity'] = $pairedQuantity2;
        $product2['quantityLeft'] -= $pairedQuantity2;
        if($product2['quantityLeft'] < 0) $product2['quantityLeft'] = 0;
        
        $this->correctMeters($pairedList, $meters);
        $pairedList['product1']['meters'] = $meters;

        return $pairedList;
    }

    public function calculateMeters($quantity, $rows, $sheet_length)
    {
        return ceil($quantity * $sheet_length / $rows / 1000);
    }

    public function calculateQuantity($meters, $rows, $sheet_length)
    {
        return round($meters * $rows * 1000 / $sheet_length, 0);
    }

    public function minSingleRows($product_width, $minWidth)
    {
        $maxRows = $this->params()['maxRows'];
        $singleRows = floor($minWidth / $product_width);
        return $singleRows > $maxRows ? $maxRows : $singleRows;
    }

    

    public function maxWidthPair2($searchProduct, $index, $products, $minMeters, $possibleWidths)
    {
        $maxSumArr = ['wasteRatio' => 1];
        $maxRowsSum = $this->params()['maxRows'];

        $minusFromWidth = $this->params()['minusfromMaxWidth'];
        $maxWasteRatio = $this->params()['maxWasteRatio'];
        $searchProductWidth = $searchProduct['sheet_width'];

        $result = [];
        foreach ($possibleWidths as $maximumWidth) 
        {
            $maxWidth = $maximumWidth - $minusFromWidth;

            $maxRows1 = floor($maxWidth/$searchProductWidth);
            if($maxRows1 === 0) continue;

            if($maxRows1 > $maxRowsSum){
                $maxRows1 = $maxRowsSum;
            }

            // remaining second products
            $pairProducts2 = $products;
            unset($pairProducts2[$index]);
            
            $searchProduct['index'] = $index;
            foreach ($pairProducts2 as $key2 => &$pairProduct2) 
            {

                for ($rows1 = 1; $rows1 <= $maxRows1; ++$rows1) 
                {
                    $remaining_width = $maxWidth - $rows1 * $searchProductWidth;

                    // calculating second product maximum rows
                    $maxRows2 = (int)floor($remaining_width / $pairProduct2['sheet_width']);
                    if($maxRows2 === 0) break;
                    // if($maxRows2 === 0){
                    //     dd($searchProduct, $maxSumArr);
                    // }

                    if($rows1 + $maxRows2 > $maxRowsSum){
                        $maxRows2 = $maxRowsSum - $rows1;
                    }
                    // two product width sum
                    $widthSum = $maxRows2 * $pairProduct2['sheet_width'] + $rows1 * $searchProductWidth;
                    $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);

                    $maxWidthSumChecked = 
                    [
                        'wasteRatio' => $wasteRatio,
                        'maximumWidth' => $maximumWidth,
                        'maxWidth' => $maxWidth,
                        'widthSum' => $widthSum
                    ];

                    $searchProduct['rows'] = $rows1;

                    $pairProduct2['rows'] = $maxRows2;
                    $pairProduct2['index'] = $key2;

                    // if($pairProduct2['sheet_width'] == 2173){
                    //     dd($searchProduct);
                    // }
                    $pairedList = 
                    [
                        'product1' => $searchProduct,
                        'product2' => $pairProduct2
                    ];

                    

                    if($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                    // $meters = $this->checkMetersQuantity($products, $maxWidthSumChecked);
                    // if($meters === false) continue;


                    if($wasteRatio < $maxSumArr['wasteRatio'] && $wasteRatio <= $maxWasteRatio)
                    {
                        $maxSumArr = $maxWidthSumChecked;

                        $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                        if($checkPairedList === false) continue;

                        $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                    }

                    $pairProducts3 = $pairProducts2;
                    unset($pairProducts3[$key2]);
                    if($pairProduct2['sheet_length'] == $searchProduct['sheet_length'])
                    {
                        
                        //three product width sum
                        for ($rows2=1; $rows2 <= $maxRows2 ; ++$rows2)
                        {
                            
                            $remaining_width -= $rows2 * $pairProduct2['sheet_width'];

                            // remaining third products
                            foreach ($pairProducts3 as $key3 => $pairProduct3) 
                            {
                                //calculating third product rows
                                $rows3 = (int)floor($remaining_width / $pairProduct3['sheet_width']);
                                

                                if($rows1 + $rows2 + $rows3 > $maxRowsSum)
                                {
                                    $rows3 = $maxRowsSum - $rows2 - $rows1;
                                }
                                $widthSum = $rows1 * $searchProductWidth + $rows2 * $pairProduct2['sheet_width'] + $rows3 * $pairProduct3['sheet_width'];
                                $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);

                                if($rows3 != 0)
                                {
                                    $maxWidthSumChecked = 
                                    [
                                        'wasteRatio' => $wasteRatio,
                                        'maximumWidth' => $maximumWidth,
                                        'maxWidth' => $maxWidth,
                                        'widthSum' => $widthSum,
                                        'pairIndex2' => $key2,
                                        'pairIndex3' => $key3
                                    ];

                                    $searchProduct['rows'] = $rows1;

                                    $pairProduct2['rows'] = $rows2;
                                    $pairProduct2['index'] = $key2;
                                    
                                    $pairProduct3['rows'] = $rows3;
                                    $pairProduct3['index'] = $key3;
                                    
                                    $pairedList = 
                                    [
                                        'product1' => $searchProduct,
                                        'product2' => $pairProduct2,
                                        'product3' => $pairProduct3
                                    ];
                                }
                                else
                                {
                                    $maxWidthSumChecked = 
                                    [
                                        'wasteRatio' => $wasteRatio,
                                        'maximumWidth' => $maximumWidth,
                                        'maxWidth' => $maxWidth,
                                        'widthSum' => $widthSum,
                                        'pairIndex2' => $key2,
                                    ];

                                    $searchProduct['rows'] = $rows1;

                                    $pairProduct2['rows'] = $rows2;
                                    $pairProduct2['index'] = $key2;
                                    
                                    $pairedList = 
                                    [
                                        'product1' => $searchProduct,
                                        'product2' => $pairProduct2
                                    ];
                                }
                                
                                if($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;
                                

                                // $meters = $this->checkMetersQuantity($products, $maxWidthSumChecked);
                                // if($meters === false) continue;

                                if($wasteRatio < $maxSumArr['wasteRatio'] && $wasteRatio <= $maxWasteRatio ){
                                    $maxSumArr = $maxWidthSumChecked;
                        
                                    $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                                    if($checkPairedList === false) continue;

                                    $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                                }
                            }   
                        }
                    }
                    else{

                        for ($rows2=1; $rows2 <= $maxRows2 ; ++$rows2)
                        {
                            $remaining_width -= $rows2 * $pairProduct2['sheet_width'];

                            // remaining third products
                            foreach ($pairProducts3 as $key3 => $pairProduct3) 
                            {
                                //calculating third product rows
                                if($pairProduct2['sheet_length'] == $pairProduct3['sheet_length'])
                                {
                                    $rows3 = floor($remaining_width / $pairProduct3['sheet_width']);
                                    // if($rows3 === 0) continue;

                                    if($rows1 + $rows2 + $rows3 > $maxRowsSum)
                                    {
                                        $rows3 = $maxRowsSum - $rows2 - $rows1;
                                    }
                                    $widthSum = $rows1 * $searchProductWidth + $rows2 * $pairProduct2['sheet_width'] + $rows3 * $pairProduct3['sheet_width'];
                                    $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);
                                    

                                    $maxWidthSumChecked = 
                                    [
                                        'wasteRatio' => $wasteRatio,
                                        'maximumWidth' => $maximumWidth,
                                        'maxWidth' => $maxWidth,
                                        'widthSum' => $widthSum,
                                        'rows1' => $rows1,
                                        'rows2' => $rows2,
                                        'rows3' => $rows3,
                                        'pairIndex2' => $key2,
                                        'pairIndex3' => $key3
                                    ];
                                    $searchProduct['rows'] = $rows1;
                                    $pairProduct2['rows'] = $rows2;
                                    $pairProduct3['rows'] = $rows3;
                                    $pairProduct3['index'] = $key3;

                                    $pairedList = 
                                    [
                                        'product1' => $searchProduct,
                                        'product2' => $pairProduct2,
                                        'product3' => $pairProduct3
                                    ];

                                    if($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                                    if($wasteRatio < $maxSumArr['wasteRatio'] && $wasteRatio <= $maxWasteRatio){
                                        $maxSumArr = $maxWidthSumChecked;
                        
                                        $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                                        if($checkPairedList === false) continue;

                                        $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                                    }
                                } 
                            }   
                        }
                    }
                    
                }  
            }
        }
        
        // dd($result);
        return $maxSumArr['wasteRatio'] != 1 ? $result : false;
    }

    public function isSingle($sheetWidth, $possibleWidths)
    {
        $maxRows = $this->params()['maxRows'];
        $minusfromMaxWidth = $this->params()['minusfromMaxWidth'];
        $maxWasteRatio = $this->params()['maxWasteRatio'];

        foreach ($possibleWidths as $maximumWidth) {
            $maxWidth = $maximumWidth - $this->params()['minusfromMaxWidth'];
            $rowsSingle = floor($maxWidth / $sheetWidth);
            if($rowsSingle > $maxRows) $rowsSingle = $maxRows;
            if(1 - $rowsSingle * $sheetWidth / $maximumWidth < $maxWasteRatio) return true;
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
        return $singles;
    }

    // public function pairing($searchProduct,$products,$minWidth,
    // $minMeters,$searchProductWidth,$key,$pairs,$boards,$mark,$minMetersParam)
    // {
    //     while (isset($products[$key]) 
    //     && $maxWidthArr = $this->maxWidthPair($minWidth,$minMeters,$searchProductWidth,$key,$products)) 
    //     {
    //         $pairedIndex = $maxWidthArr['pairIndex'];
    //         $pairedProduct = $products[$pairedIndex];
    //         $milimeters = $maxWidthArr['milimeters'];

    //         if($milimeters/1000 < $minMetersParam){
    //             return false;
    //         }
            
            
    //         $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
    //         / $searchProduct['sheet_length'],0);
    //         $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
    //         / $pairedProduct['sheet_length'],0);

    //         $products[$key]['quantity'] -= $searchProductQuantity;
    //         $products[$pairedIndex]['quantity'] -= $pairedProductQuantity;
            
    //         // if($searchProductWidth == 1700){
    //         // }
            
    //         $wasteM2 = $milimeters * (2500-$maxWidthArr['maxSum'])/1000000;   
                
    //         $product1 = 
    //         [
    //             'code' => $searchProduct['code'],
    //             'description' => $searchProduct['description'],
    //             'rows' => $maxWidthArr['rows1'],
    //             'sheet_width' => $searchProduct['sheet_width'],
    //             'sheet_length' => $searchProduct['sheet_length'],
    //             'quantity' => $searchProductQuantity,
    //             'dates' => $searchProduct['dates'],
    //             'order_id' => $searchProduct['order_id']
    //         ];
            
    //         $product2 = 
    //         [
    //             'code' => $pairedProduct['code'],
    //             'description' => $pairedProduct['description'],
    //             'rows' => $maxWidthArr['rows2'],
    //             'sheet_width' => $pairedProduct['sheet_width'],
    //             'sheet_length' => $pairedProduct['sheet_length'],
    //             'quantity' => $pairedProductQuantity,
    //             'dates' => $pairedProduct['dates'],
    //             'order_id' => $pairedProduct['order_id']
    //         ];
    //         if($product1['rows'] * $product1['sheet_width']<$product2['rows'] * $product2['sheet_width']){
    //             list($product1,$product2) = [$product2,$product1];
    //         }
            
    //         $pairs[$boards][$mark][] = 
    //         [
    //             'waste' => round($wasteM2,2),
    //             'meters' => round($milimeters/1000,0),
    //             'product1' => $product1,
    //             'product2' => $product2
    //         ];
    //         if($products[$key]['quantity']<=0){
    //             unset($products[$key]);
    //             if($products[$pairedIndex]['quantity']<=0){
    //                 unset($products[$pairedIndex]);
    //             }
    //         }
    //         else{
    //             unset($products[$pairedIndex]);
    //         }     
    //     }
        
    //     return 
    //     [
    //         'pairs' => $pairs,
    //         'products' =>$products
    //     ];
    // }

    //Calculation of the products from largest product width to smalest
    public function calculationMethod1($productsRSortByWidth, $minMetersParam, $possibleWidths)
    {
        $minMetersParam = $this->params()['minMeters'];
        
        $result = $this->pairing($productsRSortByWidth, 0, $possibleWidths);
        if($result === false){
            $result = $this->pairing($productsRSortByWidth, $minMetersParam, $possibleWidths);
        }
        dd($result);

        // $singles = $this->single($result['remaining_products'], $possibleWidths);
        return $result;

        
        

        // for ($i=0; $i < $length; $i++) 
        // {
        //     if($i == $length - 1)
        //     {
        //         $minWidth = 2140;
        //         $isLast = true;
        //     }
        //     else
        //     {
        //         $minWidth = $this->params()['minWidth'];
        //     }
        //     if($i){
        //         $merge = array_merge_recursive($remainingProducts,$productList[$i]);
        //         $result = $this->calculator($merge,$minWidth,$minMeters,$request,$board,$isLast,$joinList);
        //         if(!$result) return $this->calculationMethod($productList,$minMetersParam,$request,$board,$isLast,$joinList);
                
        //         $pairs = array_merge_recursive($pairs,$result['pairs']);
        //     } 
        //     else{
        //         $result = $this->calculator($productList[$i],$minWidth,$minMeters,$request,$board,$isLast,$joinList);
        //         if(!$result) return $this->calculationMethod($productList,$minMetersParam,$request,$board,$isLast,$joinList);
        //         $pairs = $result['pairs'];
        //     }
        //     $remainingProducts = $result['remaining_products'];
        // }

        // $joinList = $result['joinList'];
        // $joinMark = $result['joinMark'];
        // if(count($remainingProducts))
        // {
        //     $singleProducts = $this->calculatorSingle($remainingProducts,$maxWidth,$request,$board);
        //     $pairs = array_merge_recursive($pairs,$singleProducts);
        // }
        // $wasteSumArr = $this->wasteSum($pairs);
        // $pairs = array_merge_recursive($pairs,$wasteSumArr);
        // return 
        // [
        //     'pairs' => $pairs,
        //     'joinList' => $joinList,
        //     'joinMark' => $joinMark
        // ];
    }

    public function mainCalculator(Request $request, Board $board)
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
        $possibleWidths = $this->params()['possibleMaxWidths'];

        $joinList = $this->marksJoin($request);
        

        foreach ($productsList as $boardKey => $markProducts) 
        {
            $reversedProducts = array_reverse($markProducts,true);
            foreach ($reversedProducts as $markKey => $products) 
            {
                $widerThan820 = $this->filterByProductWidth($products, $markKey, $boardKey, 'widerThan820', $possibleWidths);
                $lessThan821 = $this->filterByProductWidth($products, $markKey, $boardKey, 'lessThan821', $possibleWidths);
                $singles = $this->filterByProductWidth($products, $markKey, $boardKey, 'singles', $possibleWidths);
                $allProducts[$boardKey][$markKey] = $products;

                
                $result = $this->calculationMethod1($allProducts, 0, $possibleWidths);
            }
        }


        
        // $widerThan820 = $this->getArrays('widerThan820', $request, $board);
        // $lessThan821 = $this->getArrays('lessThan821', $request, $board);
        // $singles = $this->getArrays('singles', $request, $board);
       

    }

    public function pairing($productList, $minMeters, $possibleWidths)
    {

        $pairs = [];
        $productCopy = $productList;

        $minMetersParam = $this->params()['minMeters'];
        $maxRowsParam = $this->params()['maxRows'];
        $largeWasteParam = $this->params()['largeWasteRatio'];
        $test = [];

        foreach ($productList as $boards => &$marks) 
        {
            foreach ($marks as $mark => &$products) 
            {
                
                foreach ($products as $key => &$searchProduct) 
                {
                    
                    if($this->isSingle($searchProduct['sheet_width'], $possibleWidths) 
                    || $searchProduct['quantityLeft'] == 0) continue;
                    
                    while (isset($products[$key]) 
                    && $maxWidthArr = $this->maxWidthPair2($searchProduct, $key, $products, $minMeters, $possibleWidths)) 
                    {
                        $maxWidthArr['pairedList']['product1']['maximum_width'] = $maxWidthArr['widthInfo']['maximumWidth'];

                        if($maxWidthArr['pairedList']['product1']['meters'] < $minMetersParam) return false;

                        
                        $pairs[$boards][$mark][] = $maxWidthArr['pairedList'];

                        foreach($maxWidthArr['pairedList'] as $product)
                        {
                            if($product['quantityLeft'] == 0)
                            {
                                unset($products[$product['index']]);
                            }
                            else
                            {
                                $products[$product['index']]['quantityLeft'] = $product['quantityLeft'];
                            }
                        }

                        // $test[] = $maxWidthArr;
                        // if(count($test) == 31){
                        //    dd($products,$test);
                        // }
                    
                    } 
                }  
            }
        }
            return 
            [
                'remaining_products' => $products,
                'pairs' => $pairs
            ];
    }

    // public function calculator($productsArray, $minWidth, $minMeters, 
    // Request $request, Board $board, $isLast = false, $joinList=[])
    // {
    //     $from = $this->dates($request)['manufactury_date_from'];
    //     $to = $this->dates($request)['manufactury_date_till'];
    //     $from2 = $this->dates($request)['load_date_from'];
    //     $to2 = $this->dates($request)['load_date_till'];
    //     $from3 = $this->dates($request)['future_manufactury_date_from'];
    //     $to3 = $this->dates($request)['future_manufactury_date_till'];
    //     $from4 = $this->dates($request)['future_load_date_from'];
    //     $to4 = $this->dates($request)['future_load_date_till'];


    //     $productsList = $this->getProductsList($from, $to, $from2, $to2, $request, $board);
    //     $futureProducts= $this->getProductsList($from3, $to3, $from4, $to4, $request, $board);
    //     $joinMark = '';

    //     
    //     $pairs = [];
    //     $productsCopy = $productsArray;

    //     $minMetersParam = $this->params()['minMeters'];
    //     $maxWidthParam = $this->params()['maxWidth'];
    //     $maxRowsParam = $this->params()['maxRows'];
    //     $largeWasteParam = $this->params()['largeWasteRatio'];
    //     
    //     foreach ($productsArray as $boards => &$marks) 
    //     {
    //         foreach ($marks as $mark => &$products) 
    //         {
                
    //             foreach ($products as $key => &$searchProduct) 
    //             {
                    
    //                 
    //                 $searchProductWidth = $searchProduct['sheet_width'];
    //                 if($this->isSingle($searchProductWidth)){
    //                     continue;
    //                 }
                    
    //                 $pairsProducts = $this->pairing($searchProduct,$products,$minWidth,$minMeters,
    //                 $searchProductWidth,$key,$pairs,$boards,$mark,$minMetersParam);
    //                 if(!$pairsProducts) return false;
    //                 $pairs = $pairsProducts['pairs'];
    //                 $products = $pairsProducts['products'];
                    

    //                 if($isLast && isset($products[$key]) && isset($futureProducts[$boards][$mark]))
    //                 {
    //                     $futureProductsList = &$futureProducts[$boards][$mark];
    //                     while (isset($products[$key]) 
    //                     && $maxWidthArr = $this->maxWidthPair($minWidth,$minMeters,$searchProductWidth,$key,$products,$futureProductsList)) 
    //                     {
    //                         $pairedIndex = $maxWidthArr['pairIndex'];
    //                         $pairedProduct = $futureProductsList[$pairedIndex];
    //                         $milimeters = $maxWidthArr['milimeters'];
                        
                            
    //                         if($milimeters/1000 < $minMetersParam){
    //                             return false;
    //                         }
                            
    //                         $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
    //                         / $searchProduct['sheet_length'],0);
    //                         $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
    //                         / $pairedProduct['sheet_length'],0);

    //                         $searchProduct['quantity'] -= $searchProductQuantity;
    //                         $futureProductsList[$pairedIndex]['quantity'] -= $pairedProductQuantity;

    //                         $wasteM2 = $milimeters * (2500-$maxWidthArr['maxSum'])/1000000;   
                                
    //                         $product1 = 
    //                         [
    //                             'code' => $searchProduct['code'],
    //                             'description' => $searchProduct['description'],
    //                             'rows' => $maxWidthArr['rows1'],
    //                             'sheet_width' => $searchProduct['sheet_width'],
    //                             'sheet_length' => $searchProduct['sheet_length'],
    //                             'quantity' => $searchProductQuantity,
    //                             'dates' => $searchProduct['dates'],
    //                             'order_id' => $searchProduct['order_id']
    //                         ];
                            
    //                         $product2 = 
    //                         [
    //                             'code' => $pairedProduct['code'],
    //                             'description' => $pairedProduct['description'],
    //                             'rows' => $maxWidthArr['rows2'],
    //                             'sheet_width' => $pairedProduct['sheet_width'],
    //                             'sheet_length' => $pairedProduct['sheet_length'],
    //                             'quantity' => $pairedProductQuantity,
    //                             'dates' => $pairedProduct['dates'],
    //                             'order_id' => $pairedProduct['order_id']
    //                         ];
    //                         if($product1['rows'] * $product1['sheet_width']<$product2['rows'] * $product2['sheet_width']){
    //                             list($product1,$product2) = [$product2,$product1];
    //                         }
                            
    //                         $pairs[$boards][$mark][] = 
    //                         [
    //                             'waste' => round($wasteM2,2),
    //                             'meters' => round($milimeters/1000,0),
    //                             'product1' => $product1,
    //                             'product2' => $product2
    //                         ];
                        
    //                         
    //                         if($searchProduct['quantity']<=0){
    //                             unset($products[$key]);
    //                             if($futureProductsList[$pairedIndex]['quantity']<=0){
    //                                 unset($futureProductsList[$pairedIndex]);
    //                             }
    //                         }
    //                         else{
    //                             unset($futureProductsList[$pairedIndex]);
    //                         }           
    //                     }
    //                 }
                    

    //                 if($isLast && isset($products[$key]) && count($joinList))
    //                 {
                        
    //                     $singleRows = floor($maxWidthParam/$searchProductWidth);
    //                     if($singleRows > $maxRowsParam)  $singleRows = $maxRowsParam;
                        
    //                     $isLargeWaste = 1 - $singleRows * $searchProductWidth / 2500 >= $largeWasteParam;
                        
                        
    //                     if($isLargeWaste)
    //                     {
    //                         $joinProductsList = &$joinList;
    //                         $boardName = array_key_first($joinProductsList[0]);
    //                         $joinMark = array_key_first($joinProductsList[0][$boardName]);
    //                         while (isset($products[$key]) 
    //                         && $maxWidthArr = $this->maxWidthJoin($minWidth,$minMeters,$searchProductWidth,$products[$key],$joinList)) 
    //                         {
    //                             
    //                             $index1 = $maxWidthArr['key'];
    //                             $board = $maxWidthArr['board'];
    //                             $index2 = $maxWidthArr['index'];

    //                             $pairedProduct = $joinProductsList[$index1][$board][$joinMark][$index2];
    //                             $milimeters = $maxWidthArr['milimeters'];
                            
                                
    //                             if($milimeters/1000 < $minMetersParam){
    //                                 return false;
    //                             }
                                
    //                             $searchProductQuantity = round($milimeters * $maxWidthArr['rows1'] 
    //                             / $searchProduct['sheet_length'],0);
    //                             $pairedProductQuantity = round($milimeters * $maxWidthArr['rows2'] 
    //                             / $pairedProduct['sheet_length'],0);
    
    //                             $searchProduct['quantity'] -= $searchProductQuantity;
    //                             $joinProductsList[$index1][$board][$joinMark][$index2]['quantity'] -= $pairedProductQuantity;
    
    //                             $wasteM2 = $milimeters * (2500-$maxWidthArr['maxSum'])/1000000;   
                                    
    //                             $product1 = 
    //                             [
    //                                 'code' => $searchProduct['code'],
    //                                 'description' => $searchProduct['description'],
    //                                 'rows' => $maxWidthArr['rows1'],
    //                                 'sheet_width' => $searchProduct['sheet_width'],
    //                                 'sheet_length' => $searchProduct['sheet_length'],
    //                                 'quantity' => $searchProductQuantity,
    //                                 'dates' => $searchProduct['dates'],
    //                                 'order_id' => $searchProduct['order_id']
    //                             ];
                                
    //                             $product2 = 
    //                             [
    //                                 'code' => $pairedProduct['code'],
    //                                 'description' => $pairedProduct['description'],
    //                                 'rows' => $maxWidthArr['rows2'],
    //                                 'sheet_width' => $pairedProduct['sheet_width'],
    //                                 'sheet_length' => $pairedProduct['sheet_length'],
    //                                 'quantity' => $pairedProductQuantity,
    //                                 'dates' => $pairedProduct['dates'],
    //                                 'order_id' => $pairedProduct['order_id']
    //                             ];
    //                             if($product1['rows'] * $product1['sheet_width'] < $product2['rows'] * $product2['sheet_width']){
    //                                 list($product1,$product2) = [$product2,$product1];
    //                             }
                                
    //                             $pairs[$boards][$mark][] = 
    //                             [
    //                                 'waste' => round($wasteM2,2),
    //                                 'meters' => round($milimeters/1000,0),
    //                                 'product1' => $product1,
    //                                 'product2' => $product2
    //                             ];
                            
    //                             if($searchProduct['quantity']<=0){
    //                                 unset($products[$key]);
    //                                 if($joinProductsList[$index1][$board][$joinMark][$index2]['quantity']<=0){
    //                                     unset($joinProductsList[$index1][$board][$joinMark][$index2]);
    //                                 }
    //                             }
    //                             else{
    //                                 unset($joinProductsList[$index1][$board][$joinMark][$index2]);
    //                             }           
    //                         }
    //                     }
                        
    //                 } 
    //             }  
    //         }
    //     }
    //        
    //         return 
    //         [
    //             'remaining_products' => $productsArray,
    //             'pairs' => $pairs,
    //             'joinList' => $joinList,
    //             'joinMark' => $joinMark
    //         ];
    // }

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
            }
            $remainingProducts = $result['remaining_products'];
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

        return
        [
            'joinProductsList' => $joinProductsList,
            'seperateProductList' => $seperateProductsList
        ];
    }

    public function calculationJoin(Request $request, $productList,$marks_origin,$marks_join, $board)
    {
        $isLast = false;
        $pairs = [];
        $productListCopy = $productList;
        foreach ($productList as $mark => &$joinList) 
        {
            foreach ($joinList as $joinType => $productArray) 
            {   
                
                
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
        // foreach ($reversedList as $mark => &$joinList) 
        // {
        //     foreach ($joinList as $joinType => $productArray) 
        //     {   
                
        //         $result = $this->calculationMethod($joinList['join'], 0 , $request , $board, $isLast, $joinList['origin']);
        //         $pairs2 = array_merge_recursive($pairs2,$result['pairs']);
        //         $originProductList = $result['joinList'];
        //         $joinMark = $result['joinMark'];
        //         $mark_key = array_search($joinMark,$marks_join);
        //         if($mark_key!==false)
        //         {
        //             $reversedList[$joinMark]['origin'] = $originProductList;
        //         }
        //         else
        //         {
        //             $result = $this->calculationMethod($originProductList, 0 , $request , $board, $isLast, []);
        //             $pairs2 = array_merge_recursive($pairs2,$result['pairs']);
        //         }
        //         break;
        //     }
        // }
        
        
    }

    public function store(Request $request, Board $board)
    {
        $this->mainCalculator($request, $board);
        // $from = $this->dates($request)['manufactury_date_from'];
        // $to = $this->dates($request)['manufactury_date_till'];
        // $from2 = $this->dates($request)['load_date_from'];
        // $to2 = $this->dates($request)['load_date_till'];

        // // $widerThan820 = $this->getArrays('widerThan820', $request, $board);
        // // $lessThan821 = $this->getArrays('lessThan821', $request, $board);
        // // $singles = $this->getArrays('singles', $request, $board);
        // // $exceptSingles = $this->getArrays('exceptSingles', $request, $board);
        // $products1 = [$widerThan820, $lessThan821, $singles];
        // $products2 = [$exceptSingles, $singles];
        // $products3 = [$this->getProductsList($from, $to, $from2, $to2, $request, $board)];

        // $exceptJoinProducts1 = $this->getSeperateList($products1,$request)['seperateProductList'];
        // $exceptJoinProducts2 = $this->getSeperateList($products2,$request)['seperateProductList'];
        // $exceptJoinProducts3 = $this->getSeperateList($products3,$request)['seperateProductList'];

        // if(isset($request->marks_origin))
        // {
        //     $marks_origin = $request->marks_origin;
        //     $marks_join = $request->marks_join;
        //     $joinProducts1 = $this->getSeperateList($products1,$request)['joinProductsList'];
        //     $this -> calculationJoin($request, $joinProducts1, $marks_origin,$marks_join, $board);
        // }

        // $result1 = $this->calculationMethod($exceptJoinProducts1, 0 , $request , $board);
        // $result2 = $this->calculationMethod($exceptJoinProducts2, 0 , $request , $board);
        // $result3 = $this->calculationMethod($exceptJoinProducts3, 0 , $request , $board);

        // $resultArr = [$result1, $result2, $result3];
        // $result = [];
        //     foreach ($resultArr as $key =>$array) 
        //     {
        //         foreach ($array['pairs'] as $board => $marks) 
        //         {
        //             foreach ($marks as $mark => $pairs) 
        //             {
        //                 if(!isset($result[$board][$mark]['waste']))
        //                 {
        //                     $result[$board][$mark] = $pairs;
        //                     $result[$board][$mark]['waste'] = $pairs['waste'];
        //                 }
        //                 else if($pairs['waste'] < $result[$board][$mark]['waste'])
        //                 {
        //                     $result[$board][$mark] = $pairs;
        //                     $result[$board][$mark]['waste'] = $pairs['waste'];
        //                 }
                        
                        
                        
        //             }   
        //         }
            
            
        // }
        // $quantity = $this->quantityTest($result);
        
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
