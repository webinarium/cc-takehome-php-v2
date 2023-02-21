<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderTotalsRequest;
use App\Services\IPurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    /**
     * generate purchase order totals
     *
     * @param  Request $request
     * @param  IPurchaseOrderService $POService
     * @return JsonResponse response
     */
    public function purchaseOrderTotals(PurchaseOrderTotalsRequest $request, IPurchaseOrderService $POService) : JsonResponse
    {
        $purchaseOrderIds = $request->purchase_order_ids;

        $groupedPurchaseOrders = $POService->getGroupedPurchaseOrders($purchaseOrderIds);

        $result = $POService->calculatePurchaseOrderTotals($groupedPurchaseOrders);

        return response()->json([
            'result' => $result
        ]);
    }
}
