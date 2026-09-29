<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
 public function run(): void
    {
        $customers = Customer::factory()->count(10)->create();

        $products = Product::factory()->count(20)->create();

        $customers->each(function ($customer) use ($products) {

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => 0,
                'tax' => 0,
                'grand_total' => 0,
            ]);

            $selectedProducts = $products->random(rand(1, 4));

            foreach ($selectedProducts as $product) {

                $quantity = rand(1, 5);

                $subtotal = $quantity * $product->price_per_unit;

                $tax = $subtotal * ($product->tax_percentage / 100);

                $total = $subtotal + $tax;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price_per_unit,
                    'tax_percentage' => $product->tax_percentage,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $order->items->sum('subtotal'),
                'tax' => $order->items->sum('tax'),
                'grand_total' => $order->items->sum('total'),
            ]);
        });
    }
}
