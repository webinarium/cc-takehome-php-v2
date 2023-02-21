<?php

namespace Tests\Feature;

use App\Services\CalculateTotalByVolume;
use App\Services\CalculateTotalByWeight;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Tests\ReflectionTrait;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Test api auth
     *
     * @return void
     */
    public function test_api_requires_auth()
    {
        $response = $this->postJson(
            '/api/test',
            ['purchase_order_ids' => [2344]]
        );

        $response->assertStatus(401);
    }

    public function test_can_request_purchase_order_totals()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic '. base64_encode("demo:pwd1234")
        ])->postJson(
            '/api/test',
            ['purchase_order_ids' => [2344]]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result' => [
                    [
                        'product_type_id',
                        'total'
                    ],
                ]
            ]);
    }

    public function test_can_get_grouped_purchase_orders()
    {
        $responses = [];
        Http::fake([
            'https://api.cartoncloud.com.au/CartonCloud_Demo/1' => Http::response([
                'data' => [
                    'PurchaseOrderProduct' => [
                        [
                            'unit_quantity_initial' => '8.000',
                            'product_type_id' => '1',
                            'Product' => [
                                'volume' => '0.500',
                                'weight' => '1.500',
                            ],
                        ],
                        [
                            'unit_quantity_initial' => '6.000',
                            'product_type_id' => '2',
                            'Product' => [
                                'volume' => '2.500',
                                'weight' => '3.500',
                            ]
                        ]
                    ]
                ]
            ]),
            'https://api.cartoncloud.com.au/CartonCloud_Demo/2' => Http::response([
                'data' => [
                    'PurchaseOrderProduct' => [
                        [
                            'unit_quantity_initial' => '18.000',
                            'product_type_id' => '3',
                            'Product' => [
                                'volume' => '0.525',
                                'weight' => '1.125',
                            ]
                        ]
                    ]
                ]
            ])
        ]);
        $responses[] = Http::get('https://api.cartoncloud.com.au/CartonCloud_Demo/1');
        $responses[] = Http::get('https://api.cartoncloud.com.au/CartonCloud_Demo/2');

        $service = new PurchaseOrderService([
            new CalculateTotalByWeight(),
            new CalculateTotalByVolume()
        ]);
        $orders = $this->callMethod($service, 'getGroupedPurchaseOrdersFromResponses', [$responses]);

        $this->assertCount(3, $orders);
        $this->assertArrayHasKey(3, $orders);
        $this->assertArrayNotHasKey(0, $orders);
    }

    public function test_can_calculate_purchase_order_totals()
    {
        $groupedPurchaseOrders = [
            2 => [
                [
                    'unit_quantity_initial' => '8.000',
                    'product_type_id' => '2',
                    'Product' => [
                        'volume' => '0.500',
                        'weight' => '1.500',
                    ]
                ]
            ]
        ];
        $service = new PurchaseOrderService([
            new CalculateTotalByWeight(),
            new CalculateTotalByVolume()
        ]);
        $totals = $service->calculatePurchaseOrderTotals($groupedPurchaseOrders);

        $this->assertCount(1, $totals);
        $this->assertArrayHasKey('total', $totals[0]);
    }
}
