<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Jobs\SendOrderBillJob;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    public function create()
    {
        $products = Product::query()
            ->where('stock_on_hand', '>', 0)
            ->orderBy('name')
            ->get();

        $lowStockProducts = Product::query()
            ->where('stock_on_hand', '>', 0)
            ->where('stock_on_hand', '<=', 5)
            ->orderBy('stock_on_hand')
            ->get();

        return view('orders.create', [
            'products' => $products,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

   
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated()
            );

            return redirect()
                ->route('orders.bill', $order)
                ->with(
                    'success',
                    'Order created successfully.'
                );
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    
    public function bill(Order $order)
    {
        $order->load([
            'customer',
            'items.product',
        ]);

        return view('orders.bill', [
            'order' => $order,
        ]);
    }


    public function sendBillEmail(Order $order)
    {
        SendOrderBillJob::dispatch($order->id);

        return redirect()
            ->route('orders.bill', $order)
            ->with(
                'success',
                'Bill email has been queued successfully.'
            );
    }

    public function updatePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount_given' => 'required|numeric|min:0',
        ]);

        $amountPaid = (float) $validated['amount_given'];
        $changeReturned = max(0, $amountPaid - $order->grand_total);

        $order->update([
            'amount_paid' => $amountPaid,
            'change_returned' => $changeReturned,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'amount_paid' => $amountPaid,
                'change_returned' => $changeReturned,
            ]);
        }

        return redirect()
            ->route('orders.bill', $order)
            ->with(
                'success',
                'Payment updated successfully.'
            );
    }
}
