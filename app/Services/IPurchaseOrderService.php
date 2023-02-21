<?php
namespace App\Services;

interface IPurchaseOrderService
{
    /**
     * Get grouped purchase orders by product type id
     *
     * @param  array $purchaseOrderIds
     * @return array
     */
    public function getGroupedPurchaseOrders(array $purchaseOrderIds): array;

    /**
     * Calculate Purchase Order Totals
     *
     * @param  array $groupedPurchaseOrders
     * @return array
     */
    public function calculatePurchaseOrderTotals(array $groupedPurchaseOrders): array;
}

