@extends('layouts.app')

@section('title', 'Create Order - Order & Inventory')

@section('content')
<div class="page-container">

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger error">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Low Stock Alert --}}
    @if($lowStockProducts->count())
        <div class="alert alert-warning warning">
            <div class="alert-title">
                <span>⚠ Low Stock Alert:</span>
                <span class="stock-summary-count">{{ $lowStockProducts->count() }} item(s) running low</span>
            </div>
            <ul class="stock-list">
                @foreach($lowStockProducts as $product)
                    <li>
                        <span class="product-name">{{ $product->name }}</span>
                        <span class="badge badge-warning">{{ $product->stock_on_hand }} left</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card order-card">
        <div class="card-header">
            <div>
                <h1 class="page-title">Create Order</h1>
                <p class="page-subtitle">Enter customer details and select products to generate bill</p>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST" id="order-form">
                @csrf

                <div class="form-section">
                    <h3 class="section-title">Customer Information</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name">Customer Name <span class="required">*</span></label>
                            <input
                                type="text"
                                id="customer_name"
                                name="customer_name"
                                class="form-control"
                                placeholder="Enter name"
                                value="{{ old('customer_name') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="customer_email">Customer Email <span class="required">*</span></label>
                            <input
                                type="email"
                                id="customer_email"
                                name="customer_email"
                                class="form-control"
                                placeholder="customer@email.com"
                                value="{{ old('customer_email') }}"
                                required
                            >
                        </div>
                    </div>
                </div>

                <hr class="section-divider">

                {{-- Products --}}
                <div class="form-section">
                    <div class="section-header-flex">
                        <h3 class="section-title">Order Items</h3>
                        <span class="section-hint">Select product & quantity</span>
                    </div>

                    <div id="products-container" class="products-container">
                        <div class="product-row">
                            <div class="product-select-col">
                                <select
                                    name="products[0][product_id]"
                                    class="form-control product-select"
                                    required
                                >
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }} - ₹{{ number_format($product->price_per_unit, 2) }} (Stock: {{ $product->stock_on_hand }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="quantity-col">
                                <input
                                    type="number"
                                    name="products[0][quantity]"
                                    class="form-control quantity-input"
                                    min="1"
                                    value="1"
                                    placeholder="Qty"
                                    required
                                >
                            </div>

                            <div class="action-col">
                                <button
                                    type="button"
                                    class="btn-remove"
                                    onclick="removeProduct(this)"
                                    title="Remove this item"
                                >
                                    ✕ Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="product-actions">
                        <button
                            type="button"
                            id="add-product"
                            class="btn-outline"
                        >
                            + Add Product
                        </button>
                    </div>
                </div>

                <div class="form-footer payment-footer">
                    <div class="cash-input-wrapper">
                        <label for="amount_given">Amount Given by Customer (₹):</label>
                        <input
                            type="number"
                            id="amount_given"
                            name="amount_given"
                            class="form-control cash-received-input"
                            placeholder="e.g. 500 (Optional)"
                            step="0.01"
                            min="0"
                            value="{{ old('amount_given') }}"
                        >
                    </div>

                    <button type="submit" class="submit-btn">
                        Generate Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let productIndex = 1;

    /*
    |--------------------------------------------------------------------------
    | Add Product
    |--------------------------------------------------------------------------
    */
    document
        .getElementById('add-product')
        .addEventListener('click', function () {

            const container =
                document.getElementById('products-container');

            const row =
                document.createElement('div');

            row.classList.add('product-row');

            row.innerHTML = `
                <div class="product-select-col">
                    <select
                        name="products[\${productIndex}][product_id]"
                        class="form-control product-select"
                        required
                    >
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} - ₹{{ number_format($product->price_per_unit, 2) }} (Stock: {{ $product->stock_on_hand }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="quantity-col">
                    <input
                        type="number"
                        name="products[\${productIndex}][quantity]"
                        class="form-control quantity-input"
                        min="1"
                        value="1"
                        placeholder="Qty"
                        required
                    >
                </div>

                <div class="action-col">
                    <button
                        type="button"
                        class="btn-remove"
                        onclick="removeProduct(this)"
                        title="Remove this item"
                    >
                        ✕ Remove
                    </button>
                </div>
            `;

            container.appendChild(row);
            productIndex++;
        });

    /*
    |--------------------------------------------------------------------------
    | Remove Product
    |--------------------------------------------------------------------------
    */
    function removeProduct(button)
    {
        const rows =
            document.querySelectorAll('.product-row');

        if (rows.length > 1) {
            button.closest('.product-row').remove();
        } else {
            alert('At least one product is required.');
        }
    }
</script>
@endsection
