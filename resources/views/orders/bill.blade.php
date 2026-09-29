@extends('layouts.app')

@section('title', 'Order Bill #' . $order->id . ' - Order & Inventory')

@section('content')
<div class="invoice-container container">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success success">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="invoice-card card">

        {{-- Header --}}
        <div class="invoice-header header">
            <div class="invoice-info">
                <span class="invoice-badge">INVOICE</span>
                <h1 class="invoice-title">Order #{{ $order->id }}</h1>
                <p class="invoice-date">
                    <strong>Date:</strong> {{ $order->created_at->format('d-m-Y') }}
                </p>
            </div>

            {{-- Customer --}}
            <div class="invoice-customer customer">
                <span class="customer-label">CUSTOMER DETAILS</span>
                <h3 class="customer-name">Name : {{ $order->customer->name }}</h3>
                <p class="customer-email">Email : {{ $order->customer->email }}</p>
            </div>
        </div>

        {{-- Products --}}
        <div class="invoice-section">
            <h3 class="section-title">Order Items</h3>

            <div class="table-responsive">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Tax %</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <span class="product-title">{{ $item->product->name }}</span>

                                    {{-- Low Stock Alert --}}
                                    @if($item->product->stock_on_hand <= 5)
                                        <span class="warning stock-warning-pill">
                                            ⚠ {{ $item->product->stock_on_hand }} unit(s) left
                                        </span>
                                    @endif
                                </td>

                                <td class="text-right font-medium">
                                    {{ $item->quantity }}
                                </td>

                                <td class="text-right">
                                    ₹{{ number_format($item->unit_price, 2) }}
                                </td>

                                <td class="text-right">
                                    {{ $item->tax_percentage }}%
                                </td>

                                <td class="text-right font-semibold">
                                    ₹{{ number_format($item->total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals & Payment Section --}}
        <div class="totals-section">
            <div class="payment-calculator-box">
                <div class="payment-title">
                    <span>💵 Cash Tendered & Change</span>
                </div>
                <div class="payment-form-row">
                    <div class="payment-input-group">
                        <label for="amount_given_input">Amount Given by Customer (₹):</label>
                        <input
                            type="number"
                            id="amount_given_input"
                            class="form-control payment-input"
                            placeholder="Enter amount given"
                            step="0.01"
                            min="0"
                            value="{{ $order->amount_paid ? number_format($order->amount_paid, 2, '.', '') : '' }}"
                        >
                    </div>

                    <div class="change-summary-group">
                        <span class="change-label">Balance to Return:</span>
                        <span id="change_amount" class="change-amount">
                            ₹{{ $order->change_returned !== null ? number_format($order->change_returned, 2) : '0.00' }}
                        </span>
                        <div id="change_badge" class="payment-status-badge status-neutral">
                            @if($order->amount_paid !== null)
                                @if($order->amount_paid >= $order->grand_total)
                                    ✓ Balance to Return: ₹{{ number_format($order->change_returned, 2) }}
                                @else
                                    ⚠ Still Due: ₹{{ number_format($order->grand_total - $order->amount_paid, 2) }}
                                @endif
                            @else
                                Enter cash received to calculate
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($order->subtotal, 2) }}</span>
                </div>

                <div class="totals-row">
                    <span>Tax</span>
                    <span>₹{{ number_format($order->tax, 2) }}</span>
                </div>

                <div class="totals-row grand-total">
                    <span>Grand Total</span>
                    <span>₹{{ number_format($order->grand_total, 2) }}</span>
                </div>

                <div class="totals-row print-only-row">
                    <span>Amount Received</span>
                    <span id="print_amount_paid">₹{{ $order->amount_paid ? number_format($order->amount_paid, 2) : '0.00' }}</span>
                </div>

                <div class="totals-row print-only-row print-change">
                    <span>Balance Returned</span>
                    <span id="print_change_return">₹{{ $order->change_returned ? number_format($order->change_returned, 2) : '0.00' }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="invoice-actions actions">
            {{-- Print --}}
            <button
                type="button"
                class="print-btn btn"
                onclick="window.print()"
            >
                🖨️ Print Bill
            </button>

            {{-- Send Email --}}
            <form
                action="{{ route('orders.send-email', $order) }}"
                method="POST"
                style="display: inline;"
            >
                @csrf
                <button
                    type="submit"
                    class="email-btn btn"
                >
                    📧 Send Bill Email
                </button>
            </form>

            {{-- New Order --}}
            <a
                href="{{ route('orders.create') }}"
                class="new-order-btn btn"
            >
                + New Order
            </a>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    const grandTotal = {{ (float) $order->grand_total }};
    const amountInput = document.getElementById('amount_given_input');
    const changeDisplay = document.getElementById('change_amount');
    const changeBadge = document.getElementById('change_badge');
    const printAmountPaid = document.getElementById('print_amount_paid');
    const printChangeReturn = document.getElementById('print_change_return');

    function calculateChange() {
        const value = parseFloat(amountInput.value);

        if (isNaN(value) || value <= 0) {
            changeDisplay.textContent = '₹0.00';
            changeDisplay.className = 'change-amount';
            changeBadge.textContent = 'Enter cash received to calculate';
            changeBadge.className = 'payment-status-badge status-neutral';
            if (printAmountPaid) printAmountPaid.textContent = '₹0.00';
            if (printChangeReturn) printChangeReturn.textContent = '₹0.00';
            return;
        }

        const diff = value - grandTotal;

        if (printAmountPaid) {
            printAmountPaid.textContent = '₹' + value.toFixed(2);
        }

        if (diff >= 0) {
            changeDisplay.textContent = '₹' + diff.toFixed(2);
            changeDisplay.className = 'change-amount text-success';
            changeBadge.textContent = '✓ Balance to Return: ₹' + diff.toFixed(2);
            changeBadge.className = 'payment-status-badge status-success';
            if (printChangeReturn) printChangeReturn.textContent = '₹' + diff.toFixed(2);
        } else {
            const due = Math.abs(diff);
            changeDisplay.textContent = '₹0.00';
            changeDisplay.className = 'change-amount text-danger';
            changeBadge.textContent = '⚠ Still Due: ₹' + due.toFixed(2);
            changeBadge.className = 'payment-status-badge status-warning';
            if (printChangeReturn) printChangeReturn.textContent = '₹0.00 (Due: ₹' + due.toFixed(2) + ')';
        }

        savePayment(value);
    }

    let saveTimeout = null;
    function savePayment(amount) {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            fetch("{{ route('orders.payment', $order) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount_given: amount })
            }).catch(() => {});
        }, 600);
    }

    amountInput.addEventListener('input', calculateChange);

    if (amountInput.value) {
        calculateChange();
    }
</script>
@endsection
