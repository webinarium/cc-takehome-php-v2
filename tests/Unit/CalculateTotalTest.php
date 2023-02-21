<?php

namespace Tests\Unit;

use App\Services\CalculateTotalByVolume;
use App\Services\CalculateTotalByWeight;
use PHPUnit\Framework\TestCase;

class CalculateTotalTest extends TestCase
{
    protected function setUp(): void
    {
        // Set up test data
        $this->purchaseOrders = [
            [
                'unit_quantity_initial' => '8.000',
                'Product' => [
                    'volume' => '0.500',
                    'weight' => '1.500',
                ]
            ],
            [
                'unit_quantity_initial' => '12.000',
                'Product' => [
                    'volume' => '1.400',
                    'weight' => '1.900',
                ]
            ],
            [
                'unit_quantity_initial' => '9.000',
                'Product' => [
                    'volume' => '0.525',
                    'weight' => '1.612',
                ]
            ]
        ];
    }

    /**
     * Calculate by weight
     *
     * @return void
     */
    public function test_calculate_total_by_weight()
    {
        $byWeight = new CalculateTotalByWeight($this->purchaseOrders);
        $total = $byWeight->getTotal();

        $this->assertEquals(49.3, $total);
    }

    /**
     * Calculate by weight
     *
     * @return void
     */
    public function test_calculate_total_by_volume()
    {
        $byVolume = new CalculateTotalByVolume($this->purchaseOrders);
        $total = $byVolume->getTotal();

        $this->assertEquals(25.5, $total);
    }
}
