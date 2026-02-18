<?php
session_start();

if (!isset($_SESSION['bill_data'])) {
    header("Location: billing.php");
    exit();
}

$bill_data = $_SESSION['bill_data'];
$cart_items = $bill_data['cart'];
$customer_name = $bill_data['customer_name'];
$bill_date = $bill_data['bill_date'];
$bill_number = $bill_data['bill_number'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #<?php echo $bill_number; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .bill-container {
                border: none;
                box-shadow: none;
            }
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            padding: 2rem;
            margin: 0;
        }

        .bill-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .bill-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .store-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .store-address {
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .bill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .bill-info div {
            margin-bottom: 0.5rem;
        }

        .bill-info strong {
            display: inline-block;
            width: 100px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .items-table th,
        .items-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table th {
            background: #f8fafc;
            font-weight: 600;
        }

        .total-section {
            margin-left: auto;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }

        .total-row.final {
            font-weight: 700;
            font-size: 1.1rem;
            border-top: 2px solid #e2e8f0;
            margin-top: 0.5rem;
            padding-top: 1rem;
        }

        .thank-you {
            text-align: center;
            margin-top: 2rem;
            color: #64748b;
        }

        .actions {
            text-align: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <div class="store-name">Clothing Store</div>
            <div class="store-address">123 Fashion Street, Style City</div>
            <div class="store-contact">Phone: (123) 456-7890 | Email: contact@clothingstore.com</div>
        </div>

        <div class="bill-info">
            <div class="left-info">
                <div><strong>Bill To:</strong> <?php echo htmlspecialchars($customer_name); ?></div>
                <div><strong>Bill Date:</strong> <?php echo $bill_date; ?></div>
                <div><strong>Bill #:</strong> <?php echo $bill_number; ?></div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                foreach ($cart_items as $item) {
                    $subtotal += $item['total'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₹<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php
                }
                $tax = $subtotal * 0.18;
                $total = $subtotal + $tax;
                ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-row">
                <span>Tax (18%):</span>
                <span>₹<?php echo number_format($tax, 2); ?></span>
            </div>
            <div class="total-row final">
                <span>Total:</span>
                <span>₹<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <div class="thank-you">
            <p>Thank you for shopping with us!</p>
        </div>

        <div class="actions no-print">
            <button onclick="window.print()" class="btn btn-primary">Print Bill</button>
            <button onclick="window.location.href='billing.php'" class="btn btn-secondary">Back to Billing</button>
        </div>
    </div>

    <script>
        // Clear the bill data from session after printing
        window.onafterprint = function() {
            fetch('clear_bill_session.php');
        };
    </script>
</body>
</html> 