<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderTotalsRequest;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    /**
     * generate purchase order totals
     *
     * @param  Request $request
     * @return JsonResponse response
     */
    public function purchaseOrderTotals(PurchaseOrderTotalsRequest $request) : JsonResponse
    {
        $purchaseOrderIds = $request->purchase_order_ids;

        $POService = new PurchaseOrderService;
        $groupedPurchaseOrders = $POService->getGroupedPurchaseOrders($purchaseOrderIds);

        $result = $POService->calculatePurchaseOrderTotals($groupedPurchaseOrders);

        return response()->json([
            'result' => $result
        ]);
    }
}
