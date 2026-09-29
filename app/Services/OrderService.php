<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderBillJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order with stock validation, pessimistic locking, and auto-dispatch confirmation job.
     *
     * @param array $data
     * @return Order
     * @throws InsufficientStockException
     */
    public function createOrder(array $data): Order
    {
        $order = DB::transaction(function () use ($data) {

            $customer = Customer::firstOrCreate(
                [
                    'email' => $data['customer_email'],
                ],
                [
                    'name' => $data['customer_name'],
                ]
            );

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => 0,
                'tax' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = 0;
            $tax = 0;

            foreach ($data['products'] as $item) {

                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);

                $quantity = (int) $item['quantity'];

                // Validate stock availability
                if ($product->stock_on_hand < $quantity) {
                    throw new InsufficientStockException(
                        $product->name,
                        $quantity,
                        $product->stock_on_hand
                    );
                }

                $itemSubtotal = $quantity * $product->price_per_unit;
                $itemTax = $itemSubtotal * ($product->tax_percentage / 100);
                $itemTotal = $itemSubtotal + $itemTax;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price_per_unit,
                    'tax_percentage' => $product->tax_percentage,
                    'subtotal' => $itemSubtotal,
                    'tax' => $itemTax,
                    'total' => $itemTotal,
                ]);

                // Deduct stock safely
                $product->decrement('stock_on_hand', $quantity);

                $subtotal += $itemSubtotal;
                $tax += $itemTax;
            }

            $grandTotal = $subtotal + $tax;
            $amountPaid = isset($data['amount_given']) && $data['amount_given'] !== null && $data['amount_given'] !== ''
                ? (float) $data['amount_given']
                : null;
            $changeReturned = $amountPaid !== null
                ? max(0, $amountPaid - $grandTotal)
                : null;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'change_returned' => $changeReturned,
            ]);

            return $order;
        });

        // Requirement 5: Dispatch queued job on order creation that simulates sending order-confirmation email
        SendOrderBillJob::dispatch($order->id);

        return $order->load(['customer', 'items.product']);
    }
}
