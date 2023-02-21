<?php

namespace App\Services;

class CalculateTotalByWeight implements ICalculateTotal
{
    /**
     * @inheritDoc
     */
    public function getTotal(array $product) : float
    {
        return $product['unit_quantity_initial'] * $product['Product']['weight'];
    }
}

