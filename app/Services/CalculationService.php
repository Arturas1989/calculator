<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Http\Controllers\ProductController;

class CalculationService
{


    /**
     * Constructs a new cart object.
     *
     * @param Illuminate\Session\SessionManager $session
     */





    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public $params;
    //skaičiuoklės parametrai

    public function __construct()
    {
        $this->ProductController = new ProductController();
        $this->params = 
        [
            'description' => 'tikslus',
            'quantityMore' => 20,
            'quantityRatio' => 0.03,
            'largeWasteRatio' => 0.12,
            'minMeters' => 70,
            'minMarkMeters' => 1000,
            'minBoardMeters' => 5000,
            'possibleMaxWidths' => [2500],
            'minusfromMaxWidth' => 40,
            'maxWasteRatio' => 0.08,
            'maxSingleWasteRatio' => 0.068,
            'absoluteMaxWasteRatio' => 0.141,
            'maxRows' => 8
        ];
    }
    

    //profilio numeris iš markės
    public function gradeNum($mark)
    {
        $pos = strpos($mark, 'R') ? strpos($mark, 'R') : strpos($mark, 'W');
        return intval(substr($mark, $pos - 2, 2));
    }

    //DUOMENU FUNKCIJOS
    //-----------------



    public function getProductsList($from, $to, $from2, $to2, $request, Board $board)
    {
        $productsList = [];

        $data = $board->getAllRelationsByIdAndDate($request, $from, $to, $from2, $to2);

        foreach ($data as $board) {
            $marks = $board->marks;
            if (count($marks) == 0) continue;

            $marks = $marks->sortByDesc('mark_name');
            foreach ($marks as $mark) {
                foreach ($mark->product as $product) {
                    foreach ($product->order as $order) {

                        $company_name = $product->company->company_name;
                        $product->description ?
                            $description =  $company_name . ' ' . $product->description : $description = $company_name;

                        $product->bending ? $bending =  $product->bending : $bending =  '';
                        $dates = substr($order->manufactury_date, -2) . ' (' . substr($order->load_date, -2) . ')';
                        $productsList[$board->board_name][$mark->mark_name][] =
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
        }

        return $productsList;
    }

    public function marksJoin(Request $request)
    {
        $marksJoin = [];
        if (isset($request->marks_origin)) {
            for($i = 0; $i<count($request->marks_origin); $i++){
                $marksJoin[$request->marks_origin[$i]] = $request->marks_join[$i];
            }
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

        if (isset($request->load_date_from) && isset($request->load_date_till)) {
            $from2 = $request->load_date_from;
            $to2 = $request->load_date_till;
        }
        if (isset($request->future_manufactury_date_from) && isset($request->future_manufactury_date_till)) {
            $from3 = $request->future_manufactury_date_from;
            $to3 = $request->future_manufactury_date_till;
        }
        if (isset($request->future_load_date_from) && isset($request->future_load_date_till)) {
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

    public function getMarkWidths($possibleWidths, $productsList)
    {
        $markWidths = [];
        foreach($productsList as $boardKey => $markProducts){
            foreach($markProducts as $markKey => $products){
                $markWidths[$boardKey][$markKey] = $possibleWidths;
            }
        }
        return $markWidths;
    }

    public function filterByProductWidth($markProducts, $mark, $board, $widthType, $possibleWidths)
    {
        $wasteRatio = $this->params['absoluteMaxWasteRatio'];
        $productList = [];

        switch ($widthType) {
            case 'widerThan820':
                $productList[$board][$mark] = array_filter($markProducts, function ($product) use ($possibleWidths) {
                    return $product['sheet_width'] > 820 && !$this->isSingle($product['sheet_width'], $possibleWidths);
                });
                break;

            case 'lessThan821':
                $productList[$board][$mark] = array_filter($markProducts, function ($product) use ($possibleWidths) {
                    return $product['sheet_width'] < 821 && !$this->isSingle($product['sheet_width'], $possibleWidths);
                });
                break;

            case 'exceptSingles':
                $productList[$board][$mark] = array_filter($markProducts, function ($product) use ($possibleWidths) {
                    return !$this->isSingle($product['sheet_width'], $possibleWidths);
                });
                break;

            case 'singles':
                $productList[$board][$mark] = array_filter($markProducts, function ($product) use ($possibleWidths) {
                    return $this->isSingle($product['sheet_width'], $possibleWidths);
                });
                break;

            case 'problematicProducts':
                $productList[$board][$mark] = $this->getProblematicProducts($markProducts, $possibleWidths, $wasteRatio);
                break;
            
            case 'nonProblematicProducts':
                $productList[$board][$mark] = array_filter($markProducts, function ($product) use ($possibleWidths, $wasteRatio) {
                    $wasteSingleRows = $this->singleProductsWaste($product['sheet_width'], $product['totalQuantity'], $product['sheet_length'], $possibleWidths);
                    return $wasteSingleRows['wasteRatio'] <= $wasteRatio || $this->isSingle($product['sheet_width'], $possibleWidths);
                });
                break;

            default:
                return [];
        }
        return $productList;
    }

    public function sortBySingleRows(&$productsList, $possibleWidths)
    {
        foreach($productsList as $boardKey => &$markProducts){
            foreach($markProducts as $markKey => &$products){
                uasort($products,function($product1, $product2) use($possibleWidths){
                    $productInfo1 = $this->singleProductsWaste($product1['sheet_width'], $product1['totalQuantity'], $product1['sheet_length'], $possibleWidths);
                    $productInfo2 = $this->singleProductsWaste($product2['sheet_width'], $product2['totalQuantity'], $product2['sheet_length'], $possibleWidths);
                    if($productInfo1['singleRows'] == $productInfo2['singleRows']) return $product1['sheet_width'] <=> $product2['sheet_width'];
                    return $productInfo2['singleRows'] <=> $productInfo1['singleRows'];
                });
            }
        }
    }

    public function getProblematicProducts($markProducts, $possibleWidths, $wasteRatio)
    {
        $filtered = [];
        
        foreach($markProducts as $key => &$product){

            $singleWasteRows = $this->singleProductsWaste($product['sheet_width'], $product['totalQuantity'], $product['sheet_length'], $possibleWidths);
            
            $product['singleWaste'] = $singleWasteRows['minWaste'];
            $product['singleRows'] = $singleWasteRows['singleRows'];
            $product['wasteRatio'] = $singleWasteRows['wasteRatio'];
            if ($product['wasteRatio'] > $wasteRatio && !$this->isSingle($product['sheet_width'], $possibleWidths)){
                $filtered[$key] = $product;
            }
        }
        uasort($filtered, function($product1, $product2){
            return $product2['singleRows'] == $product1['singleRows'] ? 
            $product2['sheet_width']<=>$product1['sheet_width'] : $product2['singleRows'] <=> $product1['singleRows'];
        });
        return $filtered;
    }

    public function checkMetersQuantity($pairedList, $minMeters, $possibleWidths)
    {
        
        
        
        $pairedList = $this->calculatePairedMeters($pairedList);
        
        

        $minWidth = min($possibleWidths) - $this->params['minusfromMaxWidth'];

        foreach ($pairedList as $product) {
            if ($product['quantityLeft'] == 0) continue;
            
            $singleRows = $this->minSingleRows($product['sheet_width'], $minWidth);
            
            $metersToCheck = $this->calculateMeters($product['quantityLeft'], $singleRows, $product['sheet_length']);

            if ($metersToCheck < $minMeters) {
                return false;
            }
        }

        return $pairedList;
    }


    public function widthDif($width, $maxWidth, $widthSum, $pairedRows)
    {
        $maxRows = $this->params['maxRows'];

        $singleRows = floor($maxWidth / $width);
        if ($singleRows > $maxRows) $singleRows = $maxRows;

        $singleWidth = $singleRows * $width;
        $widthDif = $singleWidth - $widthSum;

        return $widthDif / $singleRows * $pairedRows;
    }



    public function isWidthsEqual($pairedList, $widthInfo)
    {
        $maxWidth = $widthInfo['maxWidth'];
        $widthSum = $widthInfo['widthSum'];


        $widthDifs = [];
        foreach ($pairedList as $product) {
            $widthDifs[] = round($this->widthDif($product['sheet_width'], $maxWidth, $widthSum, $product['rows']), 2);
        }

        return array_sum($widthDifs) >= 0;
    }

    // check if quantityLeft doesnt exceed minimum requirements. If so add meters that it takes all quantity.
    public function canAdMetr($productList, $adMetr, $params)
    {
        foreach($productList as $product)
        {
            if($product['quantityLeft'] == 0){
                $additionalQuantity = $this->calculateQuantity($adMetr, $product['rows'], $product['sheet_length']);
                if($additionalQuantity / $product['totalQuantity'] > $params['quantityRatio']){
                    if($additionalQuantity > $params['quantityMore']){
                        return false;
                    }
                }
            }  
        }
        return true;
    }

    public function correctMeters($productList, &$meters)
    {
        
        $params = $this->params;
        $adMetrList = [];
        foreach ($productList as $product){
            if (strpos($product['description'], $params['description'])) return;
            $adMetr = $this->calculateMeters($product['quantityLeft'], $product['rows'], $product['sheet_length']);
            if($adMetr != 0){
                if($this->canAdMetr($productList, $adMetr, $params)){
                    $adMetrList[] = $adMetr;
                }
            }
        }

        if(count($adMetrList) == 0) return;
        $adMetr = max($adMetrList);

        foreach ($productList as &$product){
            $additionalQuantity = $this->calculateQuantity($adMetr, $product['rows'], $product['sheet_length']);
            $product['quantityLeft'] -= $additionalQuantity;
            $product['quantityLeft'] = max($product['quantityLeft'], 0);
            $product['pairedQuantity'] += $additionalQuantity;
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


        if (isset($pairedList['product3'])) {
            $product3 = &$pairedList['product3'];
            $meters3 = $this->calculateMeters($product3['quantityLeft'], $product3['rows'], $product3['sheet_length']);
            $meters = min($meters1, $meters2, $meters3);
            $pairedQuantity3 = $this->calculateQuantity($meters, $product3['rows'], $product3['sheet_length']);
            $product3['quantityLeft'] -= $pairedQuantity3;
            $product3['pairedQuantity'] = $pairedQuantity3;
            if ($product3['quantityLeft'] < 0) $product3['quantityLeft'] = 0;
        } else {
            $meters = min($meters1, $meters2);
        }

        $pairedQuantity1 = $this->calculateQuantity($meters, $product1['rows'], $product1['sheet_length']);
        $product1['pairedQuantity'] = $pairedQuantity1;
        $product1['quantityLeft'] -= $pairedQuantity1;
        if ($product1['quantityLeft'] < 0) $product1['quantityLeft'] = 0;

        $pairedQuantity2 = $this->calculateQuantity($meters, $product2['rows'], $product2['sheet_length']);
        $product2['pairedQuantity'] = $pairedQuantity2;
        $product2['quantityLeft'] -= $pairedQuantity2;
        if ($product2['quantityLeft'] < 0) $product2['quantityLeft'] = 0;

        
        $this->correctMeters($pairedList, $meters);
        
        $pairedList['product1']['meters'] = $meters;

        return $pairedList;
    }

    public function calculateMeters($quantity, $rows, $sheet_length)
    {
        return (int)ceil($quantity * $sheet_length / $rows / 1000);
    }

    public function calculateQuantity($meters, $rows, $sheet_length)
    {
        return (int)round($meters * $rows * 1000 / $sheet_length, 0);
    }

    public function minSingleRows($product_width, $minWidth)
    {
        $maxRows = $this->params['maxRows'];
        $singleRows = floor($minWidth / $product_width);
        return $singleRows > $maxRows ? $maxRows : max($singleRows, 1);
    }



    public function maxWidthPair2($searchProduct, $index, $products, $minMeters, $possibleWidths, $maxWasteRatio)
    {
        
        
        $maxSumArr = ['wasteRatio' => 1];
        $maxRowsSum = $this->params['maxRows'];

        $minusFromWidth = $this->params['minusfromMaxWidth'];
        $searchProductWidth = $searchProduct['sheet_width'];

        $result = [];
        foreach ($possibleWidths as $maximumWidth) {
            $maxWidth = $maximumWidth - $minusFromWidth;

            $maxRows1 = floor($maxWidth / $searchProductWidth);
            if ($maxRows1 === 0) continue;

            if ($maxRows1 > $maxRowsSum) {
                $maxRows1 = $maxRowsSum;
            }

            // remaining second products
            $pairProducts2 = $products;
            unset($pairProducts2[$index]);

            $searchProduct['index'] = $index;
            foreach ($pairProducts2 as $key2 => &$pairProduct2) {
                
                for ($rows1 = 1; $rows1 <= $maxRows1; ++$rows1) {

                    $remaining_width = $maxWidth - $rows1 * $searchProductWidth;

                    // calculating second product maximum rows
                    $maxRows2 = (int)floor($remaining_width / $pairProduct2['sheet_width']);
                    if ($maxRows2 === 0) break;

                    if ($rows1 + $maxRows2 > $maxRowsSum) {
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
                        

                    $pairedList =
                        [
                            'product1' => $searchProduct,
                            'product2' => $pairProduct2
                        ];



                    if ($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                    if ($wasteRatio < $maxSumArr['wasteRatio'] && $wasteRatio <= $maxWasteRatio) {
                        
                        $maxSumArr = $maxWidthSumChecked;
                        

                        $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                        if ($checkPairedList === false) continue;

                        $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                    }

                    $pairProducts3 = $pairProducts2;
                    unset($pairProducts3[$key2]);
                    if ($pairProduct2['sheet_length'] == $searchProduct['sheet_length']) {

                        //three product width sum
                        for ($rows2 = 1; $rows2 <= $maxRows2; ++$rows2) {

                            $remaining_width -= $rows2 * $pairProduct2['sheet_width'];

                            // remaining third products
                            foreach ($pairProducts3 as $key3 => $pairProduct3) {
                                //calculating third product rows
                                $rows3 = (int)floor($remaining_width / $pairProduct3['sheet_width']);

                                if ($rows1 + $rows2 + $rows3 > $maxRowsSum) {
                                    $rows3 = $maxRowsSum - $rows2 - $rows1;
                                }
                                $widthSum = $rows1 * $searchProductWidth + $rows2 * $pairProduct2['sheet_width'] + $rows3 * $pairProduct3['sheet_width'];
                                $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);

                                if ($rows3 != 0) {
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
                                } else {
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

                                if ($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                                if ($wasteRatio < $maxSumArr['wasteRatio'] && $wasteRatio <= $maxWasteRatio) {
                                    $maxSumArr = $maxWidthSumChecked;

                                    $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                                    if ($checkPairedList === false) continue;

                                    $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                                }
                            }
                        }
                    } else {

                        for ($rows2 = 1; $rows2 <= $maxRows2; ++$rows2) {

                            $remaining_width -= $rows2 * $pairProduct2['sheet_width'];

                            // remaining third products
                            foreach ($pairProducts3 as $key3 => $pairProduct3) {

                                //calculating third product rows
                                if ($pairProduct2['sheet_length'] == $pairProduct3['sheet_length']) {

                                    $rows3 = (int)floor($remaining_width / $pairProduct3['sheet_width']);
                                    if($rows3 === 0) continue;

                                    if ($rows1 + $rows2 + $rows3 > $maxRowsSum) {
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

                                    if ($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                                    if ($wasteRatio < $maxSumArr['wasteRatio'] && $wasteRatio <= $maxWasteRatio) {
                                        $maxSumArr = $maxWidthSumChecked;

                                        

                                        $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                                        if ($checkPairedList === false) continue;

                                        $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        

        return $maxSumArr['wasteRatio'] != 1 ? $result : false;
    }

    public function maxWidthJoin($searchProduct, $index, $products, $minMeters, $possibleWidths, $maxWasteRatio)
    {
        
        
        $maxSumArr = ['wasteRatio' => 1, 'search_prod_width_rate' => 0];
        $maxRowsSum = $this->params['maxRows'];

        $minusFromWidth = $this->params['minusfromMaxWidth'];
        $searchProductWidth = $searchProduct['sheet_width'];


        $result = [];
        
        foreach ($possibleWidths as $maximumWidth) {
            $maxWidth = $maximumWidth - $minusFromWidth;

            $maxRows1 = floor($maxWidth / $searchProductWidth);
            if ($maxRows1 === 0) continue;

            if ($maxRows1 > $maxRowsSum) {
                $maxRows1 = $maxRowsSum;
            }

            // remaining second products
            $pairProducts2 = $products;
            $searchProduct['index'] = $index;
            foreach ($products as $key2 => &$pairProduct2) {
                
                
                for ($rows1 = 1; $rows1 <= $maxRows1; ++$rows1) {
                    
                    $searchProduct['rows'] = $rows1;
                    $search_prod_width_rate = round($searchProduct['rows'] * $searchProduct['sheet_width'] / $maximumWidth, 3);
                    $remaining_width = $maxWidth - $rows1 * $searchProductWidth;

                    // calculating second product maximum rows
                    $maxRows2 = (int)floor($remaining_width / $pairProduct2['sheet_width']);
                    if ($maxRows2 === 0) break;

                    if ($rows1 + $maxRows2 > $maxRowsSum) {
                        $maxRows2 = $maxRowsSum - $rows1;
                    }
                    // two product width sum
                    $widthSum = $maxRows2 * $pairProduct2['sheet_width'] + $searchProduct['rows'] * $searchProductWidth;
                    $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);

                    $pairProduct2['rows'] = $maxRows2;
                    $pairProduct2['index'] = $key2;

                    $maxWidthSumChecked =
                        [
                            'search_prod_width_rate' => $search_prod_width_rate,
                            'wasteRatio' => $wasteRatio,
                            'maximumWidth' => $maximumWidth,
                            'maxWidth' => $maxWidth,
                            'widthSum' => $widthSum
                        ];


                    $pairedList =
                        [
                            'product1' => $searchProduct,
                            'product2' => $pairProduct2
                        ];

                        

                    if ($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;
                    
                    
                    
                    $width_search_prod = $searchProduct['rows'] * $searchProduct['sheet_width'];
                    $width_paired_prod = $pairProduct2['rows'] * $pairProduct2['sheet_width'];
                    
                    if ($search_prod_width_rate >= $maxSumArr['search_prod_width_rate'] && $wasteRatio <= $maxWasteRatio) {
                        $maxSumArr = $maxWidthSumChecked;
                        

                        $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                        if ($checkPairedList === false) continue;

                        $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                    }

                    $pairProducts3 = $pairProducts2;
                    unset($pairProducts3[$key2]);
                    if ($pairProduct2['sheet_length'] == $searchProduct['sheet_length']) {

                        //three product width sum
                        for ($rows2 = 1; $rows2 <= $maxRows2; ++$rows2) {

                            $remaining_width -= $rows2 * $pairProduct2['sheet_width'];

                            // remaining third products
                            foreach ($pairProducts3 as $key3 => $pairProduct3) {
                                //calculating third product rows
                                $rows3 = (int)floor($remaining_width / $pairProduct3['sheet_width']);
                                if($rows3 === 0) continue;

                                if ($rows1 + $rows2 + $rows3 > $maxRowsSum) {
                                    $rows3 = $maxRowsSum - $rows2 - $rows1;
                                }
                                $widthSum = $rows1 * $searchProductWidth + $rows2 * $pairProduct2['sheet_width'] + $rows3 * $pairProduct3['sheet_width'];
                                $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);

                                if ($rows3 != 0) {
                                    $maxWidthSumChecked =
                                        [
                                            'search_prod_width_rate' => $search_prod_width_rate,
                                            'wasteRatio' => $wasteRatio,
                                            'maximumWidth' => $maximumWidth,
                                            'maxWidth' => $maxWidth,
                                            'widthSum' => $widthSum,
                                            'pairIndex2' => $key2,
                                            'pairIndex3' => $key3
                                        ];

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
                                } else {
                                    $maxWidthSumChecked =
                                        [
                                            'search_prod_width_rate' => $search_prod_width_rate,
                                            'wasteRatio' => $wasteRatio,
                                            'maximumWidth' => $maximumWidth,
                                            'maxWidth' => $maxWidth,
                                            'widthSum' => $widthSum,
                                            'pairIndex2' => $key2,
                                        ];

                                    $pairProduct2['rows'] = $rows2;
                                    $pairProduct2['index'] = $key2;

                                    $pairedList =
                                        [
                                            'product1' => $searchProduct,
                                            'product2' => $pairProduct2
                                        ];
                                }

                                if ($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                                if ($search_prod_width_rate >= $maxSumArr['search_prod_width_rate'] && $wasteRatio <= $maxWasteRatio) {
                                    $maxSumArr = $maxWidthSumChecked;

                                    
                        

                                    $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                                    if ($checkPairedList === false) continue;

                                    $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                                }
                            }
                        }
                    } else {

                        for ($rows2 = 1; $rows2 <= $maxRows2; ++$rows2) {
                            $remaining_width -= $rows2 * $pairProduct2['sheet_width'];

                            // remaining third products
                            foreach ($pairProducts3 as $key3 => $pairProduct3) {
                                //calculating third product rows
                                if ($pairProduct2['sheet_length'] == $pairProduct3['sheet_length']) {

                                    $rows3 = (int)floor($remaining_width / $pairProduct3['sheet_width']);
                                    if($rows3 === 0) continue;

                                    if ($rows1 + $rows2 + $rows3 > $maxRowsSum) {
                                        $rows3 = $maxRowsSum - $rows2 - $rows1;
                                    }
                                    $widthSum = $rows1 * $searchProductWidth + $rows2 * $pairProduct2['sheet_width'] + $rows3 * $pairProduct3['sheet_width'];
                                    $wasteRatio = round(1 - $widthSum / $maximumWidth, 3);


                                    $maxWidthSumChecked =
                                        [
                                            'search_prod_width_rate' => $search_prod_width_rate,
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
                                    $pairProduct2['rows'] = $rows2;
                                    $pairProduct3['rows'] = $rows3;
                                    $pairProduct3['index'] = $key3;

                                    $pairedList =
                                        [
                                            'product1' => $searchProduct,
                                            'product2' => $pairProduct2,
                                            'product3' => $pairProduct3
                                        ];

                                    if ($this->isWidthsEqual($pairedList, $maxWidthSumChecked)) continue;

                                    if ($search_prod_width_rate >= $maxSumArr['search_prod_width_rate'] && $wasteRatio <= $maxWasteRatio) {
                                        $maxSumArr = $maxWidthSumChecked;

                                        $checkPairedList = $this->checkMetersQuantity($pairedList, $minMeters, $possibleWidths);
                                        if ($checkPairedList === false) continue;

                                        $result = ['pairedList' => $checkPairedList, 'widthInfo' => $maxSumArr];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        

        return $maxSumArr['wasteRatio'] != 1 ? $result : false;
    }

    public function isSingle($sheetWidth, $possibleWidths)
    {
        $maxRows = $this->params['maxRows'];
        $minusfromMaxWidth = $this->params['minusfromMaxWidth'];
        $maxWasteRatio = $this->params['maxSingleWasteRatio'];
        foreach ($possibleWidths as $maximumWidth) {
            $maxWidth = $maximumWidth - $this->params['minusfromMaxWidth'];
            $rowsSingle = floor($maxWidth / $sheetWidth);
            if ($rowsSingle > $maxRows) $rowsSingle = $maxRows;
            if (1 - $rowsSingle * $sheetWidth / $maximumWidth < $maxWasteRatio) return true;
        }

        return false;
    }

    public function singleProductsWaste($sheetWidth, $quantity, $sheet_length, $possibleWidths)
    {
        $maxRows = $this->params['maxRows'];
        $minusfromMaxWidth = $this->params['minusfromMaxWidth'];
        $maxWasteRatio = $this->params['maxSingleWasteRatio'];
        $minWaste = PHP_INT_MAX;
        $wasteRatio = 0;

        foreach ($possibleWidths as $maximumWidth) {
            $maxWidth = $maximumWidth - $this->params['minusfromMaxWidth'];
            $rowsSingle = (int)floor($maxWidth / $sheetWidth);
            if ($rowsSingle > $maxRows) $rowsSingle = $maxRows;
            if ($rowsSingle == 0) continue;
            $meters = $this->calculateMeters($quantity, $rowsSingle, $sheet_length);
            $waste = (int)round($meters * $maximumWidth / 1000, 0);

            if ($waste < $minWaste){
                $minWaste = $waste;
                $singleRows = $rowsSingle;
                $wasteRatio = 1 - round($singleRows * $sheetWidth / $maximumWidth, 3);
            } 
        }

        return ['minWaste' => $minWaste, 'singleRows' => $rowsSingle, 'wasteRatio' => $wasteRatio];
    }

    public function calculatorSingle($products, $maxWasteRatio, $possibleWidths)
    {
        $singles = [];
        $remaining_products = [];
        $product_info = [];
        $params = $this->params;


        foreach ($products as $board => $boards) {
            foreach ($boards as $mark => $marks) {
                foreach ($marks as $index => $product) {
                    $wasteRatio = 1;
                    foreach ($possibleWidths as $width) {
                        $maxWidth = $width - $params['minusfromMaxWidth'];
                        
                        if ($product['sheet_width'] > $maxWidth) continue;

                        $rows = floor($maxWidth / $product['sheet_width']);
                        $product_waste_ratio = round(1 - $rows * $product['sheet_width'] / $width, 3);
                        if ($product_waste_ratio < $wasteRatio && $product_waste_ratio <= $maxWasteRatio) {

                            $wasteRatio = $product_waste_ratio;
                            
                            $product_info =
                                [
                                    'rows' => (int)$rows,
                                    'meters' => $this->calculateMeters($product['quantityLeft'], $rows, $product['sheet_length']),
                                    'maximum_width' => $width
                                ];
                        }
                    }
                    if (count($product_info)) {
                        $product['index'] = $index;
                        $product['rows'] = $product_info['rows'];
                        $product['pairedQuantity'] = $product['quantityLeft'];
                        $product['quantityLeft'] = 0;
                        $product['meters'] = $product_info['meters'];
                        $product['maximum_width'] = $product_info['maximum_width'];
                        $singles[$board][$mark][]['product1'] = $product;
                        $product_info = [];
                    } else {
                        $remaining_products[$board][$mark][$index] = $product;
                    }
                }
            }
        }

        return ['pairs' => $singles, 'remaining_products' => $remaining_products];
    }

    public function calculationMethod1($productsRSortByWidth, $minMeters, $possibleWidths, &$futureProducts = [], &$joinProducts = [])
    {
        $minMetersParam = $this->params['minMeters'];
        $maxWasteRatio = $this->params['maxWasteRatio'];
        
        $result = $this->pairing($productsRSortByWidth, $minMeters, $possibleWidths, $maxWasteRatio);
        
        $maxWasteRatio = $this->params['absoluteMaxWasteRatio'];
        $resultMaxWaste =  $this->pairing($result['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio, $futureProducts);
        

        $resultMaxWaste['pairs'] = array_merge_recursive($result['pairs'], $resultMaxWaste['pairs']);

        $resultFuture = $this->pairing($resultMaxWaste['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio, $futureProducts);
        
        $resultSingle =  $this->calculatorSingle($resultFuture['remaining_products'], $maxWasteRatio, $possibleWidths);
        $finalResult['pairs'] = array_merge_recursive($resultMaxWaste['pairs'], $resultFuture['pairs'], $resultSingle['pairs']);
        $finalResult['remaining_products'] = $resultSingle['remaining_products'];
        
        return $finalResult;
    }

    public function calculationMethod2($productList, $minMeters, $possibleWidths, &$futureProducts = [], &$joinProducts = [])
    {
        $remainingProducts = [];
        $pairs = [];
        $maxWasteRatio = $this->params['maxWasteRatio'];

        foreach ($productList as $key => $products) {
            $productsMerge = array_merge_recursive($remainingProducts, $products);

            $result = $this->pairing($productsMerge, $minMeters, $possibleWidths, $maxWasteRatio);

            $pairs = array_merge_recursive($result['pairs'], $pairs);

            $remainingProducts = $result['remaining_products'];
        }

        $mainResult['pairs'] = $pairs;

        $maxWasteRatio = $this->params['absoluteMaxWasteRatio'];
        $resultMaxWaste =  $this->pairing($result['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio);

        $resultFuture = $this->pairing($resultMaxWaste['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio, $futureProducts);
        $resultSingle =  $this->calculatorSingle($resultFuture['remaining_products'], $maxWasteRatio, $possibleWidths);
        $finalResult['pairs'] = array_merge_recursive($mainResult['pairs'], $resultMaxWaste['pairs'], $resultFuture['pairs'], $resultSingle['pairs']);
        $finalResult['remaining_products'] = $resultSingle['remaining_products'];
        return $finalResult;
    }

    public function calculationMethod3($productList, $minMeters, $possibleWidths, &$futureProducts = [], &$joinProducts = [])
    {
        $remainingProducts = [];
        $pairs = [];
        $params = $this->params;
        $maxWasteRatio = $params['absoluteMaxWasteRatio'];
        $productListCopy = $productList;
       
        $length = count($productList);
        for ($i = 0; $i<$length; $i++) {
            
            $productsMerge = array_merge_recursive($remainingProducts, $productList[$i]);
            if($i != $length - 1){
                foreach ($productList[$i+1] as &$mark) {
                    foreach ($mark as  &$products) {
                        $productsNext = &$products;
                    }
                }
               
            }
            else{
                $productsNext = [];
            }
            $result = $this->pairing($productsMerge, $minMeters, $possibleWidths, $maxWasteRatio, $productsNext);
            
            $pairs = array_merge_recursive($result['pairs'], $pairs);
            
            $remainingProducts = $result['remaining_products'];
            
        }
        
        $mainResult['pairs'] = $pairs;
        $mainResult['remaining_products'] = $result['remaining_products'];
        $productsCopy = array_merge($productList[0], $productList[1], $productList[2]);
        
        $resultFuture = $this->pairing($result['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio, $futureProducts);
        
        $resultSingle =  $this->calculatorSingle($resultFuture['remaining_products'], $maxWasteRatio, $possibleWidths);
        $finalResult['pairs'] = array_merge_recursive($mainResult['pairs'], $resultFuture['pairs'], $resultSingle['pairs']);
        $finalResult['remaining_products'] = $resultSingle['remaining_products'];

        return $finalResult;
    }

    public function calculationMethod4($problematicProducts, $nonProblematicProducts, $minMeters, $markKey, $boardKey, $possibleWidths, &$futureProducts = [], &$joinProducts = [])
    {
        $copy = array_merge_recursive($problematicProducts, $nonProblematicProducts);
        $maxWasteRatio = $this->params['maxWasteRatio'];
        $problematicProductsResult = $this->pairing($problematicProducts, $minMeters, $possibleWidths, $maxWasteRatio);
        

        $nonProbResult = $this->pairing($problematicProductsResult['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio, $nonProblematicProducts[$boardKey][$markKey]);
        
        $maxWasteRatio = $this->params['absoluteMaxWasteRatio'];
        $remaining_products = array_merge_recursive($nonProbResult['remaining_products'],$nonProblematicProducts);
        $resultMaxWaste =  $this->pairing($remaining_products, $minMeters, $possibleWidths, $maxWasteRatio);
        
        $resultFuture = $this->pairing($resultMaxWaste['remaining_products'], $minMeters, $possibleWidths, $maxWasteRatio, $futureProducts);
        $resultSingle = $this->calculatorSingle($resultFuture['remaining_products'], $maxWasteRatio, $possibleWidths);
        $finalResult['pairs'] = array_merge_recursive($problematicProductsResult['pairs'],$nonProbResult['pairs'],$resultMaxWaste['pairs'],$resultFuture['pairs'],$resultSingle['pairs']);
        $finalResult['remaining_products'] = $resultSingle['remaining_products'];
        return $finalResult;
    }

    public function smallestCalculation(&$products, &$futureProducts, $markKey, $boardKey, $possibleWidths)
    {
        $minMetersParam = $this->params['minMeters'];
        $widerThan820 = $this->filterByProductWidth($products, $markKey, $boardKey, 'widerThan820', $possibleWidths);
        $lessThan821 = $this->filterByProductWidth($products, $markKey, $boardKey, 'lessThan821', $possibleWidths);
        $singles = $this->filterByProductWidth($products, $markKey, $boardKey, 'singles', $possibleWidths);
        $problematicProducts = $this->filterByProductWidth($products, $markKey, $boardKey, 'problematicProducts', $possibleWidths);
        $nonProblematicProducts = $this->filterByProductWidth($products, $markKey, $boardKey, 'nonProblematicProducts', $possibleWidths);
        $probSortByWidth = $problematicProducts;
        $nonProbSortByWidth = $nonProblematicProducts;
        uasort($probSortByWidth[$boardKey][$markKey], function($a,$b){
            return $b['sheet_width']<=>$a['sheet_width'];
        });
        uasort($nonProbSortByWidth[$boardKey][$markKey], function($a,$b){
            return $b['sheet_width']<=>$a['sheet_width'];
        });
        
        $productsCopy[$boardKey][$markKey] = $products;
        $allProducts = [];

        
        $allProducts[$boardKey][$markKey] = $products;
        

        isset($futureProducts[$boardKey][$markKey]) ? $futureList = $futureProducts[$boardKey][$markKey] : $futureList = [];
        $futureProducts1 = $futureProducts2 = $futureProducts3 = $futureProducts4 = $futureProducts5 = $futureList;

        $result1 = $this->calculationMethod1($allProducts, $minMetersParam, $possibleWidths, $futureProducts1);
        $result2 = $this->calculationMethod2([$widerThan820, $lessThan821, $singles], $minMetersParam, $possibleWidths, $futureProducts2);
        $result3 = $this->calculationMethod3([$widerThan820, $lessThan821, $singles], $minMetersParam, $possibleWidths, $futureProducts3);
        $result4 = $this->calculationMethod4($problematicProducts, $nonProblematicProducts, $minMetersParam, $markKey, $boardKey, $possibleWidths, $futureProducts4);
        $result5 = $this->calculationMethod4($probSortByWidth, $nonProbSortByWidth, $minMetersParam, $markKey, $boardKey, $possibleWidths, $futureProducts4);
        
        
        $waste = 
        [
            'result1' => $this->wasteRatio($result1),
            'result2' => $this->wasteRatio($result2),
            'result3' => $this->wasteRatio($result3),
            'result4' => $this->wasteRatio($result4),
            'result5' => $this->wasteRatio($result5)
        ];

        $remainingFutureProducts = 
        [
            'result1' => $futureProducts1,
            'result2' => $futureProducts2,
            'result3' => $futureProducts3,
            'result4' => $futureProducts4,
            'result5' => $futureProducts5
        ];
        asort($waste);
        $smallestWasteKey = array_key_first($waste);
        $futureProducts = $remainingFutureProducts[$smallestWasteKey];
        $finalResult = $$smallestWasteKey;
        return $finalResult;
    }


    public function smallestWasteResult(&$products, &$futureProducts, $markKey, $boardKey, $possibleWidths)
    {
        isset($futureProducts[$boardKey][$markKey]) ? $futureList = $futureProducts[$boardKey][$markKey] : $futureList = [];
        $futureProducts1 = $futureProducts2 = $futureProducts3 = $futureList;
        
        $result = $this->smallestCalculation($products, $futureProducts1, $markKey, $boardKey, $possibleWidths);
        uasort($products,function($a,$b){
            return $b['sheet_width'] <=> $a['sheet_width'];
        });

        $highestToLowestWidthResult = $this->smallestCalculation($products, $futureProducts2, $markKey, $boardKey, $possibleWidths);
        $waste = 
        [
            'result' => $this->wasteRatio($result),
            'highestToLowestWidthResult' => $this->wasteRatio($highestToLowestWidthResult)
        ];

        $remainingFutureProducts = 
        [
            'result' => $futureProducts1,
            'highestToLowestWidthResult' => $futureProducts2
        ];

        
        asort($waste);
        $smallestWasteKey = array_key_first($waste);
        $futureProducts[$boardKey][$markKey] = $remainingFutureProducts[$smallestWasteKey];
        
        $finalResult = $$smallestWasteKey;
        return $finalResult;
        
    }

    public function joinDiferentMarks($finalResult, $joinList, $possibleWidths, $markKey, $boardKey, &$futureProducts, &$joinProducts)
    {
        $minMetersParam = $this->params['minMeters'];
        $joinResult = $this->calculationMethod1($finalResult['remaining_products'], $minMetersParam, $possibleWidths, $joinProducts);
        $finalResult['pairs'] = array_merge_recursive($finalResult['pairs'], $joinResult['pairs']);
        
        $finalResult['remaining_products'] = $joinResult['remaining_products'];

        if( isset( $futureProducts[$boardKey][$joinList[$markKey]] ) ){
            if(isset( $joinResult['remaining_products'][$boardKey][$markKey] ) && count($joinResult['remaining_products'][$boardKey][$markKey]) ){
                $joinFutureResult = $this->calculationMethod1($joinResult['remaining_products'], $minMetersParam, $possibleWidths, $futureProducts[$boardKey][$joinList[$markKey]]);
            
                $finalResult['pairs'] = array_merge_recursive($finalResult['pairs'], $joinFutureResult['pairs']);
                $finalResult['remaining_products'] = $joinFutureResult['remaining_products'];
            }
        }
        return $finalResult;
    }

    public function problematicCalculation($products, $markKey, $boardKey, $possibleWidths, &$futureProducts2)
    {
        $problematicProducts = $this->filterByProductWidth($products, $markKey, $boardKey, 'problematicProducts', $possibleWidths);
        $nonProblematicProducts = $this->filterByProductWidth($products, $markKey, $boardKey, 'nonProblematicProducts', $possibleWidths);
        
        $probResult = $this->smallestWasteResult($problematicProducts[$boardKey][$markKey], $nonProblematicProducts, $markKey, $boardKey, $possibleWidths);
        

        if(isset($probResult['remaining_products'][$boardKey][$markKey])){
            $currentResult = $this->smallestWasteResult($probResult['remaining_products'][$boardKey][$markKey], $futureProducts2, $markKey, $boardKey, $possibleWidths);
            $currentResult['pairs'] = array_merge_recursive($probResult['pairs'],$currentResult['pairs']);
        }
        else{
            $currentResult = $probResult;
        }
        
        return ['currentResult' => $currentResult, 'nonProblematicProducts' => $nonProblematicProducts];
    }

    public function get_max_sheetWidth_list($productsList)
    {
        $maxList = [];
        foreach($productsList as $boards){
            foreach($boards as $markKey => $products){
                $maxList[$markKey] = max(array_column($products, 'sheet_width'));
            }
        }
        return $maxList;
    }

    public function calculationHighestToLowest($productsList, $futureProducts, $joinList, $markWidths, $index)
    {
        $pairs = [];
        foreach ($productsList as $boardKey => &$markProducts) {
            foreach ($markProducts as $markKey => &$products) {
                $possibleWidths = $markWidths[$boardKey][$markKey];
                $finalResult = $this->smallestWasteResult($products, $futureProducts, $markKey, $boardKey, $possibleWidths);
                
                if(count($finalResult['remaining_products'])){
                    

                    if(isset($joinList[$markKey]) && isset($markProducts[$joinList[$markKey]])){
                        
                        $joinProducts = &$markProducts[$joinList[$markKey]];
                        $finalResult = $this->joinDiferentMarks($finalResult, $joinList, $possibleWidths, $markKey, $boardKey, $futureProducts, $joinProducts);
                    }
                }

                $remainingSingles = $this->calculatorSingle($finalResult['remaining_products'], 1, $possibleWidths);
                $products = [];
                $pairs = array_merge_recursive($pairs, $finalResult['pairs'], $remainingSingles['pairs']);
            }
            
        }

        return ['pairs' => $pairs, 'remaining_products' => []];
    }

    public function calculationLowestToHighest($productList2, $futureProducts2, $joinList, $markWidths, $index)
    {
        $pairs = [];
        foreach ($productList2 as $boardKey => &$markProducts) 
        {
            $markProducts = array_reverse($markProducts,true);
            foreach($markProducts as $markKey => &$products){
                $count = 2;
                $currentResult['remaining_products'][$boardKey][$markKey] = [];
                $possibleWidths = $markWidths[$boardKey][$markKey];
                while($count!=0 && isset($currentResult['remaining_products'][$boardKey][$markKey])){
                    $result = $this->problematicCalculation($products, $markKey, $boardKey, $possibleWidths, $futureProducts2);
                    
                    $currentResult = $result['currentResult'];
                    $goodProductsForJoin = $result['nonProblematicProducts'];
                    $products = array_merge_recursive($currentResult['remaining_products'], $goodProductsForJoin);
                    $products = $products[$boardKey][$markKey];
                    $pairs = array_merge_recursive($pairs, $currentResult['pairs']);
                    $count--;
                }
                
                
                if(in_array($markKey, $joinList)){
                    
                    $originMark = array_search($markKey, $joinList);
                    isset($markProducts[$originMark]) ? $originProducts = &$markProducts[$originMark] : $originProducts = [];

                    $possibleWidths = $markWidths[$boardKey][$originMark];
                    $result2 = $this->problematicCalculation($originProducts, $originMark, $boardKey, $possibleWidths, $futureProducts2);
                    $originResult = $result2['currentResult'];
                    $originGoodProducts = $result2['nonProblematicProducts'];

                    
                    if(isset($currentResult['remaining_products'][$boardKey][$markKey])){
                        
                        $remainingProductsCurrent = $currentResult['remaining_products'][$boardKey][$markKey];
                        $join = $this->joinDiferentMarks($originResult, $joinList, $possibleWidths, $originMark, $boardKey, $futureProducts2, $goodProductsForJoin[$boardKey][$markKey]);
                        

                        if(isset($join['remaining_products'][$boardKey][$originMark])){
                            $remainingProductsOrigin = array_merge_recursive($join['remaining_products'][$boardKey][$originMark], $originGoodProducts[$boardKey][$originMark]);
                        }
                        else if(isset($originGoodProducts[$boardKey][$originMark])){
                            $remainingProductsOrigin = $originGoodProducts[$boardKey][$originMark];
                            $markProducts[$originMark] = [];
                        }
                        else{
                            $remainingProductsOrigin = [];
                        }
                        
                        $allProducts = array_merge($remainingProductsOrigin, $remainingProductsCurrent);
                        $joinResult = $this->smallestWasteResult($allProducts, $futureProducts2, $originMark, $boardKey, $possibleWidths);
                        
                        
                        if(count($joinResult['remaining_products'])>0){
                            $remainingProductsJoin = [];

                            $remainingSingles = $this->settingRemainingProducts($joinResult, $markProducts, $markKey, $originMark, $boardKey, $goodProductsForJoin, $possibleWidths);
                            $pairs = array_merge_recursive($pairs, $join['pairs'], $joinResult['pairs'], $remainingSingles['pairs']); 
                        }
                        else{
                            $remainingSingles = $this->calculatorSingle($goodProductsForJoin, 1, $possibleWidths);
                            $pairs = array_merge_recursive($pairs, $join['pairs'], $joinResult['pairs'], $remainingSingles['pairs']);
                        }
                        
                    }
                    else{
                        $possibleWidths = $markWidths[$boardKey][$originMark];
                        $currentResult['remaining_products'][$boardKey][$originMark] = $markProducts[$originMark];
                        $currentResult['pairs'] = [];
                        $joinResult = $this->joinDiferentMarks($currentResult, $joinList, $possibleWidths, $originMark, $boardKey, $futureProducts2, $goodProductsForJoin[$boardKey][$markKey]);
                        $remainingSingles = $this->settingRemainingProducts($joinResult, $markProducts, $markKey, $originMark, $boardKey, $goodProductsForJoin, $possibleWidths);
                        $pairs = array_merge_recursive($pairs, $joinResult['pairs'], $remainingSingles['pairs']);
                    }  
                }
                else{
                    $remainingProducts = array_merge_recursive($currentResult['remaining_products'], $goodProductsForJoin);
                    $remainingSingles = $this->calculatorSingle($remainingProducts, 1, $possibleWidths);
                    $pairs = array_merge_recursive($pairs, $remainingSingles['pairs']);
                }
            }
                
        }
        return ['pairs' => $pairs, 'remaining_products' => []];
    }

    public function settingRemainingProducts(&$joinResult, &$markProducts, $markKey, $originMark, $boardKey, $goodProductsForJoin, $possibleWidths)
    {
        $remainingProductsJoin = [];
        if(!isset($joinResult['remaining_products'][$boardKey][$originMark])){
            $joinResult['remaining_products'][$boardKey][$originMark] = [];
        }
        $remainingProductsJoin[$boardKey][$markKey] = array_filter($joinResult['remaining_products'][$boardKey][$originMark], function($el) use ($markKey){
            return $this->ProductController->mark($el['code']) == $markKey;
        });
        $remainingProductsOrigin = array_filter($joinResult['remaining_products'][$boardKey][$originMark], function($el) use ($originMark){
            return $this->ProductController->mark($el['code']) == $originMark;
        });
        $markProducts[$originMark] = $remainingProductsOrigin;

        $remainingProductsJoin = array_merge_recursive($remainingProductsJoin, $goodProductsForJoin);
        $remainingSingles = $this->calculatorSingle($remainingProductsJoin, 1, $possibleWidths);
        return $remainingSingles;
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
        $possibleWidths = $this->params['possibleMaxWidths'];
        $markWidths = $this->getMarkWidths($possibleWidths, $productsList);
        $max_sheet_width_list = $this->get_max_sheetWidth_list($productsList);

        $this->sortBySingleRows($productsList, $possibleWidths);

        $futureProducts = $this->getProductsList($from3, $to3, $from4, $to4, $request, $board);
        $minMetersParam = $this->params['minMeters'];
        $productList2 = $product_test = $productsList;
        $futureProducts2 = $future_test = $futureProducts;

        
        if (count($productsList) == 0) return false;

        
        $joinList = $this->marksJoin($request);
        $markWidths2 = $markWidths;
        $k1 = 0;

        do{

            $result_from_highest_mark_to_lowest = $this->calculationHighestToLowest($productsList, $futureProducts, $joinList, $markWidths, $k1);
            $checkWidth = $this->isWidthMetersCorrect($markWidths, $result_from_highest_mark_to_lowest['pairs'], $possibleWidths, $max_sheet_width_list, $k1);
            $markWidths = $checkWidth[0];
            $isWidthCorrect = $checkWidth[1];
            $k1++;

        }while(!$isWidthCorrect && $k1 < 20);

        $k2 = 0;

        do{
            
            $result_from_lowest_mark_to_highest = $this->calculationLowestToHighest($productList2, $futureProducts2, $joinList, $markWidths2, $k2);
            $checkWidth = $this->isWidthMetersCorrect($markWidths2, $result_from_lowest_mark_to_highest['pairs'], $possibleWidths, $max_sheet_width_list, $k2);
            $markWidths2 = $checkWidth[0];
            $isWidthCorrect = $checkWidth[1];
            $k2++;

        }while(!$isWidthCorrect && $k2 < 20);
        // dd($result_from_lowest_mark_to_highest);
        
        $wasteRatio1 = $this->wasteRatio($result_from_highest_mark_to_lowest);
        $wasteRatio2 = $this->wasteRatio($result_from_lowest_mark_to_highest);  
        $finalResult = $wasteRatio2 <= $wasteRatio1 ? $result_from_lowest_mark_to_highest : $result_from_highest_mark_to_lowest;
        // dd($this->quantityTest($finalResult, $product_test), $finalResult);
        // dd($this->maximumWidthMeters($result_from_highest_mark_to_lowest['pairs'], $possibleWidths), $this->maximumWidthMeters($result_from_lowest_mark_to_highest['pairs'], $possibleWidths));
        // dd($this->quantityTest($result_from_highest_mark_to_lowest, $product_test), $this->quantityTest($result_from_lowest_mark_to_highest, $product_test));
        // dd($finalResult['pairs']['BC']['BC22R']);
        dd($wasteRatio1,$wasteRatio2,$finalResult,$this->quantityTest($finalResult, $product_test),1, $k1, $k2, $this->maximumWidthMeters($finalResult['pairs'], $possibleWidths));   
    }

    public function getLargerToFitWidth($possibleWidths, $maxProductWidth, $fromWidth)
    {
        sort($possibleWidths);
        foreach($possibleWidths as $width){
            if($width - $fromWidth >= $maxProductWidth) return $width;
        }
        return 0;
    }

    public function getFirstLargerWidth($widthMeters, $toFitWidth)
    {
        arsort($widthMeters);
        $largestWidth = max(array_keys($widthMeters));
        foreach($widthMeters as $width => $meterSum){
            if($width >= $toFitWidth) return $width;
        }
        return $largestWidth;
    }

    public function isWidthMetersCorrect($markWidths, $pairs, $possibleWidths, $max_sheet_width_list, $k)
    {
        
        $isCorrect = true;
        $widthMetersList = $this->maximumWidthMeters($pairs, $possibleWidths);
        // if($index == 1)dd($pairs, $markWidths, $widthMetersList);
        $minMarkMeters = $this->params['minMarkMeters'];
        $minBoardMeters = $this->params['minBoardMeters'];
        $fromWidth = $this->params['minusfromMaxWidth'];
        $maxProductWidth = max($max_sheet_width_list);
        $toFitWidth = $this->getLargerToFitWidth($possibleWidths, $maxProductWidth, $fromWidth);
        // dd($toFitWidth);
        // dd($widthMetersList);
        
        foreach($widthMetersList as $board => &$marks){
            
            $totalWidthMeters = $widthMetersList[$board]['total'];
            
            $maxTotal = max($totalWidthMeters);
            unset($widthMetersList[$board]['total']);
            if($maxTotal < $minBoardMeters){
                
                $isCorrect = false;
                $highestMeterWidth = $this->getFirstLargerWidth($totalWidthMeters, $toFitWidth);
                foreach($marks as $mark => &$widths){
                    $markWidths[$board][$mark] = [$highestMeterWidth];
                }
            }
            else{
                foreach($marks as $mark => $widths){
                    
                    $maxMarkProductWidth = $max_sheet_width_list[$mark];
                    $toFitMarkWidth = $this->getLargerToFitWidth($possibleWidths, $maxMarkProductWidth, $fromWidth);
                    $maxMarkWidthMeters = max($widths);
                    if($maxMarkWidthMeters < $minMarkMeters){
                        
                        $isCorrect = false;
                        $highestWidth = $this->getFirstLargerWidth($widths, $toFitMarkWidth);
                        $highestMeterWidth = $this->getFirstLargerWidth($totalWidthMeters, $toFitWidth);
                        
                        $markWidths[$board][$mark] = [$highestMeterWidth];
                        if($markWidths['B']['B16R'][0] === null) dd($highestMeterWidth,$totalWidthMeters, $toFitWidth, $mark, $highestWidth, $toFitMarkWidth, $widths);
                    }
                    else{
                        
                        foreach($widths as $width => $metersSum){
                            
                            if($metersSum < $minMarkMeters && count($markWidths[$board][$mark]) > 1){
                                
                                // dd($metersSum);
                                if($metersSum != 0){
                                    
                                    $isCorrect = false;
                                }
                                $index = array_search($width,$markWidths[$board][$mark]);
                                unset($markWidths[$board][$mark][$index]);
                            }
                        }
                    }
                }
            }  
        }
        
        // if($markWidths['B']['B16R'] === null)dd($markWidths);
        // if($k== 1) dd($isCorrect);
        return [$markWidths, $isCorrect];
    }

    public function maximumWidthMeters($result, $possibleWidths)
    {
        $meterList = [];
        
        foreach($result as $board => $marks){
            foreach($possibleWidths as $width){
                $meterList[$board]['total'][$width] = 0;
            }
            foreach($marks as $mark => $pairs){
                foreach($possibleWidths as $width){
                    $meterList[$board][$mark][$width] = 0;
                }
                foreach($pairs as $pair){
                    $meterList[$board]['total'][$pair['product1']['maximum_width']] += $pair['product1']['meters'];
                    $meterList[$board][$mark][$pair['product1']['maximum_width']] += $pair['product1']['meters'];
                } 
            }
        }
        return $meterList;
    }
    
    public function calculate_product_m2($sheet_width, $sheet_length, $quantity)
    {
        return round($sheet_width * $sheet_length / 1000000 * $quantity, 2);
    }

    public function calculate_paper_m2($meters, $maximum_width)
    {
        return round($meters * $maximum_width / 1000, 2);
    }

    public function wasteRatio($result)
    {
        $paper_m2 = 0;
        $product_m2 = 0;
        $params = $this->params;

        foreach($result['pairs'] as $marks){
            foreach($marks as $pairs){
                foreach($pairs as $products){
                    $paper_m2 += $this->calculate_paper_m2($products['product1']['meters'], $products['product1']['maximum_width']);
                    foreach($products as $product){
                        $product_m2 += $this->calculate_product_m2($product['sheet_width'], $product['sheet_length'], $product['pairedQuantity']);
                    } 
                } 
            }
        }
        
        sort($params['possibleMaxWidths']);
        foreach($result['remaining_products'] as $marks){
            foreach ($marks as $products) {
                foreach ($products as $product){
                    foreach($params['possibleMaxWidths'] as $paperWidth){
                        $maxWidth = $paperWidth - $params['minusfromMaxWidth'];
                        if($product['sheet_width'] <= $maxWidth){
                            $singleRows = $this->minSingleRows($product['sheet_width'], $maxWidth);
                            
                            $meters = $this->calculateMeters($product['quantityLeft'], $singleRows, $product['sheet_length']);
                            $paper_m2 += $this->calculate_paper_m2($meters, $paperWidth);
                            $product_m2 += $this->calculate_product_m2($product['sheet_width'], $product['sheet_length'], $product['quantityLeft']);
                        }
                    }
                }  
            }  
        }

        $wasteRatio = $paper_m2 == 0 ? PHP_INT_MAX : round(1 - $product_m2/$paper_m2, 3);
        return $wasteRatio;
    }

    public function getQuantityList($result)
    {
        $quantityList = [];
        foreach($result as $option => $productList){
            foreach ($productList as $board) {
                foreach ($board as $mark) {
                    foreach ($mark as $pair) {
                        if (is_array($pair)) {
                            if($option == 'remaining_products'){
                                if (!isset($quantityList[$pair['code']])) {
                                    $quantityList[$pair['code']] = $pair['quantityLeft'];
                                }
                                else{
                                    $quantityList[$pair['code']] += $pair['quantityLeft'];
                                }
                            }
                            else{
                                foreach ($pair as $product) {
                                    if (!isset($quantityList[$product['code']])) {
                                        $quantityList[$product['code']] = $product['pairedQuantity'];
                                    }
                                    else{
                                        $quantityList[$product['code']] += $product['pairedQuantity'];
                                    }
                                }
                            }
                            
                        }
                    }
                }
            }
        }
        return $quantityList;
    }

    public function quantityFutureTest($result, $products)
    {
        $badProductList = [];
        $quantityList = $this->getQuantityList($result);
        $params = $this->params;

        foreach($products as $boards){
            foreach($boards as $marks){
                foreach($marks as $product){
                    if(isset($quantityList[$product['code']])){
                        if($quantityList[$product['code']] > $product['totalQuantity']){

                            if($product['totalQuantity'] * (1 + $params['quantityRatio']) < $quantityList[$product['code']]
                            && $product['totalQuantity'] + $params['quantityMore'] < $quantityList[$product['code']]){
    
                                $percent = $params['quantityRatio'] * 100;
                                $badProductList[$product['code']]['totalQuantity'] = $product['totalQuantity'];
                                $badProductList[$product['code']]['error_message'] = "kiekis produktu sarase: ". $quantityList[$product['code']] ." daugiau nei parametras: $percent% nei ivestas:".  $product['totalQuantity'];
                            }
                            if($product['totalQuantity'] + $params['quantityMore'] < $quantityList[$product['code']]
                            && $product['totalQuantity'] / $quantityList[$product['code']] - 1 > $params['quantityRatio']){
                                $badProductList[$product['code']]['totalQuantity'] = $product['totalQuantity'];
                                $badProductList[$product['code']]['error_message'] = "kiekio suma yra: ". $quantityList[$product['code']] ."; parametras yra: " . $params['quantityMore']. "; uzsakymas yra: ". $product['totalQuantity'];
                            }
                        }
                    }
                    
                    
                }
            }
        }
        return count($badProductList) ? $badProductList : 'success';
    }

    public function quantityTest($result,$products)
    {
        $badProductList = [];
        $quantityList = $this->getQuantityList($result);
        $params = $this->params;

        foreach($products as $boards){
            foreach($boards as $marks){
                foreach($marks as $product){
                    if(!isset($quantityList[$product['code']])){
                        $badProductList[$product['code']]['totalQuantity'] = $product['totalQuantity'];
                        $badProductList[$product['code']]['error_message'] = 'sio kodo produktu sarase nera';
                    }
                    else if ($product['totalQuantity'] > $quantityList[$product['code']]){
                        $badProductList[$product['code']]['totalQuantity'] = $product['totalQuantity'];
                        $badProductList[$product['code']]['error_message'] = "kiekis produktu sarase: ". $quantityList[$product['code']] ." mazesnis nei ivestas: ". $product['totalQuantity'];
                    }
                    else if($quantityList[$product['code']] > $product['totalQuantity']){

                        if($product['totalQuantity'] * (1 + $params['quantityRatio']) < $quantityList[$product['code']]
                        && $product['totalQuantity'] + $params['quantityMore'] < $quantityList[$product['code']]){

                            $percent = $params['quantityRatio'] * 100;
                            $badProductList[$product['code']]['totalQuantity'] = $product['totalQuantity'];
                            $badProductList[$product['code']]['error_message'] = "kiekis produktu sarase: ". $quantityList[$product['code']] ." daugiau nei parametras: $percent% nei ivestas:".  $product['totalQuantity'];
                        }
                        if($product['totalQuantity'] + $params['quantityMore'] < $quantityList[$product['code']]
                        && $product['totalQuantity'] / $quantityList[$product['code']] - 1 > $params['quantityRatio']){
                            $badProductList[$product['code']]['totalQuantity'] = $product['totalQuantity'];
                            $badProductList[$product['code']]['error_message'] = "kiekio suma yra: ". $quantityList[$product['code']] ."; parametras yra: " . $params['quantityMore']. "; uzsakymas yra: ". $product['totalQuantity'];
                        }
                    }
                    
                }
            }
        }
        

        // krsort($quantityList);
        return count($badProductList) ? $badProductList : 'success';
    }

    public function filterPairs($code, $result, $board, $mark)
    {
        return array_filter($result['pairs'][$board][$mark], function($el) use ($code){
            if(isset($el['product3'])){
                return $el['product1']['code'] == $code || $el['product2']['code'] == $code || $el['product3']['code'] == $code;
            }
            else if (isset($el['product2'])){
                return $el['product1']['code'] == $code || $el['product2']['code'] == $code;
            }
            else{
                return $el['product1']['code'] == $code;
            }
        });
    }

    public function pairing($productList, $minMeters, $possibleWidths, $maxWasteRatio, &$prod_to_reduce_waste = [])
    {
        $pairs = [];
        $minMetersParam = $this->params['minMeters'];
        count($prod_to_reduce_waste) ? $method_name = 'maxWidthJoin' : $method_name = 'maxWidthPair2';

        foreach ($productList as $boards => &$marks) {
            foreach ($marks as $mark => &$products) {
                count($prod_to_reduce_waste) ? $prodList = &$prod_to_reduce_waste : $prodList = &$products;
                foreach ($products as $key => &$searchProduct) {

                    if (
                        $this->isSingle($searchProduct['sheet_width'], $possibleWidths)
                        || $searchProduct['quantityLeft'] == 0
                    ) continue;

                    while (
                        isset($products[$key])
                        && $maxWidthArr = $this->$method_name($searchProduct, $key, $prodList, $minMeters, $possibleWidths, $maxWasteRatio)
                    ) {

                        $maxWidthArr['pairedList']['product1']['maximum_width'] = $maxWidthArr['widthInfo']['maximumWidth'];

                        $pairs[$boards][$mark][] = $maxWidthArr['pairedList'];

                        foreach ($maxWidthArr['pairedList'] as $product_key => $product) {
                            if ($product_key == 'product1') {
                                if ($product['quantityLeft'] == 0) {
                                    unset($products[$product['index']]);
                                } else {
                                    $products[$product['index']]['quantityLeft'] = $product['quantityLeft'];
                                }
                            } else {
                                if ($product['quantityLeft'] == 0) {
                                    unset($prodList[$product['index']]);
                                } else {
                                    $prodList[$product['index']]['quantityLeft'] = $product['quantityLeft'];
                                }
                            }
                        }
                    }
                }
            }
        }

        return
            [
                'remaining_products' => $productList,
                'pairs' => $pairs
            ];
    }


    public function wasteSum($array)
    {
        $wasteSum = 0;
        $wasteSumArr = [];

        foreach ($array as $board => $marks) {
            foreach ($marks as $mark => $products) {
                foreach ($products as $product) {
                    foreach ($product as $key2 => $value) {
                        if ($key2 == 'waste') {
                            $wasteSum += $product['waste'];
                        } else {
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

    

    public function sheetWidthTest($array, $sheet_width)
    {
        $result = [];
        foreach ($array as $board) {
            foreach ($board as $mark) {
                foreach ($mark as $pair) {
                    if (is_array($pair)) {
                        foreach ($pair as $key => $value) {
                            if ($key == 'product1' || $key == 'product2' || $key == 'product') {
                                foreach ($value as $key2 => $product) {
                                    if ($key2 == 'sheet_width' && $value['sheet_width'] == $sheet_width) {

                                        $result[] = $pair;
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

    public function getSeperateList($list, Request $request)
    {
        $seperateProductsList = [];
        $joinProductsList = [];
        $allowedJoins = [];
        if (count($this->marksJoin($request))) {
            $marks_origin = $this->marksJoin($request)['marks_origin'];
            $marks_join = $this->marksJoin($request)['marks_join'];
        } else {
            return
                [
                    'joinProductsList' => $joinProductsList,
                    'seperateProductList' => $list
                ];
        }
        foreach ($list as $key => $productList) {
            foreach ($productList as $board => $marks) {
                foreach ($marks as $mark => $products) {
                    $mark_key = array_search($mark, $marks_origin);
                    if ($mark_key !== false) {
                        $join_mark = $marks_join[$mark_key];
                        $joinProductsList[$mark]['origin'][$key][$board][$mark] = $productList[$board][$mark];
                        $joinProductsList[$mark]['join'][$key][$board][$join_mark] = $productList[$board][$join_mark];
                    } else {
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

    public function calculationJoin(Request $request, $productList, $marks_origin, $marks_join, $board)
    {
        $isLast = false;
        $pairs = [];
        $productListCopy = $productList;
        foreach ($productList as $mark => &$joinList) {
            foreach ($joinList as $joinType => $productArray) {


                $result = $this->calculationMethod($joinList['origin'], 0, $request, $board, $isLast, $joinList['join']);
                $pairs = array_merge_recursive($pairs, $result['pairs']);
                $joinProductList = $result['joinList'];
                $joinMark = $result['joinMark'];
                $mark_key = array_search($joinMark, $marks_origin);
                if ($mark_key !== false) {
                    $productList[$joinMark]['origin'] = $joinProductList;
                } else {
                    $result = $this->calculationMethod($joinProductList, 0, $request, $board, $isLast, []);
                    $pairs = array_merge_recursive($pairs, $result['pairs']);
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
}
