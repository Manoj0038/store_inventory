<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function lowStock(Request $request): JsonResponse
    {
        $defaultThreshold = config('inventory.low_stock_threshold', 5);
        $threshold = $request->has('threshold')
            ? (int) $request->input('threshold')
            : (int) $defaultThreshold;

        if ($threshold < 0) {
            $threshold = 0;
        }

        $products = Product::query()
            ->where('stock_on_hand', '<=', $threshold)
            ->orderBy('stock_on_hand', 'asc')
            ->get();

        return response()->json([
            'threshold' => $threshold,
            'count' => $products->count(),
            'products' => $products,
        ], 200);
    }
}
