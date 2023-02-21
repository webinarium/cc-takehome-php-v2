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

    /**
     * @inheritDoc
     */
    public function supports(int $productTypeId): bool
    {
        return in_array($productTypeId, [1, 3]);
    }
}

