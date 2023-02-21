<?php

namespace App\Services;

interface ICalculateTotal {
    /**
     * Calculates total for the specified product
     *
     * @param array $product A single product with all its properties
     * @return float Calculated total
     */
    public function getTotal(array $product): float;

    /**
     * Whether the calculator supports specified product type
     *
     * @param int $productTypeId
     * @return bool
     */
    public function supports(int $productTypeId): bool;
}
