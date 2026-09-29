
### 1. Prerequisites
 **PHP** >= 8.1 
 **Composer**
 **MySQL 


### 2. Installation Steps

 **Step 1**: Downnload this project

 **Step 2**: Configure Environment

  generate app key:
  php artisan key:generate


Ensure your `.env` contains:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_inventory
DB_USERNAME=root
DB_PASSWORD=""

QUEUE_CONNECTION=database
LOW_STOCK_THRESHOLD=5

as well as  configure SMTP Credentials
```

#### Step 4: Run Migrations and Seed Sample Data
Make sure MySQL is running in XAMPP or Mysql Workbench :
php artisan migrate --seed


#### Step 5: Start the Local Server

php artisan serve

 **Web UI:** [http://127.0.0.1:8000/orders/create](http://127.0.0.1:8000/orders/create)
 **API Base URL:** [http://127.0.0.1:8000/api](http://127.0.0.1:8000/api)

#### Step 6: Start the Background Queue Worker
In a separate terminal run below command:
php artisan queue:work


### Running Automated Tests (PHPUnit)

All functional requirements, insufficient stock, and concurrency safeguards are covered

php artisan test


### Test Suite Summary:
```
   PASS  Tests\Feature\OrderApiTest
  ✓ can create order via api and deducts stock
  ✓ order creation fails when insufficient stock (Edge Case)
  ✓ order creation dispatches queued email job
  ✓ can fetch customer order history by email
  ✓ customer order history returns 404 for unknown email
  ✓ can fetch products below configurable low stock threshold
  ✓ concurrent order attempts prevent overselling (Concurrency Safe)

  Tests: 9 passed (37 assertions)
```

-------------------------------------------------------------

## REST API Reference

### 1. Create an Order
**Endpoint:** `POST /api/orders`  
**Headers:** `Content-Type: application/json`, `Accept: application/json`

#### Request Body:
```json
{
  "customer_name": "Karthi Kumar",
  "customer_email": "karthi@example.com",
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

#### Success Response (`201 Created`):
```json
{
  "message": "Order created successfully.",
  "order": {
    "id": 1,
    "customer_id": 1,
    "subtotal": "200.00",
    "tax": "36.00",
    "grand_total": "236.00",
    "customer": {
      "id": 1,
      "name": "Karthi Kumar",
      "email": "karthi@example.com"
    },
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "unit_price": "100.00",
        "tax_percentage": "18.00",
        "subtotal": "200.00",
        "tax": "36.00",
        "total": "236.00",
        "product": {
          "id": 1,
          "name": "Wireless Mouse",
          "stock_on_hand": 8
        }
      }
    ]
  }
}
```

#### Insufficient Stock Error (`422 Unprocessable Entity`):
```json
{
  "message": "Insufficient stock for 'Wireless Mouse'. Requested: 15, Available: 8.",
  "product_name": "Wireless Mouse",
  "requested_quantity": 15,
  "available_stock": 8
}
```

---

### 2. Fetch Customer Order History (Requirement 3)
**Endpoint:** `GET /api/customers/{email}/orders`  
**Example:** `GET /api/customers/karthi@example.com/orders`

#### Success Response (`200 OK`):
```json
{
  "customer": {
    "id": 1,
    "name": "Karthi Kumar",
    "email": "karthi@example.com"
  },
  "orders_count": 1,
  "orders": [
    {
      "id": 1,
      "subtotal": "200.00",
      "tax": "36.00",
      "grand_total": "236.00",
      "created_at": "2026-09-26T15:00:00.000000Z",
      "items": [ ... ]
    }
  ]
}
```

#### Not Found Response (`404 Not Found`):
```json
{
  "message": "Customer with email 'unknown@example.com' not found.",
  "orders": []
}
```

---

### 3. Fetch Low-Stock Products (Requirement 4)
**Endpoint:** `GET /api/products/low-stock?threshold=5`  

#### Success Response (`200 OK`):
```json
{
  "threshold": 5,
  "count": 2,
  "products": [
    {
      "id": 4,
      "name": "Mechanical Keyboard",
      "code": "PROD-1042",
      "price_per_unit": "450.00",
      "tax_percentage": "18.00",
      "stock_on_hand": 2
    },
    {
      "id": 9,
      "name": "USB-C Cable",
      "code": "PROD-2015",
      "price_per_unit": "50.00",
      "tax_percentage": "5.00",
      "stock_on_hand": 5
    }
  ]
}
```
----------------------------------------------------------------------------

The purpose of adding the section below with this type of logic is to avoid directly calling the Mail function or PHPMailer.


##  Concurrency 

When two concurrent requests attempt to purchase the same product when only 1 unit is in stock:
1. `OrderService` executes inside a database transaction: `DB::transaction(...)`.
2. It applies MySQL pessimistic row-level locking:
   ```php
   $product = Product::query()
       ->lockForUpdate()
       ->findOrFail($item['product_id']);
   ```
3. Request #1 acquires the lock, validates stock (`1 >= 1`), decrements stock to `0`, and commits.
4. Request #2 waits for the lock. Once acquired, it reads fresh `stock_on_hand = 0`, fails validation, and cleanly throws `InsufficientStockException` (`422 Unprocessable Entity`).
5. **Result:** Exactly one order succeeds, zero overselling, and inventory never drops below 0.

---

##  Asynchronous Email Processing (`dispatch()`)

Instead of freezing the HTTP request during SMTP transmission, orders immediately dispatch an asynchronous queue job:
```php
SendOrderBillJob::dispatch($order->id);
```
- Job is written to the MySQL `jobs` table in under 3 milliseconds.
- User/API client receives immediate response.
- Background worker (`php artisan queue:work`) processes and sends the order bill asynchronously.
