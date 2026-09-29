<?php

namespace Tests\Feature;

use App\Jobs\SendOrderBillJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_via_api_and_deducts_stock(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Wireless Mouse',
            'price_per_unit' => 100.00,
            'tax_percentage' => 10.00,
            'stock_on_hand' => 10,
        ]);

        $payload = [
            'customer_name' => 'Alice Johnson',
            'customer_email' => 'alice@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $response = $this->postJson(route('api.orders.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Order created successfully.')
            ->assertJsonPath('order.customer.email', 'alice@example.com')
            ->assertJsonPath('order.subtotal', '300.00')
            ->assertJsonPath('order.tax', '30.00')
            ->assertJsonPath('order.grand_total', '330.00');

        $this->assertEquals(7, $product->fresh()->stock_on_hand);

        $this->assertDatabaseHas('customers', [
            'email' => 'alice@example.com',
            'name' => 'Alice Johnson',
        ]);

        $this->assertDatabaseHas('orders', [
            'subtotal' => 300.00,
            'tax' => 30.00,
            'grand_total' => 330.00,
        ]);
    }

    public function test_order_creation_fails_when_insufficient_stock(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Gaming Keyboard',
            'price_per_unit' => 150.00,
            'tax_percentage' => 18.00,
            'stock_on_hand' => 2, 
        ]);

        $payload = [
            'customer_name' => 'Bob Smith',
            'customer_email' => 'bob@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5, 
                ],
            ],
        ];

        $response = $this->postJson(route('api.orders.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'product_name',
                'requested_quantity',
                'available_stock',
            ]);

        $this->assertEquals(2, $product->fresh()->stock_on_hand);

        $this->assertEquals(0, Order::count());
    }

   
    public function test_order_creation_dispatches_queued_email_job(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'price_per_unit' => 50.00,
            'tax_percentage' => 0.00,
            'stock_on_hand' => 5,
        ]);

        $payload = [
            'customer_name' => 'Charlie Brown',
            'customer_email' => 'charlie@example.com',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson(route('api.orders.store'), $payload);
        $response->assertStatus(201);

        $orderId = $response->json('order.id');

        // Assert job was pushed to queue with correct order ID
        Queue::assertPushed(SendOrderBillJob::class, function ($job) use ($orderId) {
            return $job->orderId === $orderId;
        });
    }

  
    public function test_can_fetch_customer_order_history_by_email(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Diana Prince',
            'email' => 'diana@example.com',
        ]);

        $product = Product::factory()->create();

        $order1 = Order::create([
            'customer_id' => $customer->id,
            'subtotal' => 100,
            'tax' => 10,
            'grand_total' => 110,
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'tax_percentage' => 10,
            'subtotal' => 100,
            'tax' => 10,
            'total' => 110,
        ]);

        $order2 = Order::create([
            'customer_id' => $customer->id,
            'subtotal' => 200,
            'tax' => 20,
            'grand_total' => 220,
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax_percentage' => 10,
            'subtotal' => 200,
            'tax' => 20,
            'total' => 220,
        ]);

        $response = $this->getJson(route('api.customers.orders', ['email' => 'diana@example.com']));

        $response->assertStatus(200)
            ->assertJsonPath('customer.email', 'diana@example.com')
            ->assertJsonPath('orders_count', 2)
            ->assertJsonCount(2, 'orders');
    }

  
    public function test_customer_order_history_returns_404_for_unknown_email(): void
    {
        $response = $this->getJson(route('api.customers.orders', ['email' => 'unknown@example.com']));

        $response->assertStatus(404)
            ->assertJsonPath('message', "Customer with email 'unknown@example.com' not found.");
    }


    public function test_can_fetch_products_below_configurable_low_stock_threshold(): void
    {
        $lowStockProduct1 = Product::factory()->create([
            'name' => 'Low Stock Item 1',
            'stock_on_hand' => 3,
        ]);

        $lowStockProduct2 = Product::factory()->create([
            'name' => 'Low Stock Item 2',
            'stock_on_hand' => 5,
        ]);

        $highStockProduct = Product::factory()->create([
            'name' => 'High Stock Item',
            'stock_on_hand' => 15,
        ]);

        $response = $this->getJson(route('api.products.low-stock', ['threshold' => 5]));

        $response->assertStatus(200)
            ->assertJsonPath('threshold', 5)
            ->assertJsonPath('count', 2)
            ->assertJsonFragment(['name' => 'Low Stock Item 1'])
            ->assertJsonFragment(['name' => 'Low Stock Item 2'])
            ->assertJsonMissing(['name' => 'High Stock Item']);
    }

    public function test_concurrent_order_attempts_prevent_overselling(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Rare Collector Item',
            'price_per_unit' => 500.00,
            'tax_percentage' => 0.00,
            'stock_on_hand' => 1, 
        ]);

        $response1 = $this->postJson(route('api.orders.store'), [
            'customer_name' => 'Customer One',
            'customer_email' => 'customer1@example.com',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);
        $response1->assertStatus(201);
        $this->assertEquals(0, $product->fresh()->stock_on_hand);

        $response2 = $this->postJson(route('api.orders.store'), [
            'customer_name' => 'Customer Two',
            'customer_email' => 'customer2@example.com',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);
        $response2->assertStatus(422)
            ->assertJsonPath('available_stock', 0);

        $this->assertEquals(0, $product->fresh()->stock_on_hand);
    }
}
