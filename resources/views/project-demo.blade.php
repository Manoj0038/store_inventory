<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel Inventory Project</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
            line-height: 1.7;
        }

        .navbar {
            background: #111827;
            color: white;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            font-size: 22px;
        }

        .navbar span {
            color: #60a5fa;
            font-size: 14px;
        }

        .hero {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: white;
            padding: 70px 8%;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .hero p {
            max-width: 700px;
            font-size: 18px;
            opacity: 0.9;
        }

        .container {
            width: 84%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .card {
            background: white;
            padding: 30px;
            margin-bottom: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
        }

        .card h2 {
            margin-bottom: 15px;
            color: #111827;
        }

        .card p {
            color: #4b5563;
            margin-bottom: 12px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .step {
            padding: 22px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fafafa;
        }

        .step-number {
            width: 35px;
            height: 35px;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .step h3 {
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 13px;
            font-weight: bold;
        }

        .tech-stack {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .tech {
            background: #eff6ff;
            color: #2563eb;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 30px;
            background: #111827;
            color: #9ca3af;
            margin-top: 50px;
        }

        @media (max-width: 700px) {
            .hero h1 {
                font-size: 30px;
            }

            .steps {
                grid-template-columns: 1fr;
            }

            .container {
                width: 92%;
            }
        }
    </style>
</head>

<body>

    {{-- Navbar --}}
    <nav class="navbar">
        <h2>Store Order Inventory</h2>
        <a href="{{ route('orders.create') }}" style="color:white">Create Order</a>
    </nav>

    {{-- Hero --}}
    <section class="hero">
        <h1>Store Order & Inventory Management</h1>
        <p>
            A simple inventory and order management system
            built using Laravel.
        </p>
    </section>

    <div class="container">

        {{-- Introduction --}}
        <div class="card">
            <h2>Project Introduction</h2>

            <p>
                Hi, I've created a simple stock inventory
                and order management system using Laravel.
            </p>

            <p>
                Before running the project, please read the instructions
                in the README file and follow the steps carefully.
            </p>

            <div class="tech-stack">
                <span class="tech">Laravel</span>
                <span class="tech">PHP</span>
                <span class="tech">MySQL</span>
                <span class="tech">Blade Template UI</span>
            </div>
        </div>

        {{-- Features --}}
        <div class="card">
            <h2>How the System Works</h2>

            <div class="steps">

                <div class="step">
                    <div class="step-number">1</div>

                    <h3>Order Create</h3>

                    <p>
                        Once you run the project, it will redirect
                        you to the Order Create page.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">2</div>

                    <h3>Customer Details</h3>

                    <p>
                        Enter the customer's name and email,
                        select the required products, and specify
                        the quantity.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">3</div>

                    <h3>Product Management</h3>

                    <p>
                        You can add multiple products and remove
                        any product after selecting it.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">4</div>

                    <h3>Low Stock Alert</h3>

                    <p>
                        The navigation bar shows low-stock products.
                        If stock is less than
                        <span class="badge">6 items</span>,
                        it will be displayed as low stock.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">5</div>

                    <h3>Generate Bill</h3>

                    <p>
                        After entering all the required details,
                        click the Generate Bill button.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">6</div>

                    <h3>Payment & Balance</h3>

                    <p>
                        Enter the cash amount received from the customer.
                        The system automatically calculates the
                        remaining balance.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">7</div>

                    <h3>Invoice</h3>

                    <p>
                        After generating the bill, all customer
                        and order details will be displayed
                        on the invoice page.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">8</div>

                    <h3>PDF & Email</h3>

                    <p>
                        You can generate the invoice as a PDF
                        or send it directly to the customer
                        by email.
                    </p>
                </div>

            </div>
        </div>

        {{-- Conclusion --}}
        <div class="card">
            <h2>Conclusion</h2>

            <p>
                That's all about my Laravel stock inventory
                and order management project.
            </p>

            <p>
                Thank you for watching!
            </p>
        </div>

    </div>

 

</body>
</html>
