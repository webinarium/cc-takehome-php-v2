<?php

namespace App\Services;

class CalculateTotalByWeight implements ICalculateTotal
{
    /**
     * constructor
     *
     * @param  array $purchaseOrders
     */
    public function __construct(array $purchaseOrders) {
        $this->purchaseOrders = $purchaseOrders;
    }

    /**
     * calculate total
     *
     * @return string
     */
    public function getTotal() : string
    {
        $total = 0;
        foreach ($this->purchaseOrders as $po) {
            $total += $po['unit_quantity_initial'] * $po['Product']['weight'];
        }

        // use number_format to force integer to have 1 decimal
        return number_format(round($total, 1), 1);
    }
}

