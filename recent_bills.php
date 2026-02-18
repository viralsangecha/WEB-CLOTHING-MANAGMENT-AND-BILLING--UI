<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo "<script>alert('You are not logged in, please log in');</script>";
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "admins");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First, check if created_at column exists
$check_column = $conn->query("SHOW COLUMNS FROM sales LIKE 'created_at'");
if ($check_column->num_rows > 0) {
    // If created_at exists, use it
    $sql = "SELECT 
                customer_name,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as sale_date,
                GROUP_CONCAT(CONCAT(item_name, ' (', quantity_sold, ')') SEPARATOR ', ') as items,
                SUM(total_price) as total_amount
            FROM sales 
            GROUP BY customer_name, created_at
            ORDER BY created_at DESC 
            LIMIT 50";
} else {
    // If created_at doesn't exist, use simpler query
    $sql = "SELECT 
                customer_name,
                GROUP_CONCAT(CONCAT(item_name, ' (', quantity_sold, ')') SEPARATOR ', ') as items,
                SUM(total_price) as total_amount
            FROM sales 
            GROUP BY customer_name, id
            ORDER BY id DESC 
            LIMIT 50";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Bills - Clothing Store Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reuse the same CSS variables and basic styles from billing.php */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 600;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .bills-container {
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bill-table th,
        .bill-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .bill-table th {
            background: var(--background);
            font-weight: 500;
        }

        .bill-table tr:hover {
            background: var(--background);
        }

        .amount {
            font-weight: 500;
        }

        .no-bills {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .bill-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Recent Bills</h1>
            <a href="billing.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Back to Billing
            </a>
        </div>

        <div class="bills-container">
            <table class="bill-table">
                <thead>
                    <tr>
                        <?php if ($check_column->num_rows > 0): ?>
                        <th>Date & Time</th>
                        <?php endif; ?>
                        <th>Customer Name</th>
                        <th>Items Purchased</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            if ($check_column->num_rows > 0) {
                                echo "<td>" . htmlspecialchars($row['sale_date']) . "</td>";
                            }
                            echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['items']) . "</td>";
                            echo "<td class='amount'>₹" . number_format($row['total_amount'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        $colspan = $check_column->num_rows > 0 ? 4 : 3;
                        echo "<tr><td colspan='$colspan' class='no-bills'>No recent bills found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 