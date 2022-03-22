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

    public function inputGenerator($width, $widthList)
    {
        $data = 
        [
            'products' =>
            [
                0 => 
                [
                    "code" => "G20BE0R8",
                    "description" => "Airuslita",
                    "sheet_width" => 1240,
                    "sheet_length" => 1200,
                    "quantityLeft" => 900,
                    "totalQuantity" => 900,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 203
                ]  
            ],
            'searchProduct' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1240,
                "sheet_length" => 1200,
                "quantityLeft" => 900,
                "totalQuantity" => 900,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203
            ]
        ];

        $minWasteRate = 0.025;
        $maxWasteRate = 0.03;
        $product = $data['searchProduct'];

        foreach ($widthList as $maximumWidth) {
            $product['sheet_width'] = $maximumWidth == $width ? 
            $maximumWidth * (1 - $minWasteRate)  - $data['searchProduct']['sheet_width']: 
            $maximumWidth * (1 - $maxWasteRate)  - $data['searchProduct']['sheet_width'];
            $product['sheet_length'] = rand(570,1100);
            $data['products'][] = $product;
        }

        return $data;
    }

    public function productData()
    {
        return 
        [ 
            'BE' =>
            [
                'BE20R' => 
                [
                    [
                        "code" => "G20BE0R8",
                        "description" => "Airuslita",
                        "sheet_width" => 1600,
                        "sheet_length" => 1200,
                        "quantityLeft" => 900,
                        "totalQuantity" => 900,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 203
                    ], 
                    [
                        "code" => "G20BE0R7",
                        "description" => "Airuslita",
                        "sheet_width" => 840,
                        "sheet_length" => 1200,
                        "quantityLeft" => 900,
                        "totalQuantity" => 900,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 202
                    ],
                    [
                        "code" => "G20BE0R9",
                        "description" => "Airuslita",
                        "sheet_width" => 1400,
                        "sheet_length" => 1200,
                        "quantityLeft" => 1000,
                        "totalQuantity" => 1000,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 204
                    ], 
                    [
                        "code" => "G20BE0R5",
                        "description" => "Airuslita",
                        "sheet_width" => 1000,
                        "sheet_length" => 1200,
                        "quantityLeft" => 1000,
                        "totalQuantity" => 1000,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 200
                    ]
                ] 
            ] 
        ];
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
                    "quantityLeft" => 900,
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
                    "quantityLeft" => 800,
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
                    "quantityLeft" => 1000,
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
                    "quantityLeft" => 700,
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
                "quantityLeft" => 900,
                "totalQuantity" => 900,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203
            ]
        ];
         
        
    }

    public function data3_1(){
        return 
        [
            'pairedList' => 
            [
                'product1' => 
                [
                    "code" => "G20BE0R8",
                    "description" => "Airuslita",
                    "sheet_width" => 489,
                    "sheet_length" => 1200,
                    "quantityLeft" => 900,
                    "totalQuantity" => 900,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 203,
                    "rows" => 1
                ],
                'product2' =>
                [
                    "code" => "G20BE0R7",
                    "description" => "Airuslita",
                    "sheet_width" => 492,
                    "sheet_length" => 1200,
                    "quantityLeft" => 800,
                    "totalQuantity" => 800,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 202,
                    "rows" => 4
                ]
            
            ],
            'widthInfo' =>
            [
                "wasteRatio" => 0.017,
                "maximumWidth" => 2500,
                "maxWidth" => 2460,
                "widthSum" => 2457,
                "rows1" => 1,
                "rows2" => 4,
                "pairIndex2" => 1
            ]    
        ];
         
        
    }

    public function data3_2(){
        return 
        [
            'pairedList' =>
            [
                'product1' => 
                [
                    "code" => "G20BE0R8",
                    "description" => "Airuslita",
                    "sheet_width" => 1240,
                    "sheet_length" => 1200,
                    "quantityLeft" => 900,
                    "totalQuantity" => 900,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 203,
                    'rows' => 1
                ],
                'product2' => 
                [
                    "code" => "G20BE0R7",
                    "description" => "Airuslita",
                    "sheet_width" => 840,
                    "sheet_length" => 1200,
                    "quantityLeft" => 800,
                    "totalQuantity" => 800,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 202,
                    'rows' => 1
                ],
                'product3' => 
                [
                    "code" => "G20BE0R9",
                    "description" => "Airuslita",
                    "sheet_width" => 120,
                    "sheet_length" => 1200,
                    "quantityLeft" => 1000,
                    "totalQuantity" => 1000,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 204,
                    'rows' => 3
                ]  
            ],
            'widthInfo' =>
            [
                "wasteRatio" => 0.024,
                "maximumWidth" => 2500,
                "maxWidth" => 2460,
                "widthSum" => 2440,
                "rows1" => 1,
                "rows2" => 1,
                "rows3" => 3,
                "pairIndex2" => 1,
                "pairIndex3" => 2
            ]
        ];  
    }

    public function data3_3(){
        return 
        [
            'pairedList' =>
            [
                'product1' => 
                [
                    "code" => "G20BE0R8",
                    "description" => "Airuslita",
                    "sheet_width" => 1240,
                    "sheet_length" => 1200,
                    "quantityLeft" => 900,
                    "totalQuantity" => 900,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 203,
                    'rows' => 1
                ],
                'product2' => 
                [
                    "code" => "G20BE0R7",
                    "description" => "Airuslita",
                    "sheet_width" => 470,
                    "sheet_length" => 1200,
                    "quantityLeft" => 800,
                    "totalQuantity" => 800,
                    "dates" => "08 (09)",
                    "bending" => "",
                    "order_id" => 202,
                    'rows' => 2
                ] 
            ],
            'widthInfo' =>
            [
                "wasteRatio" => 0.128,
                "maximumWidth" => 2500,
                "maxWidth" => 2460,
                "widthSum" => 2180,
                "rows1" => 1,
                "rows2" => 2,
                "pairIndex2" => 1
            ]
        ];
    }

    public function data2(){
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
                    "quantityLeft" => 900,
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
                    "quantityLeft" => 1000,
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
                    "quantityLeft" => 1000,
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
                    "quantityLeft" => 700,
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
                "quantityLeft" => 900,
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
    

    public function test_pairController_method_pairing_should_return_pairs()
    {
        $productList = $this->productData();
        $result = $this->pairController->pairing($productList, 0);
        $expected_result = 
        [
            "remaining_products" => [],
            "pairs" => [
                0 => [
                    "product1" => [
                        "code" => "G20BE0R8",
                        "description" => "Airuslita",
                        "sheet_width" => 1600,
                        "sheet_length" => 1200,
                        "quantityLeft" => 0.0,
                        "totalQuantity" => 900,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 203,
                        "index" => 0,
                        "rows" => 1,
                        "meters" => 1080.0
                    ],
                    "product2" => [
                        "code" => "G20BE0R7",
                        "description" => "Airuslita",
                        "sheet_width" => 840,
                        "sheet_length" => 1200,
                        "quantityLeft" => 0.0,
                        "totalQuantity" => 900,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 202,
                        "rows" => 1,
                        "index" => 1
                    ]
                ],
                1 => [
                    "product1" => [
                        "code" => "G20BE0R9",
                        "description" => "Airuslita",
                        "sheet_width" => 1400,
                        "sheet_length" => 1200,
                        "quantityLeft" => 0.0,
                        "totalQuantity" => 1000,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 204,
                        "index" => 2,
                        "rows" => 1,
                        "meters" => 1200.0,
                    ],
                    "product2" => [
                        "code" => "G20BE0R5",
                        "description" => "Airuslita",
                        "sheet_width" => 1000,
                        "sheet_length" => 1200,
                        "quantityLeft" => 0.0,
                        "totalQuantity" => 1000,
                        "dates" => "08 (09)",
                        "bending" => "",
                        "order_id" => 200,
                        "rows" => 1,
                        "index" => 3
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected_result, $result);

    }
    public function test_pairController_method_checkMetersQuantity_should_return_pairedList_when_meters_above_70()
    {
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1200,
                "sheet_length" => 1200,
                "quantityLeft" => 750,
                "totalQuantity" => 750,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1,
                'meters' => 300
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 390,
                "sheet_length" => 1200,
                "quantityLeft" => 1851,
                "totalQuantity" => 1851,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 400,
                "sheet_length" => 1500,
                "quantityLeft" => 600,
                "totalQuantity" => 600,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1
            ]
        ];

        $result = $this->pairController->checkMetersQuantity($pairedList, 70);
        $this->assertIsArray($result);
    }

    public function test_pairController_method_checkMetersQuantity_assert_false_when_min_meters_below_70()
    {
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1200,
                "sheet_length" => 1200,
                "quantityLeft" => 750,
                "totalQuantity" => 750,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1,
                'meters' => 300
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 390,
                "sheet_length" => 1200,
                "quantityLeft" => 1615,
                "totalQuantity" => 1615,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 400,
                "sheet_length" => 1500,
                "quantityLeft" => 600,
                "totalQuantity" => 600,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1
            ]
        ];

        $result = $this->pairController->checkMetersQuantity($pairedList, 70);

        $this->assertEquals(false, $result);
    }

    public function test_pairController_method_correctMeters_shouldnt_correct_when_1_product_dont_meet_quantity_requirements()
    {
        $meters = 900;
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1200,
                "sheet_length" => 1200,
                "quantityLeft" => 5,
                "totalQuantity" => 750,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 390,
                "sheet_length" => 1200,
                "quantityLeft" => 82,
                "totalQuantity" => 1500,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 400,
                "sheet_length" => 1500,
                "quantityLeft" => 1,
                "totalQuantity" => 600,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1
            ]
        ];

        $this->pairController->correctMeters($pairedList, $meters);

        $this->assertEquals(900, $meters);
    }
    public function test_pairController_method_correctMeters_should_work_with_3_products()
    {
        $meters = 900;
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 1200,
                "sheet_length" => 1200,
                "quantityLeft" => 5,
                "totalQuantity" => 755,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 390,
                "sheet_length" => 1200,
                "quantityLeft" => 0,
                "totalQuantity" => 1500,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],
            
            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 400,
                "sheet_length" => 1500,
                "quantityLeft" => 10,
                "totalQuantity" => 600,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 1
            ]
        ];

        $this->pairController->correctMeters($pairedList, $meters);

        $this->assertEquals(915, $meters);
    }

    public function test_pairController_method_correctMeters_shouldnt_correct_with_description_tikslus()
    {
        $meters = 300;
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita (tikslus kiekis)",
                "sheet_width" => 350,
                "sheet_length" => 1200,
                "quantityLeft" => 65,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 348,
                "sheet_length" => 1300,
                "quantityLeft" => 1300,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 347,
                "sheet_length" => 1300,
                "quantityLeft" => 1300,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ]
        ];

        $this->pairController->correctMeters($pairedList, $meters);

        $this->assertEquals(300, $meters);
    }
    public function test_pairController_method_correctMeters_should_work_with_quantityLeft_below_or_equal_quantityRatio()
    {
        $meters = 300;
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 350,
                "sheet_length" => 1200,
                "quantityLeft" => 65,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 348,
                "sheet_length" => 1300,
                "quantityLeft" => 5,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 347,
                "sheet_length" => 1300,
                "quantityLeft" => 5,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ]
        ];

        $this->pairController->correctMeters($pairedList, $meters);

        $this->assertEquals(339, $meters);
    }

    public function test_pairController_method_correctMeters_should_work_with_quantityLeft_below_or_equal_twenty()
    {
        $meters = 300;
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 350,
                "sheet_length" => 1200,
                "quantityLeft" => 20,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product2' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 348,
                "sheet_length" => 1300,
                "quantityLeft" => 0,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product3' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 347,
                "sheet_length" => 1300,
                "quantityLeft" => 5,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ]
        ];

        $this->pairController->correctMeters($pairedList, $meters);

        $this->assertEquals(312, $meters);
    }

    public function test_pairController_method_calculatePairedMeters_should_work_with_three_products()
    {
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 350,
                "sheet_length" => 1548,
                "quantityLeft" => 1300,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],
            

            'product2' =>
            [
                "code" => "G20BE0R9",
                "description" => "Airuslita",
                "sheet_width" => 349,
                "sheet_length" => 1240,
                "quantityLeft" => 1630,
                "totalQuantity" => 1630,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 3,
            ],

            'product3' =>
            [
                "code" => "G20BE0R9",
                "description" => "Airuslita",
                "sheet_width" => 348,
                "sheet_length" => 1240,
                "quantityLeft" => 500,
                "totalQuantity" => 500,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ]
            
        ];

        $result = $this->pairController->calculatePairedMeters($pairedList);
        $this->assertEquals(310, $result['product1']['meters']);
    }

    public function test_pairController_method_calculatePairedMeters_should_work_with_two_products()
    {
        $pairedList = 
        [
            'product1' =>
            [
                "code" => "G20BE0R8",
                "description" => "Airuslita",
                "sheet_width" => 492,
                "sheet_length" => 1548,
                "quantityLeft" => 1300,
                "totalQuantity" => 1300,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 2
            ],

            'product2' =>
            [
                "code" => "G20BE0R9",
                "description" => "Airuslita",
                "sheet_width" => 489,
                "sheet_length" => 1240,
                "quantityLeft" => 1630,
                "totalQuantity" => 1630,
                "dates" => "08 (09)",
                "bending" => "",
                "order_id" => 203,
                'rows' => 3
            ],

            'product3' => null
        ];

        $result = $this->pairController->calculatePairedMeters($pairedList);
        $this->assertEquals(674, $result['product1']['meters']);
    }

    public function test_pairController_method_minSingleRows_should_work_with_diferent_width()
    {
        $maxRows = $this->pairController->params()['maxRows'];
        $minWidth = 2100;
        $sheet_width = 270;
        $singleRows = $this->pairController->minSingleRows($sheet_width, $minWidth, $maxRows);
        $this->assertEquals($singleRows, 7);

        $minWidth = 2300;
        $sheet_width = 270;
        $singleRows = $this->pairController->minSingleRows($sheet_width, $minWidth, $maxRows);
        $this->assertEquals($singleRows, 8);
    }

     public function test_pairController_method_minSingleRows_should_return_max_rows_if_exceeds_single_rows()
    {
        $maxRows = $this->pairController->params()['maxRows'];
        $minWidth = 2500 - $this->pairController->params()['minusfromMaxWidth'];
        $sheet_width = 270;
        $singleRows = $this->pairController->minSingleRows($sheet_width, $minWidth);
        $this->assertEquals($singleRows, $maxRows);
    }

    public function test_pairController_method_calculateQuantity()
    {
        $meters1 = 100;
        $rows1 = 3;
        $sheet_length1 = 1470;
        $quantity1 = $this->pairController->calculateQuantity($meters1, $rows1, $sheet_length1);
        $this->assertEquals($quantity1, 204);

        $meters2 = 300;
        $rows2 = 2;
        $sheet_length2 = 1430;
        $quantity2 = $this->pairController->calculateQuantity($meters2, $rows2, $sheet_length2);
        $this->assertEquals($quantity2, 420);

        $meters3 = 500;
        $rows3 = 2;
        $sheet_length3 = 1620;
        $quantity3 = $this->pairController->calculateQuantity($meters3, $rows3, $sheet_length3);
        $this->assertEquals($quantity3, 617);
    }

    public function test_pairController_method_calculateMeters()
    {
        $quantity1 = 800;
        $rows1 = 3;
        $sheet_length1 = 1000;
        $meters1 = $this->pairController->calculateMeters($quantity1, $rows1, $sheet_length1);
        $this->assertEquals($meters1, 267);

        $quantity2 = 500;
        $rows2 = 4;
        $sheet_length2 = 1100;
        $meters2 = $this->pairController->calculateMeters($quantity2, $rows2, $sheet_length2);
        $this->assertEquals($meters2, 138);

        $quantity3 = 1490;
        $rows3 = 2;
        $sheet_length3 = 1220;
        $meters3 = $this->pairController->calculateMeters($quantity3, $rows3, $sheet_length3);
        $this->assertEquals($meters3, 909);
    }

    public function test_pairController_method_widthDif_test_with_input(){
        $maxWidth = 2460;
        $widthSum = 2390;

        $width1 = 600;
        $pairedRows1 = 2;
        $result1 = $this->pairController->widthDif($width1, $maxWidth, $widthSum, $pairedRows1);
        $this->assertEquals(round($result1, 2), 5.00);

        $width2 = 400;
        $pairedRows2 = 2;
        $result2 = $this->pairController->widthDif($width2, $maxWidth, $widthSum, $pairedRows2);
        $this->assertEquals(round($result2, 2), 3.33);

        $width3 = 390;
        $pairedRows3 = 1;
        $result3 = $this->pairController->widthDif($width3, $maxWidth, $widthSum, $pairedRows3);
        $this->assertEquals(round($result3, 2), -8.33);
    }

    public function test_pairController_method_isWidthsEqual_assert_false_when_single_rows_sum_is_not_equal_to_paired_products_rows_sum2(){
        $data = $this->data3_3();
        $widthInfo = $data['widthInfo'];
        $pairedList = $data['pairedList'];
        $result = $this->pairController->isWidthsEqual($pairedList, $widthInfo);
        
        $this->assertFalse($result);
    }

    public function test_pairController_method_isWidthsEqual_assert_false_when_single_rows_sum_is_not_equal_to_paired_products_rows_sum(){
        $data = $this->data3_2();
        $widthInfo = $data['widthInfo'];
        $pairedList = $data['pairedList'];
        $result = $this->pairController->isWidthsEqual($pairedList, $widthInfo);

        $this->assertFalse($result);
    }

    public function test_pairController_method_isWidthsEqual_assert_true_when_single_rows_sum_is_equal_to_paired_products_rows_sum(){
        $data = $this->data3_1();
        $pairedList = $data['pairedList'];
        $widthInfo = $data['widthInfo'];
        $result = $this->pairController->isWidthsEqual($pairedList, $widthInfo);

        $this->assertTrue($result);
    }

     public function test_pairController_method_maxWidthPair_works_with_diferent_maximum_widths()
    {
        $widthList = $this->pairController->params()['possibleMaxWidths'];
        $index = 0;

        foreach ($widthList as $maximumWidth) {
            $data = $this->inputGenerator($maximumWidth, $widthList);
            $result = $this->pairController->maxWidthPair2($data['searchProduct'], $index, $data['products'], 0);
            $this->assertEquals($maximumWidth, $result['widthInfo']['maximumWidth']);
        }
    }

     public function test_pairController_method_maxWidthPair_2_3_products_maxSum_is_greater_or_equal_to_minWidth()
    {
        $data2 = $this->data2();
        $data3 = $this->data3();
        $products2 = $data2['products'];
        $products3 = $data3['products'];
        $searchProduct2 = $data2['searchProduct'];
        $searchProduct3 = $data3['searchProduct'];
        $index = 0;
        $maxWasteRatio = $this->pairController->params()['maxWasteRatio'];
         
        
        $result2 = $this->pairController->maxWidthPair2($searchProduct2, $index, $products2, 0);
        $result3 = $this->pairController->maxWidthPair2($searchProduct3, $index, $products3, 0);
        $minWidth2 = (1 - $maxWasteRatio) * $result2['widthInfo']['maximumWidth'];
        $minWidth3 = (1 - $maxWasteRatio) * $result3['widthInfo']['maximumWidth'];
        
        $this->assertGreaterThanOrEqual($minWidth2, $result2['widthInfo']['widthSum']);
        $this->assertGreaterThanOrEqual($minWidth3, $result3['widthInfo']['widthSum']);
    }

    public function test_pairController_method_maxWidthPair_2_products_maxSum_is_less_or_equal_to_maxWidth()
    {
        $data = $this->data2();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);

        $this->assertLessThanOrEqual($result['widthInfo']['maxWidth'], $result['widthInfo']['widthSum']);
    }

    public function test_pairController_method_maxWidthPair_2_products_rows_sum_is_less_or_equal_maximum_rows()
    {
        $data = $this->data2();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $maxRows = $this->pairController->params()['maxRows'];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);

        $rowSum = 0;
        foreach($result['pairedList'] as $product){
            $rowSum += $product['rows'];
        }

        $this->assertLessThanOrEqual($maxRows, $rowSum);
    }
    
    public function test_pairController_method_maxWidthPair_should_be_able_to_return_array_with_2_products()
    {
        $data = $this->data2();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $expectedResult =
        [
            "pairedList" => [
                "product1" => [
                  "code" => "G20BE0R8",
                  "description" => "Airuslita",
                  "sheet_width" => 1240,
                  "sheet_length" => 1200,
                  "quantityLeft" => 0.0,
                  "totalQuantity" => 900,
                  "dates" => "08 (09)",
                  "bending" => "",
                  "order_id" => 203,
                  "index" => 0,
                  "rows" => 1,
                  "meters" => 1080.0,
                ],
                "product2" => [
                  "code" => "G20BE0R11",
                  "description" => "Airuslita",
                  "sheet_width" => 1220,
                  "sheet_length" => 1220,
                  "quantityLeft" => 115.0,
                  "totalQuantity" => 1000,
                  "dates" => "08 (09)",
                  "bending" => "",
                  "order_id" => 206,
                  "rows" => 1,
                  "index" => 1
                ],
            ],
              "widthInfo" => [
                "wasteRatio" => 0.016,
                "maximumWidth" => 2500,
                "maxWidth" => 2460,
                "widthSum" => 2460
              ]
        ];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);
        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
    }

    public function test_pairController_method_maxWidthPair_should_be_able_to_return_array_with_3_products()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $expectedResult =
        [
            "pairedList" => [
                "product1" => [
                  "code" => "G20BE0R8",
                  "description" => "Airuslita",
                  "sheet_width" => 1240,
                  "sheet_length" => 1200,
                  "quantityLeft" => 567.0,
                  "totalQuantity" => 900,
                  "dates" => "08 (09)",
                  "bending" => "",
                  "order_id" => 203,
                  "index" => 0,
                  "rows" => 1,
                  "meters" => 400.0
                ],
                "product2" => [
                  "code" => "G20BE0R7",
                  "description" => "Airuslita",
                  "sheet_width" => 840,
                  "sheet_length" => 1200,
                  "quantityLeft" => 467.0,
                  "totalQuantity" => 800,
                  "dates" => "08 (09)",
                  "bending" => "",
                  "order_id" => 202,
                  "rows" => 1,
                  "index" => 2
                ],
                "product3" => [
                  "code" => "G20BE0R9",
                  "description" => "Airuslita",
                  "sheet_width" => 120,
                  "sheet_length" => 1200,
                  "quantityLeft" => 0.0,
                  "totalQuantity" => 1000,
                  "dates" => "08 (09)",
                  "bending" => "",
                  "order_id" => 204,
                  "rows" => 3,
                  "index" => 9,
                ]
                ],
              "widthInfo" => [
                "wasteRatio" => 0.024,
                "maximumWidth" => 2500,
                "maxWidth" => 2460,
                "widthSum" => 2440,
                "pairIndex2" => 2,
                "pairIndex3" => 9
              ]
        ];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);
        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
    }

    public function test_pairController_method_maxWidthPair_when_paired_3_products_lengths_of_at_least_2_products_should_be_equal()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);
        $product1 = $result['pairedList']['product1'];
        $product2 = $result['pairedList']['product2'];
        $product3 = $result['pairedList']['product3'];

        $isConditionMet = $product1['sheet_length'] == $product2['sheet_length'] 
        || $product2['sheet_length'] == $product3['sheet_length'];

        $this->assertTrue($isConditionMet);
    }


    public function test_pairController_method_maxWidthPair_3_products_rows_sum_is_less_or_equal_maximum_rows()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        $maxRows = $this->pairController->params()['maxRows'];
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);
        $rowsSum = 0;
        // dd($result['pairedList']);
        foreach ($result['pairedList'] as $product) {
            $rowsSum += $product['rows'];
        }
        $this->assertLessThanOrEqual($maxRows, $rowsSum);
    }

    public function test_pairController_method_maxWidthPair_3_products_maxSum_is_less_or_equal_to_maxWidth()
    {
        $data = $this->data3();
        $products = $data['products'];
        $searchProduct = $data['searchProduct'];
        $index = 0;
        
        $result = $this->pairController->maxWidthPair2($searchProduct, $index, $products, 0);
        
        
        $this->assertLessThanOrEqual($result['widthInfo']['maximumWidth'], $result['widthInfo']['widthSum']);
    }
}
