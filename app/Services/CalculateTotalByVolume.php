<?php

namespace App\Services;

class CalculateTotalByVolume implements ICalculateTotal
{
    /**
     * @inheritDoc
     */
    public function getTotal(array $product): float
    {
        return $product['unit_quantity_initial'] * $product['Product']['volume'];
    }
}

