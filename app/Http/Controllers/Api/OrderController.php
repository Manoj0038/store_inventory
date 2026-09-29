<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated()
            );

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
            ], 201);

        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'product_name' => $e->productName,
                'requested_quantity' => $e->requestedQuantity,
                'available_stock' => $e->availableStock,
            ], 422);
        }
    }


    public function customerOrderHistory(string $email): JsonResponse
    {
        $customer = Customer::query()
            ->where('email', $email)
            ->first();

        if (!$customer) {
            return response()->json([
                'message' => "Customer with email '{$email}' not found.",
                'orders' => [],
            ], 404);
        }

        $orders = $customer->orders()
            ->with(['items.product'])
            ->latest()
            ->get();

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'orders_count' => $orders->count(),
            'orders' => $orders,
        ], 200);
    }
}
