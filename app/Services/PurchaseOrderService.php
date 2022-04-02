<?php
namespace App\Services;

use App\Services\CalculateTotalByVolume;
use App\Services\CalculateTotalByWeight;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class PurchaseOrderService
{
    /**
     * Product type id and calculate total method mapping
     * eg. product_type_id => method
     */
    const PRODUCT_CALCULATE_TOTAL_METHODS = [
        1 => CalculateTotalByWeight::class,
        2 => CalculateTotalByVolume::class,
        3 => CalculateTotalByWeight::class,
    ];

    /**
     * Get grouped purchase orders by product type id
     *
     * @param  array $purchaseOrderIds
     * @return array
     */
    public function getGroupedPurchaseOrders($purchaseOrderIds)
    {
        $responses = $this->getPurchaseOrdersFromApi($purchaseOrderIds);
        $grouped = $this->getGroupedPurchaseOrdersFromResponses($responses);

        return $grouped;
    }

    /**
     * Get grouped purchase orders from api responses
     *
     * @param  array $responses
     * @return array
     */
    public function getGroupedPurchaseOrdersFromResponses(array $responses): array
    {
        $groupedPurchaseOrders = [];
        foreach ($responses as $res) {
            if ($res->failed()) {
                $res->throw()->json();
            }

            $products = $res->json('data.PurchaseOrderProduct');

            foreach ($products as $product) {
                $groupedPurchaseOrders[$product['product_type_id']][] = [
                    'product_type_id' => $product['product_type_id'],
                    'unit_quantity_initial' => $product['unit_quantity_initial'],
                    'weight' => $product['Product']['weight'],
                    'volume' => $product['Product']['volume'],
                ];
            }
        }

        return $groupedPurchaseOrders;
    }

    /**
     * Get purchase orders data from Api
     *
     * @param  array $purchaseOrderIds
     * @return array
     */
    protected function getPurchaseOrdersFromApi(array $purchaseOrderIds)
    {
        $responses = Http::pool(function (Pool $pool) use ($purchaseOrderIds) {
            $apiPool = [];
            foreach ($purchaseOrderIds as $id) {
                $apiUrl = $this->getApiUrl('PurchaseOrders/' . $id);

                $apiPool[] = $pool->withBasicAuth(
                    config('services.cartoncloud.api.username'),
                    config('services.cartoncloud.api.password')
                )->get($apiUrl);
            }

            return $apiPool;
        });

        return $responses;
    }

    /**
     * Get API url
     *
     * @param  string $path
     * @return void
     */
    protected function getApiUrl(string $path)
    {
        return 'https://api.cartoncloud.com.au/CartonCloud_Demo/'. $path . '?version=5&associated=true';
    }

    /**
     * Calculate Purchase Order Totals
     *
     * @param  array $groupedPurchaseOrders
     * @return array
     */
    public function calculatePurchaseOrderTotals($groupedPurchaseOrders)
    {
        $result = [];
        foreach($groupedPurchaseOrders as $productTypeId => $purchaseOrders) {
            if (! array_key_exists($productTypeId, self::PRODUCT_CALCULATE_TOTAL_METHODS)) {
                throw new \Exception(
                    'Product type ' . $productTypeId . ' and its calculate total method mapping doesn\'t exist.'
                );
            }

            $calculateMethod = new (self::PRODUCT_CALCULATE_TOTAL_METHODS[$productTypeId])($purchaseOrders);

            $total = $calculateMethod->getTotal();

            $result[] = [
                'product_type_id' => $productTypeId,
                'total' => $total
            ];
        }

        return $result;
    }
}

