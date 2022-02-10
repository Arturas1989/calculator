<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\PairController;

class PairControllerTest extends TestCase
{

    // private $pairController;

    public function  setUp(): void
    {
        parent::setUp();

        $this->pairController = new PairController;
    }

    public function data3(){
        return 
        [
            'products' =>
            [
                0 => 
                [
                    "code" => "G20BE0R8",
                    "description" => "Airuslita",
                    "sheet_width" => 1240,
                    "sheet_length" => 1200,
                    "quantity" => 900,
                    "totalQuantity" => 900,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 203
                ],
                2 => 
                [
                    "code" => "G20BE0R7",
                    "description" => "Airuslita",
                    "sheet_width" => 840,
                    "sheet_length" => 1200,
                    "quantity" => 800,
                    "totalQuantity" => 800,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 202
                ],
                9 => 
                [
                    "code" => "G20BE0R9",
                    "description" => "Airuslita",
                    "sheet_width" => 120,
                    "sheet_length" => 1200,
                    "quantity" => 1000,
                    "totalQuantity" => 1000,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 204
                ],
                4 => 
                [
                    "code" => "G20BE0R5",
                    "description" => "Airuslita",
                    "sheet_width" => 500,
                    "sheet_length" => 1500,
                    "quantity" => 700,
                    "totalQuantity" => 700,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 200
                ],   
            ],
            'searchProduct' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1240,
                "sheet_length" => 1200,
                "quantity" => 900,
                "totalQuantity" => 900,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203
            ]
        ];
         
        
    }

    public function data2()
    {
        return 
        [
            'products' =>
            [
                0 => 
                [
                    "code" => "G20BE0R8",
                    "description" => "Airuslita",
                    "sheet_width" => 1240,
                    "sheet_length" => 1200,
                    "quantity" => 900,
                    "totalQuantity" => 900,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 203
                ],
                1 => 
                [
                    "code" => "G20BE0R11",
                    "description" => "Airuslita",
                    "sheet_width" => 1220,
                    "sheet_length" => 1220,
                    "quantity" => 1000,
                    "totalQuantity" => 1000,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 206
                ],
                9 => 
                [
                    "code" => "G20BE0R9",
                    "description" => "Airuslita",
                    "sheet_width" => 120,
                    "sheet_length" => 1200,
                    "quantity" => 1000,
                    "totalQuantity" => 1000,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 204
                ],
                4 => 
                [
                    "code" => "G20BE0R5",
                    "description" => "Airuslita",
                    "sheet_width" => 500,
                    "sheet_length" => 1500,
                    "quantity" => 700,
                    "totalQuantity" => 700,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 200
                ],   
            ],
            'searchProduct' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1240,
                "sheet_length" => 1200,
                "quantity" => 900,
                "totalQuantity" => 900,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203
            ]
        ];
         
        
    }
    
    /**
     * A basic unit test example.
     *
     * @return void
     */

    public function test_pairController_method_maxWidthPair_2_3_products_maxSum_is_greater_or_equal_to_minWidth()
    {
        $data2 = $this->data2();
        $data3 = $this->data3();
        $products2 = $data2['products'];
        $products3 = $data3['products'];
        $searchProduct2 = $data2['searchProduct'];
        $searchProduct3 = $data3['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['absoluteMinWidth'];
        $maxWidth = $this->pairController->params()['maxWidth'];
        
        $result2 = $this->pairController->maxWidthPair2($searchProduct2, $index, $products2, $minWidth);
        $result3 = $this->pairController->maxWidthPair2($searchProduct3, $index, $products3, $minWidth);

        
        $this->assertGreaterThanOrEqual($minWidth, $result2['maxSum']);
        $this->assertGreaterThanOrEqual($minWidth, $result3['maxSum']);
    }

    public function test_pairController_method_maxWidthPair_2_products_maxSum_is_less_or_equal_to_maxWidth()
    {
        $data = $this->data2();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        $maxWidth = $this->pairController->params()['maxWidth'];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);

        
        $this->assertLessThanOrEqual($maxWidth, $result['maxSum']);
    }

    public function test_pairController_method_maxWidthPair_2_products_rows_sum_is_less_or_equal_maximum_rows()
    {
        $data = $this->data2();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        $maxRows = $this->pairController->params()['maxRows'];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);

        $rowSum = $result['rows1'] + $result['rows2'];
        $this->assertLessThanOrEqual($maxRows, $rowSum);
    }
    
    public function test_pairController_method_maxWidthPair_should_be_able_to_return_array_with_2_products()
    {
        $data = $this->data2();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        // dd($searchProduct);
        $expectedResult =
        [
            "maxSum" => 2460,
            "rows1" => 1,
            "rows2" => 1,
            "rows3" => null,
            "pairIndex2" => 1,
            "pairIndex3" => null
        ];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);
        
        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
    }

    public function test_pairController_method_maxWidthPair_should_be_able_to_return_array_with_3_products()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        // dd($searchProduct);
        $expectedResult =
        [
            "maxSum" => 2440,
            "rows1" => 1,
            "rows2" => 1,
            "rows3" => 3,
            "pairIndex2" => 2,
            "pairIndex3" => 9
        ];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);
        
        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
    }

    public function test_pairController_method_maxWidthPair_when_paired_3_products_lengths_of_at_least_2_products_should_be_equal()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        // dd($searchProduct);
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);
        $index2 = $result['pairIndex2'];
        $index3 = $result['pairIndex3'];

        $isConditionMet = $products[$index]['sheet_length'] == $products[$index2]['sheet_length'] 
        || $products[$index2]['sheet_length'] == $products[$index3]['sheet_length'];

        $this->assertTrue($isConditionMet);
    }


    public function test_pairController_method_maxWidthPair_3_products_rows_sum_is_less_or_equal_maximum_rows()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        $maxRows = $this->pairController->params()['maxRows'];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);

        $rowSum = $result['rows1'] + $result['rows2'] + $result['rows3'];
        $this->assertLessThanOrEqual($maxRows, $rowSum);
    }

    public function test_pairController_method_maxWidthPair_3_products_maxSum_is_less_or_equal_to_maxWidth()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $minWidth = $this->pairController->params()['minWidth'];
        $maxWidth = $this->pairController->params()['maxWidth'];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, $minWidth);

        
        $this->assertLessThanOrEqual($maxWidth, $result['maxSum']);
    }
}
