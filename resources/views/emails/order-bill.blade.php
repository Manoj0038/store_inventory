<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        Order Bill #{{ $order->id }}
    </title>

</head>

<body style="
    margin: 0;
    padding: 30px;
    background-color: #f4f4f4;
    font-family: Arial, sans-serif;
">

<div style="
    max-width: 700px;
    margin: auto;
    background-color: white;
    padding: 30px;
">

    <h2>
        Order Confirmation
    </h2>

    <p>
        Hi {{ $order->customer->name }},
    </p>

    <p>
        Thank you for your order.
        Here are your order details.
    </p>


    <hr>


    <h3>
        Order #{{ $order->id }}
    </h3>

    <p>
        Date:
        {{ $order->created_at->format('d-m-Y H:i') }}
    </p>


    <table
        width="100%"
        cellpadding="8"
        cellspacing="0"
        border="1"
        style="border-collapse: collapse;"
    >

        <thead>

            <tr style="background-color: #eeeeee;">

                <th align="left">
                    Product
                </th>

                <th>
                    Qty
                </th>

                <th>
                    Unit Price
                </th>

                <th>
                    Tax
                </th>

                <th>
                    Total
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($order->items as $item)

                <tr>

                    <td>
                        {{ $item->product->name }}
                    </td>

                    <td align="center">
                        {{ $item->quantity }}
                    </td>

                    <td align="right">
                        ₹{{ number_format($item->unit_price, 2) }}
                    </td>

                    <td align="right">
                        ₹{{ number_format($item->tax, 2) }}
                    </td>

                    <td align="right">
                        ₹{{ number_format($item->total, 2) }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <br>


    <table width="100%">

        <tr>

            <td align="right">
                Subtotal:
            </td>

            <td align="right">
                ₹{{ number_format($order->subtotal, 2) }}
            </td>

        </tr>

        <tr>

            <td align="right">
                Tax:
            </td>

            <td align="right">
                ₹{{ number_format($order->tax, 2) }}
            </td>

        </tr>

        <tr>

            <td align="right">

                <strong>
                    Grand Total:
                </strong>

            </td>

            <td align="right">

                <strong>
                    ₹{{ number_format($order->grand_total, 2) }}
                </strong>

            </td>

        </tr>

        @if($order->amount_paid !== null)
        <tr>
            <td align="right" style="padding-top: 6px; color: #555;">
                Amount Paid:
            </td>
            <td align="right" style="padding-top: 6px; color: #555;">
                ₹{{ number_format($order->amount_paid, 2) }}
            </td>
        </tr>
        <tr>
            <td align="right" style="color: #166534; font-weight: bold;">
                Balance Returned:
            </td>
            <td align="right" style="color: #166534; font-weight: bold;">
                ₹{{ number_format($order->change_returned, 2) }}
            </td>
        </tr>
        @endif

    </table>


    <br>

    <p>
        Thank you for shopping with us.
    </p>

    <p>
        Regards,<br>
        Order Management Team
    </p>

</div>

</body>

</html>
