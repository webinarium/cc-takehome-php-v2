<?php
namespace App\Services;

use App\Services\CalculateTotalByVolume;
use App\Services\CalculateTotalByWeight;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class PurchaseOrderService implements IPurchaseOrderService
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
     * @inheritDoc
     */
    public function getGroupedPurchaseOrders(array $purchaseOrderIds): array
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
    protected function getGroupedPurchaseOrdersFromResponses(array $responses): array
    {
        $groupedPurchaseOrders = [];
        foreach ($responses as $res) {
            if ($res->failed()) {
                $res->throw()->json();
            }

            $products = $res->json('data.PurchaseOrderProduct');

            foreach ($products as $product) {
                $groupedPurchaseOrders[$product['product_type_id']][] = $product;
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
    protected function getPurchaseOrdersFromApi(array $purchaseOrderIds): array
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
     * @return string
     */
    protected function getApiUrl(string $path): string
    {
        return 'https://api.cartoncloud.com.au/CartonCloud_Demo/'. $path . '?version=5&associated=true';
    }

    /**
     * @inheritDoc
     */
    public function calculatePurchaseOrderTotals(array $groupedPurchaseOrders): array
    {
        $result = [];
        foreach($groupedPurchaseOrders as $productTypeId => $purchaseOrders) {
            if (! array_key_exists($productTypeId, self::PRODUCT_CALCULATE_TOTAL_METHODS)) {
                throw new \Exception(
                    'Product type ' . $productTypeId . ' and its calculate total method mapping doesn\'t exist.'
                );
            }

            $calculateMethod = new (self::PRODUCT_CALCULATE_TOTAL_METHODS[$productTypeId]);

            $total = array_sum(array_map(fn ($product): float => $calculateMethod->getTotal($product), $purchaseOrders));

            $result[] = [
                'product_type_id' => $productTypeId,
                'total' => number_format(round($total, 1), 1)
            ];
        }

        return $result;
    }
}

